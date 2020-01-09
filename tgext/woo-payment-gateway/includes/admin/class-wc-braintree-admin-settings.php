<?php
/**
 *
 * @since 3.0.0
 * @package Braintree/Admin
 *
 */
class WC_Braintree_Admin_Settings {

	public static function init() {
		add_action ( 'woocommerce_settings_checkout', array( 
				__CLASS__, 'output' 
		) );
		add_action ( 'woocommerce_settings_checkout_braintree_advanced', array( 
				__CLASS__, 'output_advanced_settings' 
		) );
		add_action ( 'woocommerce_settings_checkout_braintree_local_gateways', array( 
				__CLASS__, 'output_local_gateways' 
		) );
		add_action ( 'woocommerce_update_options_checkout_braintree_advanced', array( 
				__CLASS__, 'save_advanced_settings' 
		) );
		add_action ( 'woocommerce_update_options_checkout_braintree_local_gateways', array( 
				__CLASS__, 'save_local_gateway' 
		) );
		add_filter ( 'wc_braintree_admin_settings_tabs', array( 
				__CLASS__, 'admin_settings_tabs' 
		), 20 );
		add_action ( 'wc_braintree_settings_before_options_braintree_advanced', array( 
				__CLASS__, 'before_options' 
		) );
		add_action ( 'wc_braintree_settings_before_options_braintree_local_gateways', array( 
				__CLASS__, 'before_options' 
		) );
		add_action ( 'woocommerce_update_options_checkout', array( 
				__CLASS__, 'deprecated_save' 
		) );
	}

	public static function output() {
		global $current_section;
		do_action ( 'woocommerce_settings_checkout_' . $current_section );
	}

	public static function output_advanced_settings() {
		self::output_custom_section ( 'braintree_merchant_account' );
	}

	public static function output_local_gateways() {
		self::output_custom_section ( 'braintree_ideal' );
	}

	public static function output_custom_section($sub_section = '') {
		global $current_section, $wc_braintree_subsection;
		$wc_braintree_subsection = isset ( $_GET[ 'sub_section' ] ) ? sanitize_title ( $_GET[ 'sub_section' ] ) : $sub_section;
		do_action ( 'woocommerce_settings_checkout_' . $current_section . '_' . $wc_braintree_subsection );
	}

	public static function save_advanced_settings() {
		self::save_custom_section ( 'braintree_merchant_account' );
	}

	public static function save_local_gateway() {
		self::save_custom_section ( 'braintree_ideal' );
	}

	public static function save_custom_section($sub_section = '') {
		global $current_section, $wc_braintree_subsection;
		$wc_braintree_subsection = isset ( $_GET[ 'sub_section' ] ) ? sanitize_title ( $_GET[ 'sub_section' ] ) : $sub_section;
		do_action ( 'woocommerce_update_options_checkout_' . $current_section . '_' . $wc_braintree_subsection );
	}

	public static function deprecated_save() {
		global $current_section;
		if ($current_section && ! did_action ( 'woocommerce_update_options_checkout_' . $current_section )) {
			do_action ( 'woocommerce_update_options_checkout_' . $current_section );
		}
	}

	public static function admin_settings_tabs($tabs) {
		$tabs[ 'braintree_local_gateways' ] = __ ( 'Local Gateways', 'woo-payment-gateway' );
		$tabs[ 'braintree_advanced' ] = __ ( 'Advanced Settings', 'woo-payment-gateway' );
		return $tabs;
	}

	public static function before_options() {
		global $current_section, $wc_braintree_subsection;
		do_action ( 'wc_braintree_settings_before_options_' . $current_section . '_' . $wc_braintree_subsection );
	}
}
WC_Braintree_Admin_Settings::init ();