<?php
/**
 * Simple class that loads plugin files instead of theme files if certain conditions are met.
 * @since 3.0.0
 * @package Braintree/Classes
 *
 */
class WC_Braintree_Template_Loader {

	public static function init() {
		add_filter ( 'template_include', array( __CLASS__, 
				'template_loader' 
		) );
	}

	public static function template_loader($template) {
		if (isset ( $_GET[ 'wc-braintree' ] ) && 'shipping-methods' === $_GET[ 'wc-braintree' ]) {
			$address = array( 
					'first_name' => isset ( $_GET[ 'first_name' ] ) ? wc_clean ( $_GET[ 'first_name' ] ) : '', 
					'last_name' => isset ( $_GET[ 'last_name' ] ) ? wc_clean ( $_GET[ 'last_name' ] ) : '', 
					'address_1' => isset ( $_GET[ 'address_1' ] ) ? wc_clean ( $_GET[ 'address_1' ] ) : '', 
					'address_2' => isset ( $_GET[ 'address_2' ] ) ? wc_clean ( $_GET[ 'address_2' ] ) : '', 
					'city' => isset ( $_GET[ 'city' ] ) ? wc_clean ( $_GET[ 'city' ] ) : '', 
					'state' => isset ( $_GET[ 'state' ] ) ? wc_clean ( $_GET[ 'state' ] ) : '', 
					'postcode' => isset ( $_GET[ 'postcode' ] ) ? wc_clean ( $_GET[ 'postcode' ] ) : '', 
					'country' => isset ( $_GET[ 'country' ] ) ? wc_clean ( $_GET[ 'country' ] ) : '' 
			);
			wc_braintree_update_customer_location ( $address );
			wc_maybe_define_constant ( 'WOOCOMMERCE_CART', true );
			$template = braintree ()->plugin_path () . 'templates/paypal-shipping-methods.php';
		}
		return $template;
	}
}
WC_Braintree_Template_Loader::init ();