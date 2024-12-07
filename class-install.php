<?php

namespace MoneyManager;

defined( 'ABSPATH' ) || die( 'No direct script access allowed.' );

use MoneyManager\Managers\WooCommerce_Manager;

/**
 * Class Install
 * @package MoneyManager
 */
class Install
{
    /**
     * Check whether the plugin has ever been installed
     *
     * @return bool
     */
    public function installed()
    {
        return get_option( 'money_manager_version' ) !== false;
    }

    /**
     * Install the plugin
     */
    public function install()
    {
        if ( get_transient( 'money_manager_installing' ) == 'yes' ) {
            return;
        }

        set_transient( 'money_manager_installing', 'yes', MINUTE_IN_SECONDS * 10 );

        $this->create_tables();
        $this->create_options();
        self::create_fixtures();

        delete_transient( 'money_manager_installing' );
    }

    /**
     * Uninstall the plugin
     */
    public function uninstall()
    {
        $this->drop_tables();
        $this->drop_options_and_meta();
    }

    /**
     * Create tables in database
     */
    protected function create_tables()
    {
        global $wpdb;

        $collate = '';

        if ( $wpdb->has_cap( 'collation' ) ) {
            $collate = $wpdb->get_charset_collate();
        }

        /**
         * Some users are still using MySQL version <5.7, so we limit the length of the index
         * @see https://wordpress.org/support/topic/help-needed-102/
         * @see https://dev.mysql.com/doc/refman/5.7/en/innodb-parameters.html#sysvar_innodb_large_prefix
         */
        $max_index_length = 191;

        $wpdb->query( "
            create table {$wpdb->prefix}money_manager_categories (
                id bigint unsigned not null auto_increment primary key,
                parent_id bigint unsigned null,
                title varchar(255) not null,
                color varchar(7) not null,
                created_at timestamp null,
                updated_at timestamp null,
                created_by bigint unsigned DEFAULT 0,
                key {$wpdb->prefix}money_manager_cat_title_index (title($max_index_length))
            ) engine = innodb $collate" );
        $wpdb->query( "create table {$wpdb->prefix}money_manager_parties (
                id bigint unsigned auto_increment primary key,
                title varchar(255) not null,
                default_category_id bigint unsigned null,
                color varchar(7) not null,
                created_at timestamp null,
                updated_at timestamp null,
                created_by bigint unsigned DEFAULT 0,
                key {$wpdb->prefix}money_manager_prt_title_index (title($max_index_length))
            ) engine = innodb $collate" );
        $wpdb->query( "
            create table {$wpdb->prefix}money_manager_accounts (
                id bigint unsigned auto_increment primary key,
                title varchar(255) null,
                type enum('checking', 'card', 'cash', 'debt', 'crypto') not null,
                currency varchar(8) not null,
                balance decimal(15,3) default 0.000 null,
                initial_balance decimal(15,3) default 0.000 not null,
                notes text null,
                color varchar(7) not null,
                created_at timestamp null,
                updated_at timestamp null,
                created_by bigint unsigned DEFAULT 0,
                key {$wpdb->prefix}money_manager_acc_title_index (title($max_index_length))
            ) engine = innodb $collate" );
        $wpdb->query( "
            create table {$wpdb->prefix}money_manager_transactions (
                id bigint unsigned auto_increment primary key,
                account_id bigint unsigned not null,
                to_account_id bigint unsigned null,
                party_id bigint unsigned null,
                category_id bigint unsigned null,
                wc_order_id bigint unsigned null,
                date date not null,
                type enum('transfer', 'income', 'expense') not null,
                amount decimal(15,3) default 0.000 not null,
                to_amount decimal(15,3) null,
                notes text null,
                created_at timestamp null,
                updated_at timestamp null,
                created_by bigint unsigned DEFAULT 0,
                key {$wpdb->prefix}money_manager_txn_date_index (date),
                key {$wpdb->prefix}money_manager_txn_wc_order_id_index (wc_order_id)
            ) engine = innodb $collate" );
        $wpdb->query( "
            create table {$wpdb->prefix}money_manager_currencies (
                id bigint unsigned auto_increment primary key,
                code varchar(8) not null,
                is_base tinyint(1) default 0 not null,
                default_quote double unsigned default '1' not null,
                color varchar(7) not null,
                created_at timestamp null,
                updated_at timestamp null,
                created_by bigint unsigned DEFAULT 0
            ) engine = innodb $collate" );
        $wpdb->query( "
            create table {$wpdb->prefix}money_manager_quotes (
                id bigint unsigned auto_increment primary key,
                currency varchar(8) not null,
                date date not null,
                value double unsigned default '1' not null,
                created_at timestamp null,
                updated_at timestamp null,
                created_by bigint unsigned DEFAULT 0
            ) engine = innodb $collate" );
        $wpdb->query( "
            create table {$wpdb->prefix}money_manager_files (
                id bigint unsigned auto_increment primary key,
                account_id bigint unsigned null,
                transaction_id bigint unsigned null,
                attachment_id bigint unsigned not null,
                filename varchar(255) not null,
                description text null,
                url varchar(255) not null,
                created_at timestamp null,
                updated_at timestamp null,
                created_by bigint unsigned DEFAULT 0,
                key {$wpdb->prefix}money_manager_file_att_id_index (attachment_id)
            ) engine = innodb $collate" );
        $wpdb->query( "
            create table {$wpdb->prefix}money_manager_budgets (
                id bigint unsigned auto_increment primary key,
                date date not null,
                type enum('income', 'expenses') not null,
                amount decimal(15,3) default 0.000 null,
                currency varchar(8) not null,
                created_at timestamp null,
                updated_at timestamp null,
                created_by bigint unsigned DEFAULT 0,
                key {$wpdb->prefix}money_manager_bgt_date_index (date)
            ) engine = innodb $collate" );
        $wpdb->query( "
            create table {$wpdb->prefix}money_manager_recurring_transactions (
                id bigint unsigned auto_increment primary key,
                next_due_date date null,
                pattern varchar(255) not null,
                account_id bigint unsigned null,
                to_account_id bigint unsigned null,
                party_id bigint unsigned null,
                category_id bigint unsigned null,
                type enum('transfer', 'income', 'expense') not null,
                amount decimal(15,3) default 0.000 not null,
                to_amount decimal(15,3) null,
                notes text null,
                created_at timestamp null,
                updated_at timestamp null,
                created_by bigint unsigned DEFAULT 0,
                key {$wpdb->prefix}money_manager_rec_txn_ndd_index (next_due_date)
            ) engine = innodb $collate" );
        $wpdb->query( "
            create table {$wpdb->prefix}money_manager_notifications (
                id bigint unsigned auto_increment primary key,
                recurring_transaction_id bigint unsigned null,
                method enum('email') not null,
                enabled tinyint(1) default 0 not null,
                `to` varchar(255) null,
                subject varchar(255) null,
                message text null,
                schedule varchar(255) not null,
                next_date date null,
                sent_at timestamp null,
                created_at timestamp null,
                updated_at timestamp null,
                created_by bigint unsigned null
            ) engine = innodb $collate" );
        $wpdb->query( "
            create table {$wpdb->prefix}money_manager_split_transactions (
                id bigint unsigned auto_increment primary key,
                transaction_id bigint unsigned not null,
                category_id bigint unsigned null,
                amount decimal(15,3) default 0.000 not null,
                notes text null,
                created_at timestamp null,
                updated_at timestamp null,
                created_by bigint unsigned null
            ) engine = innodb $collate" );
    }

    /**
     * Create options
     */
    protected function create_options()
    {
        add_option( 'money_manager_version', MoneyManager()->version );
        add_option( 'money_manager_woocommerce', WooCommerce_Manager::settings() );
    }

    /**
     * Create fixtures in database
     */
    public static function create_fixtures()
    {
        $user_id = get_current_user_id();
        $existing_base_currency = Models\Currency::rows( function ( $query ) use ($user_id) {
            return $query . ' WHERE is_base = 1 AND created_by = ' . $user_id;
        });


        // If  no base currency exists, create one
        if (!$existing_base_currency) {
            // Create a new base currency for the current user
            $usd = new Models\Currency(array(
                'code' => 'USD',
                'is_base' => true,
                'color' => '#cc86a6',
                'created_by' => $user_id, // Use WordPress user ID
            ));

            // Save the currency to the database
            $usd->save();
        }
    }

    /**
     * Drop tables in database
     */
    protected function drop_tables()
    {
        global $wpdb;

        $wpdb->query( "drop table if exists {$wpdb->prefix}money_manager_split_transactions" );
        $wpdb->query( "drop table if exists {$wpdb->prefix}money_manager_notifications" );
        $wpdb->query( "drop table if exists {$wpdb->prefix}money_manager_recurring_transactions" );
        $wpdb->query( "drop table if exists {$wpdb->prefix}money_manager_budgets" );
        $wpdb->query( "drop table if exists {$wpdb->prefix}money_manager_files" );
        $wpdb->query( "drop table if exists {$wpdb->prefix}money_manager_quotes" );
        $wpdb->query( "drop table if exists {$wpdb->prefix}money_manager_currencies" );
        $wpdb->query( "drop table if exists {$wpdb->prefix}money_manager_transactions" );
        $wpdb->query( "drop table if exists {$wpdb->prefix}money_manager_accounts" );
        $wpdb->query( "drop table if exists {$wpdb->prefix}money_manager_parties" );
        $wpdb->query( "drop table if exists {$wpdb->prefix}money_manager_categories" );
    }

    /**
     * Delete options and user meta
     */
    protected function drop_options_and_meta()
    {
        global $wpdb;

        $wpdb->query( "delete from {$wpdb->options} where created_by = " . get_current_user_id() . " AND option_name like 'money\\_manager%'" );
        $wpdb->query( "delete from {$wpdb->usermeta} where created_by = " . get_current_user_id() . " AND meta_key like 'money\\_manager%'" );
    }

}