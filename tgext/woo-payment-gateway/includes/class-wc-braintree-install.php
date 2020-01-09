<?php
/**
 * @since 3.0.0
 * @package Braintree/Classes
 *
 */
class WC_Braintree_Install {

	public static function init() {
		add_filter ( 'plugin_action_links_' . WC_BRAINTREE_PLUGIN_NAME, array( 
				__CLASS__, 'plugin_action_links' 
		) );
		register_activation_hook ( WC_BRAINTREE_PLUGIN_NAME, array( 
				__CLASS__, 'install' 
		) );
	}

	public static function plugin_action_links($links) {
		$action_links = array( 
				'settings' => sprintf ( '<a href="%1$s">%2$s</a>', admin_url ( 'admin.php?page=wc-settings&tab=checkout&section=braintree_api' ), esc_html__ ( 'Settings', 'woo-payment-gateway' ) ), 
				'docs' => sprintf ( '<a target="_blank" href="https://docs.paymentplugins.com/wc-braintree/config">%s</a>', __ ( 'Documentation', 'woo-payment-gateway' ) ) 
		);
		
		return array_merge ( $action_links, $links );
	}

	public static function install() {
		if (get_option ( 'braintree_wc_version', false ) !== false) {
			return;
		}
		// check if plugin is active.
		if(is_plugin_active('woocommerce-gateway-paypal-powered-by-braintree/woocommerce-gateway-paypal-powered-by-braintree.php')){
			throw new Exception("Please deactivate all other Braintree plugins to proceed with installation");
		}
		
		/**
		 * Add necessary options
		 */
		update_option ( 'wc_braintree_fetch_sandbox_merchant_accounts', true );
		update_option ( 'wc_braintree_fetch_production_merchant_accounts', true );
		
		update_option ( 'woocommerce_queue_flush_rewrite_rules', 'yes' );
		
		if (get_option ( 'braintree_payment_settings', false ) === false) {
			// this code ensures upgrades will take place for merchants testing version 3.0.0
			update_option ( 'braintree_wc_version', braintree ()->version );
		}
	}
}
WC_Braintree_Install::init ();