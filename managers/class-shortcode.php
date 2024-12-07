<?php

namespace MoneyManager\Managers;

defined( 'ABSPATH' ) || die( 'No direct script access allowed.' );

use MoneyManager\I18n;

/**
 * Class Home_Page
 * @package MoneyManager
 */
class Shortcode {
    /**
     * Init home page
     */
    public static function init()
    {
        add_shortcode('wp_money_manager', array( new self(), 'display_admin_page_content' ));
    }


    /**
     * Display admin page content
     */
    public function display_admin_page_content() {
        // Check if logged in
        if(!is_user_logged_in()) {
            // Add custom styles for the login form
            add_action('wp_footer', [$this, 'add_login_form_styles']);

            // Render the WordPress login form
            ob_start();
            echo '<div class="login-form-wrapper">';
            wp_login_form([
                'redirect' => esc_url(home_url($_SERVER['REQUEST_URI'])), // Redirect back to the current page after login
                'label_username' => __('Username or Email Address', 'money-manager'),
                'label_password' => __('Password', 'money-manager'),
                'label_log_in' => __('Log In', 'money-manager'),
                'remember' => true,
            ]);
            echo '</div>';
            return ob_get_clean();
        }

        // Enqueue necessary assets
        $this->enqueue_assets();
        return $this->render_page();
    }

    public function add_login_form_styles() {
        echo '
    <style>
        .login-form-wrapper * {
            box-sizing: border-box;
        }
        .login-form-wrapper {
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .login-form-wrapper form {
            margin: 0;
        }
        .login-form-wrapper h2 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 24px;
            color: #333;
        }
        .login-form-wrapper p {
            margin-bottom: 15px;
        }
        .login-form-wrapper label {
            font-size: 14px;
            color: #555;
        }
        .login-form-wrapper input[type="text"],
        .login-form-wrapper input[type="password"] {
            width: 100%;
            padding: 10px;
            font-size: 14px;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-top: 5px;
        }
        .login-form-wrapper input[type="checkbox"] {
            margin-right: 5px;
        }
        .login-form-wrapper .button-primary {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            background-color: #0073aa;
            border: none;
            border-radius: 4px;
            color: #fff;
            text-transform: uppercase;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .login-form-wrapper .button-primary:hover {
            background-color: #005177;
        }
        .login-form-wrapper .login-remember {
            display: flex;
            align-items: center;
        }
        .login-form-wrapper .login-remember label {
            font-size: 14px;
        }
        .login-form-wrapper .login-submit {
            margin-top: 20px;
        }
    </style>
    ';
    }


    /**
     * Render home page
     */
    public function render_page()
    {
        return '<div id="money-manager" class="frontend-div"></div>';
    }
    /**
     * Enqueue assets for home page
     */
    public static function enqueue_assets()
    {
        // Media Library
        wp_enqueue_media();

        wp_enqueue_script(
            'wp-money-manager-app.min.js',
            plugins_url( 'js/app.min.js', MONEY_MANAGER_PLUGIN_FILE ),
            array( 'media-editor' )
        );
        wp_enqueue_style(
            'fontawesome.min.css',
            plugins_url( 'css/fontawesome.min.css', MONEY_MANAGER_PLUGIN_FILE )
        );
        wp_enqueue_style(
            'wp-money-manager-app.min.css',
            plugins_url( 'css/app.min.css', MONEY_MANAGER_PLUGIN_FILE )
        );
        wp_enqueue_style(
            'wp-money-manager-frontend.css',
            plugins_url( 'css/frontend.css', MONEY_MANAGER_PLUGIN_FILE )
        );

        wp_localize_script(
            'wp-money-manager-app.min.js',
            'MoneyManagerSettings',
            apply_filters(
                'money_manager_app_js_options',
                array(
                    'endpoint' => esc_url_raw( rest_url() ) . 'money-manager/v1',
                    'nonce' => wp_create_nonce( 'wp_rest' ),
                    'meta' => get_user_meta( get_current_user_id(), 'money_manager', true ) ?: array(),
                    'locale' => str_replace( '_', '-', get_locale() ),
                    'i18n' => I18n::getStrings(),
                ) + self::get_wc_options()
            )
        );
    }

    /**
     * Get JS options for WooCommerce
     *
     * @return array
     */
    public static function get_wc_options()
    {
        if ( WooCommerce_Manager::active() ) {
            $payment_methods = array();
            $order_statuses = array();

            foreach ( WC()->payment_gateways->get_available_payment_gateways() as $gateway ) {
                $payment_methods[] = array(
                    'id' => $gateway->id,
                    'name' => $gateway->title,
                );
            }

            foreach ( wc_get_order_statuses() as $key => $name ) {
                $id = str_replace( 'wc-', '', $key );
                $order_statuses[] = compact( 'id', 'name' );
            }

            return array(
                'woocommerce' => true,
                'woocommerce_payment_methods' => $payment_methods,
                'woocommerce_order_statuses' => $order_statuses,
            );
        }

        return array(
            'woocommerce' => false,
        );
    }
}
