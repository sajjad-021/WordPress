<?php
/*
Plugin Name: Nextend Social Login Pro Addon
Plugin URI: https://nextendweb.com/social-login/
Description: Pro Addon for Nextend Social Login.
Version: 3.0.20
Author: Nextendweb
License: GPL3
*/

if (!defined('NSL_PRO_PATH_PLUGIN')) {
    define('NSL_PRO_PATH_PLUGIN', __FILE__);
}

if (!defined('NSL_PRO_PATH')) {
    define('NSL_PRO_PATH', dirname(NSL_PRO_PATH_PLUGIN));
}

class NextendSocialLoginPRO {

    public static $version = '3.0.20';

    public static $nslMinVersion = '3.0.20';

    public static function pro($isPro) {
        if (NextendSocialLogin::hasLicense()) {
            $isPro = true;
        }

        return $isPro;
    }

    public static function init() {
        add_action('plugins_loaded', 'NextendSocialLoginPRO::plugins_loaded');
        add_action('nsl_start', 'NextendSocialLoginPRO::start');

    }

    public static function plugins_loaded() {
        if (!defined('NSL_PATH')) {
            add_action('admin_notices', array(
                'NextendSocialLoginPRO',
                'admin_notices_nsl_not_installed'
            ));
        }
    }

    public static function admin_notices_nsl_not_installed() {

        echo '<div class="error"><p>';
        printf(__('Please install and activate %1$s to use the %2$s', 'nextend-facebook-connect'), "Nextend Social Login", "Pro Addon");

        $installed_plugin = get_plugins('/nextend-facebook-connect');
        if (isset($installed_plugin['nextend-facebook-connect.php'])) {
            $file = 'nextend-facebook-connect/nextend-facebook-connect.php';

            $button_text  = __('Activate', 'nextend-facebook-connect');
            $activate_url = add_query_arg(array(
                '_wpnonce' => wp_create_nonce('activate-plugin_' . $file),
                'action'   => 'activate',
                'plugin'   => $file,
            ), network_admin_url('plugins.php'));

            if (is_network_admin()) {
                $button_text  = __('Network Activate', 'nextend-facebook-connect');
                $activate_url = add_query_arg(array('networkwide' => 1), $activate_url);
            }
            echo ' <a href="' . esc_url($activate_url) . '">' . $button_text . '</a>';
        } else {
            $slug         = 'nextend-facebook-connect';
            $activate_url = add_query_arg(array(
                '_wpnonce' => wp_create_nonce('install-plugin_' . $slug),
                'action'   => 'install-plugin',
                'plugin'   => $slug,
            ), network_admin_url('update.php'));

            echo ' <a href="' . esc_url($activate_url) . '">' . __('Install now!', 'nextend-facebook-connect') . '</a>';
        }
        echo '</p></div>';
    }

    public static function start() {
        if (NextendSocialLogin::checkVersion()) {
            $isAdminArea = defined('WP_ADMIN') && WP_ADMIN;

            if ((!$isAdminArea && NextendSocialLogin::hasLicense(false)) || ($isAdminArea && NextendSocialLogin::hasLicense())) {
                add_action('nsl_provider_init', 'NextendSocialLoginPRO::provider_init');
                add_action('nsl_add_providers', 'NextendSocialLoginPRO::addProviders');
            }
        }
    }

    /**
     * @param NextendSocialProvider $provider
     */
    public static function provider_init($provider) {
        require_once(dirname(__FILE__) . '/class-provider-extension.php');
        require_once(dirname(__FILE__) . '/class-provider-extension-with-syncdata.php');

        $extensionPath = dirname(__FILE__) . '/provider-extensions';
        if (file_exists($extensionPath . '/' . $provider->getId() . '.php')) {
            require_once($extensionPath . '/' . $provider->getId() . '.php');
            $className = 'NextendSocialLoginPROProviderExtension' . $provider->getId();
            new $className($provider);
        } else {
            new NextendSocialLoginPROProviderExtension($provider);
        }
    }

    public static function addProviders() {
        add_filter('nsl-pro', 'NextendSocialLoginPRO::pro');

        $providersPath = dirname(__FILE__) . '/providers/';

        $providers = array_diff(scandir($providersPath), array(
            '..',
            '.'
        ));

        foreach ($providers AS $provider) {
            if (file_exists($providersPath . $provider . '/' . $provider . '.php')) {
                require_once($providersPath . $provider . '/' . $provider . '.php');
            }
        }

        add_action('nsl_providers_loaded', 'NextendSocialLoginPRO::providersLoaded');

        add_action('nsl_init', 'NextendSocialLoginPRO::nsl_init');
    }


    public static function providersLoaded() {

        if (count(NextendSocialLogin::$enabledProviders)) {

            if (!is_user_logged_in()) {

                if (NextendSocialLogin::$settings->get('comment_login_button') == 'show') {
                    add_action('comment_form_must_log_in_after', 'NextendSocialLoginPRO::comment_form_must_log_in_after');

                    //Jetpack can override the default WordPress comment form, so we need to hook our buttons later.
                    if( class_exists( 'Jetpack', false ) && Jetpack::is_module_active( 'comments' ) ) {
                        add_action('comment_form_after', 'NextendSocialLoginPRO::comment_form_must_log_in_after');
                    }
                }

                $buddypress_register_button = NextendSocialLogin::$settings->get('buddypress_register_button');
                if (!empty($buddypress_register_button)) {
                    $action = 'bp_before_account_details_fields';
                    if ($buddypress_register_button == 'bp_before_register_page') {
                        $action = 'bp_before_register_page';
                    } else if ($buddypress_register_button == 'bp_after_register_page') {
                        $action = 'bp_after_register_page';
                    }
                    add_action($action, 'NextendSocialLoginPRO::bp_register_form');
                }

                //BuddyPress Login Widget
                switch (NextendSocialLogin::$settings->get('buddypress_login')) {
                    case 'show':
                        add_action('bp_login_widget_form', 'NextendSocialLoginPRO::buddypress_login');
                        break;
                }


                switch (NextendSocialLogin::$settings->get('woocommerce_login')) {
                    case 'before':
                        add_action('woocommerce_login_form_start', 'NextendSocialLoginPRO::woocommerce_login_form_start');
                        break;
                    case 'after':
                        add_action('woocommerce_login_form_end', 'NextendSocialLoginPRO::woocommerce_login_form_end');
                        break;
                }

                switch (NextendSocialLogin::$settings->get('woocommerce_register')) {
                    case 'before':
                        add_action('woocommerce_register_form_start', 'NextendSocialLoginPRO::woocommerce_register_form_start');
                        break;
                    case 'after':
                        add_action('woocommerce_register_form_end', 'NextendSocialLoginPRO::woocommerce_register_form_end');
                        break;
                }

                switch (NextendSocialLogin::$settings->get('woocommerce_billing')) {
                    case 'before':
                        add_action('woocommerce_before_checkout_billing_form', 'NextendSocialLoginPRO::woocommerce_before_checkout_billing_form');
                        break;
                    case 'after':
                        add_action('woocommerce_after_checkout_billing_form', 'NextendSocialLoginPRO::woocommerce_after_checkout_billing_form');
                        break;
                }

                add_action('mepr-login-form-before-submit', 'NextendSocialLoginPRO::memberpress_login');

                switch (NextendSocialLogin::$settings->get('memberpress_signup')) {
                    case 'before':
                        add_action('mepr-checkout-before-submit', 'NextendSocialLoginPRO::memberpress_signup');
                        break;
                }

                add_action('userpro_inside_form_register', 'NextendSocialLoginPRO::userpro_mark_register');
                add_action('userpro_social_connect_buttons', 'NextendSocialLoginPRO::userpro_login_or_register');

                //Ultimate Member
                switch (NextendSocialLogin::$settings->get('ultimatemember_login')) {
                    case 'after':
                        add_action('um_after_login_fields', 'NextendSocialLoginPRO::ultimatemember_login', 2000);
                        break;
                }
                switch (NextendSocialLogin::$settings->get('ultimatemember_register')) {
                    case 'after':
                        add_action('um_after_register_fields', 'NextendSocialLoginPRO::ultimatemember_register', 2000);
                        break;
                }

            } else {
                switch (NextendSocialLogin::$settings->get('woocommerce_account_details')) {
                    case 'before':
                        add_action('woocommerce_edit_account_form_start', 'NextendSocialLoginPRO::woocommerce_edit_account_form_start');
                        break;
                    case 'after':
                        add_action('woocommerce_edit_account_form_end', 'NextendSocialLoginPRO::woocommerce_edit_account_form_end');
                        break;
                }
            }


            switch (NextendSocialLogin::$settings->get('memberpress_account_details')) {
                case 'after':
                    add_action('mepr_account_home', 'NextendSocialLoginPRO::memberpress_account_home');
                    break;
            }

            switch (NextendSocialLogin::$settings->get('ultimatemember_account_details')) {
                case 'after':
                    add_action('um_after_account_general_button', 'NextendSocialLoginPRO::ultimatemember_account_home');
                    break;
            }
        }

        if (NextendSocialLogin::$settings->get('registration_notification_notify') != '0') {
            add_action('nsl_pre_register_new_user', 'NextendSocialLoginPRO::pre_register_new_user');
        }

        add_filter('nsl_update_settings_validate_nextend_social_login', 'NextendSocialLoginPRO::validateSettings', 10, 2);
    }

    public static function nsl_init() {

        if (NextendSocialLogin::$settings->get('allow_unlink') == 0) {
            add_filter('nsl_allow_unlink', '__return_false');
        }

        if (!empty(NextendSocialLogin::$settings->get('admin_bar_roles')) && is_user_logged_in()) {
            add_action('after_setup_theme', 'NextendSocialLoginPRO::disable_adminbar_roles');
        }

    }

    public static function validateSettings($newData, $postedData) {

        foreach ($postedData as $key => $value) {
            switch ($key) {
                case 'target':
                case 'login_form_button_style':
                case 'login_form_layout':
                case 'embedded_login_form_button_style':
                case 'embedded_login_form_layout':
                case 'comment_login_button':
                case 'comment_button_align':
                case 'comment_button_style':
                case 'buddypress_register_button':
                case 'buddypress_register_button_align':
                case 'buddypress_register_button_style':
                case 'buddypress_login':
                case 'buddypress_login_form_layout':
                case 'buddypress_login_button_style':
                case 'buddypress_sidebar_login':
                case 'woocommerce_login':
                case 'woocommerce_login_form_layout':
                case 'woocommerce_register':
                case 'woocommerce_register_form_layout':
                case 'woocommerce_billing':
                case 'woocommerce_billing_form_layout':
                case 'woocommerce_account_details':
                case 'woocoommerce_form_button_style':
                case 'woocoommerce_form_button_align':
                case 'registration_notification_notify':
                case 'memberpress_form_button_align':
                case 'memberpress_login_form_button_style':
                case 'memberpress_login_form_layout':
                case 'memberpress_signup':
                case 'memberpress_signup_form_button_style':
                case 'memberpress_signup_form_layout':
                case 'memberpress_account_details':
                case 'userpro_show_login_form':
                case 'userpro_show_register_form':
                case 'userpro_form_button_align':
                case 'userpro_login_form_button_style':
                case 'userpro_register_form_button_style':
                case 'userpro_login_form_layout':
                case 'userpro_register_form_layout':
                case 'ultimatemember_form_button_align':
                case 'ultimatemember_login':
                case 'ultimatemember_login_form_button_style':
                case 'ultimatemember_login_form_layout':
                case 'ultimatemember_register':
                case 'ultimatemember_register_form_button_style':
                case 'ultimatemember_register_form_layout':
                case 'ultimatemember_account_details':
                    $newData[$key] = sanitize_text_field($value);
                    break;

                case 'allow_unlink':
                    if ($value == '0') {
                        $newData[$key] = 0;
                    } else {
                        $newData[$key] = 1;
                    }
                    break;
                case 'admin_bar_roles':
                    $newData[$key] = array_filter((array)$value);
                    break;
            }
        }

        return $newData;
    }


    public static function comment_form_must_log_in_after() {
        $template = NextendSocialLogin::get_template_part('comment/default.php');
        if (!empty($template) && file_exists($template)) {

            $buttons = NextendSocialLogin::renderButtonsWithContainer(NextendSocialLogin::$settings->get('comment_button_style'), false, false, false, NextendSocialLogin::$settings->get('comment_button_align'));

            include($template);
        }
    }

    public static function bp_register_form() {
        $template = NextendSocialLogin::get_template_part('buddypress/register.php');
        if (!empty($template) && file_exists($template)) {

            $buttons = NextendSocialLogin::renderButtonsWithContainer(NextendSocialLogin::$settings->get('buddypress_register_button_style'), false, false, false, NextendSocialLogin::$settings->get('buddypress_register_button_align'));

            include($template);
        }
    }

    public static function woocommerceApplySocialButtonLayout($action) {
        switch ($action) {
            case 'login':
                $template = NextendSocialLogin::get_template_part('woocommerce/login/' . sanitize_file_name(NextendSocialLogin::$settings->get('woocommerce_login_form_layout')) . '.php');
                break;
            case 'register':
                $template = NextendSocialLogin::get_template_part('woocommerce/register/' . sanitize_file_name(NextendSocialLogin::$settings->get('woocommerce_register_form_layout')) . '.php');
                break;
            case 'billing':
                $template = NextendSocialLogin::get_template_part('woocommerce/billing/' . sanitize_file_name(NextendSocialLogin::$settings->get('woocommerce_billing_form_layout')) . '.php');
                break;
        }
        if (!empty($template) && file_exists($template)) {
            $index = NextendSocialLogin::$counter++;

            $containerID = 'nsl-custom-login-form-' . $index;

            echo '<div id="' . $containerID . '">' . NextendSocialLogin::renderButtonsWithContainer(NextendSocialLogin::$settings->get('woocoommerce_form_button_style'), false, false, false, NextendSocialLogin::$settings->get('woocoommerce_form_button_align')) . '</div>';

            include($template);
        }
    }

    private static function woocommerceLogin($action, $position) {

        if (NextendSocialLogin::$settings->get('woocommerce_login_form_layout') == 'default') {
            $template = NextendSocialLogin::get_template_part('woocommerce/' . $action . '-' . $position . '.php');
            if (!empty($template) && file_exists($template)) {

                $buttons = NextendSocialLogin::renderButtonsWithContainer(NextendSocialLogin::$settings->get('woocoommerce_form_button_style'), false, false, false, NextendSocialLogin::$settings->get('woocoommerce_form_button_align'));

                include($template);
            }
        } else {
            self::woocommerceApplySocialButtonLayout($action);
        }
    }

    private static function woocommerceRegister($action, $position) {

        if (NextendSocialLogin::$settings->get('woocommerce_register_form_layout') == 'default') {
            $template = NextendSocialLogin::get_template_part('woocommerce/' . $action . '-' . $position . '.php');
            if (!empty($template) && file_exists($template)) {

                $buttons = NextendSocialLogin::renderButtonsWithContainer(NextendSocialLogin::$settings->get('woocoommerce_form_button_style'), false, false, false, NextendSocialLogin::$settings->get('woocoommerce_form_button_align'));

                include($template);
            }
        } else {
            self::woocommerceApplySocialButtonLayout($action);
        }
    }

    private static function woocommerceBilling($action, $position) {

        if (NextendSocialLogin::$settings->get('woocommerce_billing_form_layout') == 'default') {
            $template = NextendSocialLogin::get_template_part('woocommerce/' . $action . '-' . $position . '.php');
            if (!empty($template) && file_exists($template)) {

                $buttons = NextendSocialLogin::renderButtonsWithContainer(NextendSocialLogin::$settings->get('woocoommerce_form_button_style'), false, false, false, NextendSocialLogin::$settings->get('woocoommerce_form_button_align'));

                include($template);
            }
        } else {
            self::woocommerceApplySocialButtonLayout($action);
        }
    }

    public static function woocommerce_login_form_start() {
        //woocommerce/login-start.php
        self::woocommerceLogin('login', 'start');
    }

    public static function woocommerce_login_form_end() {
        //woocommerce/login-end.php
        self::woocommerceLogin('login', 'end');

    }

    public static function woocommerce_register_form_start() {
        //woocommerce/register-start.php
        self::woocommerceRegister('register', 'start');

    }

    public static function woocommerce_register_form_end() {
        //woocommerce/register-end.php
        self::woocommerceRegister('register', 'end');

    }

    public static function woocommerce_before_checkout_billing_form() {
        //woocommerce/billing-before.php;
        self::woocommerceBilling('billing', 'before');
    }

    public static function woocommerce_after_checkout_billing_form() {
        //woocommerce/billing-after.php;
        self::woocommerceBilling('billing', 'after');

    }

    public static function woocommerce_edit_account_form_start() {

        $template = NextendSocialLogin::get_template_part('woocommerce/edit-account-before.php');
        if (!empty($template) && file_exists($template)) {

            $buttons = NextendSocialLogin::renderLinkAndUnlinkButtons(false, true, true, NextendSocialLogin::$settings->get('woocoommerce_form_button_align'));

            include($template);
        }
    }

    public static function woocommerce_edit_account_form_end() {

        $template = NextendSocialLogin::get_template_part('woocommerce/edit-account-after.php');
        if (!empty($template) && file_exists($template)) {

            $buttons = NextendSocialLogin::renderLinkAndUnlinkButtons(false, true, true, NextendSocialLogin::$settings->get('woocoommerce_form_button_align'));

            include($template);
        }
    }

    public static function pre_register_new_user() {
        remove_action('register_new_user', 'wp_send_new_user_notifications');


        switch (NextendSocialLogin::$settings->get('registration_notification_notify')) {
            case 'both':
                add_action('register_new_user', 'NextendSocialLoginPRO::wp_send_new_user_notifications_both');
                break;
            case 'user':
                add_action('register_new_user', 'NextendSocialLoginPRO::wp_send_new_user_notifications_user');
                break;
            case 'admin':
                add_action('register_new_user', 'NextendSocialLoginPRO::wp_send_new_user_notifications_admin');
                break;
            case 'nobody':

                break;
        }
    }

    public static function wp_send_new_user_notifications_both($user_id) {

        self::wp_send_new_user_notifications_user($user_id);
        self::wp_send_new_user_notifications_admin($user_id);
    }

    public static function wp_send_new_user_notifications_user($user_id) {
        if (class_exists('WooCommerce', false)) {
            WooCommerce::instance()
                       ->mailer();
            do_action('woocommerce_created_customer_notification', $user_id);
        } else {
            wp_new_user_notification($user_id, null, 'user');
        }

    }

    public static function wp_send_new_user_notifications_admin($user_id) {
        wp_new_user_notification($user_id, null, 'admin');
    }

    public static function memberpress_login() {

        $index = NextendSocialLogin::$counter++;

        $containerID = 'nsl-custom-login-form-' . $index;

        echo '<div id="' . $containerID . '">' . NextendSocialLogin::renderButtonsWithContainer(NextendSocialLogin::$settings->get('memberpress_login_form_button_style'), false, false, false, NextendSocialLogin::$settings->get('memberpress_form_button_align')) . '</div>';

        $template = NextendSocialLogin::get_template_part('memberpress/login/' . sanitize_file_name(NextendSocialLogin::$settings->get('memberpress_login_form_layout')) . '.php');
        if (!empty($template) && file_exists($template)) {
            include($template);
        }

    }

    public static function memberpress_signup() {

        $index = NextendSocialLogin::$counter++;

        $containerID = 'nsl-custom-login-form-' . $index;

        echo '<div id="' . $containerID . '">' . NextendSocialLogin::renderButtonsWithContainer(NextendSocialLogin::$settings->get('memberpress_signup_form_button_style'), false, false, false, NextendSocialLogin::$settings->get('memberpress_form_button_align')) . '</div>';

        $template = NextendSocialLogin::get_template_part('memberpress/sign-up/' . sanitize_file_name(NextendSocialLogin::$settings->get('memberpress_signup_form_layout')) . '.php');
        if (!empty($template) && file_exists($template)) {
            include($template);
        }
    }

    public static function memberpress_account_home() {

        $template = NextendSocialLogin::get_template_part('memberpress/account-home.php');
        if (!empty($template) && file_exists($template)) {

            $buttons = NextendSocialLogin::renderLinkAndUnlinkButtons(false, true, true, NextendSocialLogin::$settings->get('memberpress_form_button_align'));

            include($template);
        }
    }

    private static $userProIsRegister = false;

    public static function userpro_mark_register() {
        self::$userProIsRegister = true;
    }

    public static function userpro_login_or_register() {

        if (self::$userProIsRegister) {
            if (NextendSocialLogin::$settings->get('userpro_show_register_form') == 'show') {
                NextendSocialLoginPRO::userpro_register();
            }
            self::$userProIsRegister = false;
        } else {
            if (NextendSocialLogin::$settings->get('userpro_show_login_form') == 'show') {
                NextendSocialLoginPRO::userpro_login();
            }
        }
    }

    public static function userpro_login() {
        $index = NextendSocialLogin::$counter++;

        $containerID = 'nsl-custom-login-form-' . $index;

        echo '<div id="' . $containerID . '">' . NextendSocialLogin::renderButtonsWithContainer(NextendSocialLogin::$settings->get('userpro_login_form_button_style'), false, false, false, NextendSocialLogin::$settings->get('userpro_form_button_align')) . '</div>';

        $template = NextendSocialLogin::get_template_part('userpro/login/' . sanitize_file_name(NextendSocialLogin::$settings->get('userpro_login_form_layout')) . '.php');
        if (!empty($template) && file_exists($template)) {
            include($template);
        }

    }

    public static function userpro_register() {
        $index = NextendSocialLogin::$counter++;

        $containerID = 'nsl-custom-login-form-' . $index;

        echo '<div id="' . $containerID . '">' . NextendSocialLogin::renderButtonsWithContainer(NextendSocialLogin::$settings->get('userpro_register_form_button_style'), false, false, false, NextendSocialLogin::$settings->get('userpro_form_button_align')) . '</div>';

        $template = NextendSocialLogin::get_template_part('userpro/register/' . sanitize_file_name(NextendSocialLogin::$settings->get('userpro_register_form_layout')) . '.php');
        if (!empty($template) && file_exists($template)) {
            include($template);
        }
    }

    public static function ultimatemember_login() {

        $index = NextendSocialLogin::$counter++;

        $containerID = 'nsl-custom-login-form-' . $index;

        echo '<div id="' . $containerID . '">' . NextendSocialLogin::renderButtonsWithContainer(NextendSocialLogin::$settings->get('ultimatemember_login_form_button_style'), false, false, false, NextendSocialLogin::$settings->get('ultimatemember_form_button_align')) . '</div>';

        $template = NextendSocialLogin::get_template_part('ultimate-member/login/' . sanitize_file_name(NextendSocialLogin::$settings->get('ultimatemember_login_form_layout')) . '.php');
        if (!empty($template) && file_exists($template)) {
            include($template);
        }

    }

    public static function ultimatemember_register() {

        $index = NextendSocialLogin::$counter++;

        $containerID = 'nsl-custom-login-form-' . $index;

        echo '<div id="' . $containerID . '">' . NextendSocialLogin::renderButtonsWithContainer(NextendSocialLogin::$settings->get('ultimatemember_register_form_button_style'), false, false, false, NextendSocialLogin::$settings->get('ultimatemember_form_button_align')) . '</div>';

        $template = NextendSocialLogin::get_template_part('ultimate-member/register/' . sanitize_file_name(NextendSocialLogin::$settings->get('ultimatemember_register_form_layout')) . '.php');
        if (!empty($template) && file_exists($template)) {
            include($template);
        }
    }

    public static function ultimatemember_account_home() {

        $template = NextendSocialLogin::get_template_part('ultimate-member/account-home.php');
        if (!empty($template) && file_exists($template)) {

            $buttons = NextendSocialLogin::renderLinkAndUnlinkButtons(false, true, true, NextendSocialLogin::$settings->get('ultimatemember_form_button_align'));

            include($template);
        }
    }


    public static function buddypress_login() {

        $index = NextendSocialLogin::$counter++;

        $containerID = 'nsl-custom-login-form-' . $index;

        echo '<div id="' . $containerID . '">' . NextendSocialLogin::renderButtonsWithContainer(NextendSocialLogin::$settings->get('buddypress_login_button_style'), false, false, false, NextendSocialLogin::$settings->get('buddypress_register_button_align')) . '</div>';

        $template = NextendSocialLogin::get_template_part('buddypress/login/' . sanitize_file_name(NextendSocialLogin::$settings->get('buddypress_login_form_layout')) . '.php');
        if (!empty($template) && file_exists($template)) {
            include($template);
        }

    }

    public static function disable_adminbar_roles() {
        $user_info               = wp_get_current_user();
        $adminbar_disabled_roles = NextendSocialLogin::$settings->get('admin_bar_roles');

        if (is_array($adminbar_disabled_roles) && count($adminbar_disabled_roles) > 0) {
            $role_match=array_intersect($user_info->roles,$adminbar_disabled_roles);
            if($role_match){
                show_admin_bar(false);
            }
        }
    }
}

NextendSocialLoginPRO::init();