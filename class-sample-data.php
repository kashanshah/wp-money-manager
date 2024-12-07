<?php

namespace MoneyManager;

defined( 'ABSPATH' ) || die( 'No direct script access allowed.' );

use MoneyManager\Managers\Account_Manager;

/**
 * Class Sample_Data
 * @package MoneyManager
 */
class Sample_Data
{
    /**
     * Check whether the sample data has ever been imported
     *
     * @return bool
     */
    public static function imported()
    {
        return (bool) get_option( 'money_manager_sample_data_imported' );
    }

    /**
     * Import sample data
     */
    public function import()
    {
        if ( get_transient( 'money_manager_importing_sample_data' ) == 'yes' ) {
            return;
        }

        set_transient( 'money_manager_importing_sample_data', 'yes', MINUTE_IN_SECONDS * 5 );

        $eur = new Models\Currency( array(
            'code' => 'GBP',
            'default_quote' => '1.4',
            'color' => '#8187fc',
            'created_by' => get_current_user_id(),
        ) );
        $eur->save();

        $income = new Models\Category( array(
            'title' => __( 'Income', 'money-manager' ),
            'color' => '#cc86a6',
            'created_by' => get_current_user_id(),
        ) );
        $income->save();

        $expenses = new Models\Category( array(
            'title' => __( 'Expenses', 'money-manager' ),
            'color' => '#84afb3',
            'created_by' => get_current_user_id(),
        ) );
        $expenses->save();

        $other = new Models\Category( array(
            'title' => __( 'Other', 'money-manager' ),
            'color' => '#d1d8a2',
            'created_by' => get_current_user_id(),
        ) );
        $other->save();

        $sales = new Models\Category( array(
            'title' => __( 'Sales', 'money-manager' ),
            'parent_id' => $income->id,
            'color' => '#8187fc',
            'created_by' => get_current_user_id(),
        ) );
        $sales->save();

        $rent = new Models\Category( array(
            'title' => __( 'Rent', 'money-manager' ),
            'parent_id' => $expenses->id,
            'color' => '#ae938c',
            'created_by' => get_current_user_id(),
        ) );
        $rent->save();

        $insurance = new Models\Category( array(
            'title' => __( 'Insurance', 'money-manager' ),
            'parent_id' => $expenses->id,
            'color' => '#b6afa6',
            'created_by' => get_current_user_id(),
        ) );
        $insurance->save();

        $bank_acc_usd = new Models\Account( array(
            'title' => __( 'Bank of America', 'money-manager' ),
            'type' => 'checking',
            'currency' => 'USD',
            'color' => '#ff7700',
            'created_by' => get_current_user_id(),
        ) );
        $bank_acc_usd->save();

        $bank_acc_gbp = new Models\Account( array(
            'title' => __( 'Bank of England', 'money-manager' ),
            'type' => 'checking',
            'currency' => 'GBP',
            'color' => '#00947e',
            'created_by' => get_current_user_id(),
        ) );
        $bank_acc_gbp->save();

        $visa_card = new Models\Account( array(
            'title' => __( 'VISA card', 'money-manager' ),
            'type' => 'card',
            'currency' => 'USD',
            'color' => '#296fa8',
            'created_by' => get_current_user_id(),
        ) );
        $visa_card->save();

        $cash_desk = new Models\Account( array(
            'title' => __( 'Cash desk', 'money-manager' ),
            'type' => 'cash',
            'currency' => 'USD',
            'color' => '#fba85a',
            'created_by' => get_current_user_id(),
        ) );
        $cash_desk->save();

        $loan = new Models\Account( array(
            'title' => __( 'Loan', 'money-manager' ),
            'type' => 'debt',
            'currency' => 'USD',
            'color' => '#d37870',
            'created_by' => get_current_user_id(),
        ) );
        $loan->save();

        $bank = new Models\Party( array(
            'title' => __( 'Bank of America', 'money-manager' ),
            'default_category_id' => $other->id,
            'color' => '#8fcaca',
            'created_by' => get_current_user_id(),
        ) );
        $bank->save();

        $clients = new Models\Party( array(
            'title' => __( 'My clients', 'money-manager' ),
            'default_category_id' => $sales->id,
            'color' => '#fcb9aa',
            'created_by' => get_current_user_id(),
        ) );
        $clients->save();

        $ins_company = new Models\Party( array(
            'title' => __( 'Insurance Company', 'money-manager' ),
            'default_category_id' => $insurance->id,
            'color' => '#b6cfb6',
            'created_by' => get_current_user_id(),
        ) );
        $ins_company->save();

        $landlord = new Models\Party( array(
            'title' => __( 'Landlord', 'money-manager' ),
            'default_category_id' => $rent->id,
            'color' => '#55cbcd',
            'created_by' => get_current_user_id(),
        ) );
        $landlord->save();

        $date = current_datetime();
        ( new Models\Transaction( array(
            'account_id' => $loan->id,
            'to_account_id' => $bank_acc_usd->id,
            'party_id' => $bank->id,
            'category_id' => $other->id,
            'date' => $date->modify( '2 months ago' )->format( 'Y-m-d' ),
            'type' => 'transfer',
            'amount' => 30000,
            'to_amount' => 30000,
            'created_by' => get_current_user_id(),
        ) ) )->save();
        ( new Models\Transaction( array(
            'account_id' => $bank_acc_usd->id,
            'party_id' => $clients->id,
            'category_id' => $sales->id,
            'date' => $date->modify( '2 months ago' )->format( 'Y-m-d' ),
            'type' => 'income',
            'amount' => 4200,
            'to_amount' => 4200,
            'created_by' => get_current_user_id(),
        ) ) )->save();
        ( new Models\Transaction( array(
            'account_id' => $bank_acc_usd->id,
            'party_id' => $ins_company->id,
            'category_id' => $insurance->id,
            'date' => $date->modify( '2 months ago' )->format( 'Y-m-d' ),
            'type' => 'expense',
            'amount' => 2500,
            'to_amount' => 2500,
            'created_by' => get_current_user_id(),
        ) ) )->save();
        ( new Models\Transaction( array(
            'account_id' => $bank_acc_usd->id,
            'party_id' => $ins_company->id,
            'category_id' => $insurance->id,
            'date' => $date->modify( '1 month ago' )->format( 'Y-m-d' ),
            'type' => 'expense',
            'amount' => 1200,
            'to_amount' => 1200,
            'created_by' => get_current_user_id(),
        ) ) )->save();
        ( new Models\Transaction( array(
            'account_id' => $cash_desk->id,
            'party_id' => $clients->id,
            'category_id' => $sales->id,
            'date' => $date->modify( '1 month ago' )->format( 'Y-m-d' ),
            'type' => 'income',
            'amount' => 2600,
            'to_amount' => 2600,
            'created_by' => get_current_user_id(),
        ) ) )->save();
        ( new Models\Transaction( array(
            'account_id' => $bank_acc_usd->id,
            'party_id' => $landlord->id,
            'category_id' => $rent->id,
            'date' => $date->modify( '2 weeks ago' )->format( 'Y-m-d' ),
            'type' => 'expense',
            'amount' => 2500,
            'to_amount' => 2500,
            'created_by' => get_current_user_id(),
        ) ) )->save();
        ( new Models\Transaction( array(
            'account_id' => $cash_desk->id,
            'party_id' => $clients->id,
            'category_id' => $sales->id,
            'date' => $date->modify( '2 weeks ago' )->format( 'Y-m-d' ),
            'type' => 'income',
            'amount' => 2500,
            'to_amount' => 2500,
            'created_by' => get_current_user_id(),
        ) ) )->save();
        ( new Models\Transaction( array(
            'account_id' => $cash_desk->id,
            'party_id' => $clients->id,
            'category_id' => $sales->id,
            'date' => $date->modify( '1 week ago' )->format( 'Y-m-d' ),
            'type' => 'income',
            'amount' => 3100,
            'to_amount' => 3100,
            'created_by' => get_current_user_id(),
        ) ) )->save();
        ( new Models\Transaction( array(
            'account_id' => $cash_desk->id,
            'to_account_id' => $bank_acc_usd->id,
            'category_id' => $other->id,
            'date' => $date->format( 'Y-m-d' ),
            'type' => 'transfer',
            'amount' => 2800,
            'to_amount' => 2800,
            'created_by' => get_current_user_id(),
        ) ) )->save();
        ( new Models\Transaction( array(
            'account_id' => $bank_acc_usd->id,
            'to_account_id' => $bank_acc_gbp->id,
            'category_id' => $other->id,
            'date' => $date->format( 'Y-m-d' ),
            'type' => 'transfer',
            'amount' => 2800,
            'to_amount' => 2000,
            'created_by' => get_current_user_id(),
        ) ) )->save();
        ( new Models\Transaction( array(
            'account_id' => $bank_acc_usd->id,
            'to_account_id' => $visa_card->id,
            'category_id' => $other->id,
            'date' => $date->format( 'Y-m-d' ),
            'type' => 'transfer',
            'amount' => 5000,
            'to_amount' => 5000,
            'created_by' => get_current_user_id(),
        ) ) )->save();

        Account_Manager::refresh_balance( $bank_acc_usd->id );
        Account_Manager::refresh_balance( $bank_acc_gbp->id );
        Account_Manager::refresh_balance( $visa_card->id );
        Account_Manager::refresh_balance( $cash_desk->id );
        Account_Manager::refresh_balance( $loan->id );

        update_option( 'money_manager_sample_data_imported', true );

        delete_transient( 'money_manager_importing_sample_data' );
    }
}