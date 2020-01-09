<?php
/**
 * 
 * @since 3.0.0
 * @package Braintree/Classes
 *
 */
class WC_Braintree_Frontend_Scripts {

	public $braintree_version;

	private $registered_scripts = array();

	private $registered_styles = array();

	private $enqueued_scripts = array();

	private $enqueued_styles = array();

	private $localized_scripts = array();

	private $update_payment_method_request;

	private $client_manager_enqueued = false;

	public $global_scripts = array( 
			'hosted-fields-v3' => 'https://js.braintreegateway.com/web/%1$s/js/hosted-fields.min.js', 
			'dropin-v3-ext' => 'https://js.braintreegateway.com/web/dropin/1.21.0/js/dropin.min.js', 
			'client-v3' => 'https://js.braintreegateway.com/web/%1$s/js/client.min.js', 
			'data-collector-v3' => 'https://js.braintreegateway.com/web/%1$s/js/data-collector.min.js', 
			'3ds-v3' => 'https://js.braintreegateway.com/web/%1$s/js/three-d-secure.min.js', 
			'paypal-v3' => 'https://js.braintreegateway.com/web/%1$s/js/paypal.min.js', 
			'paypal-checkout-v3' => 'https://js.braintreegateway.com/web/%1$s/js/paypal-checkout.js', 
			'paypal-objects' => 'https://www.paypalobjects.com/api/checkout.js', 
			'googlepay-v3' => 'https://js.braintreegateway.com/web//%1$s/js/google-payment.min.js', 
			'googlepay-pay' => 'https://pay.google.com/gp/p/js/pay.js', 
			'applepay-v3' => 'https://js.braintreegateway.com/web/%1$s/js/apple-pay.min.js', 
			'venmo-v3' => 'https://js.braintreegateway.com/web/%1$s/js/venmo.min.js', 
			'local-payment-v3' => 'https://js.braintreegateway.com/web/%1$s/js/local-payment.min.js' 
	);

	public $prefix = 'wc-braintree-';

	public function __construct() {
		$this->braintree_version = '3.56.0';
		
		add_action ( 'wp_enqueue_scripts', array( $this, 
				'enqueue_scripts' 
		) );
		add_action ( 'wp_print_scripts', array( $this, 
				'print_scripts' 
		), 5 );
		add_action ( 'woocommerce_subscriptions_pre_update_payment_method', array( 
				$this, 'pre_update_payment_method' 
		) );
	}

	public function enqueue_scripts() {
		$this->register_scripts ();
	}

	private function register_scripts() {
		$this->braintree_version = apply_filters ( 'wc_braintree_global_script_version', $this->braintree_version );
		
		foreach ( $this->global_scripts as $key => $src ) {
			$this->register_script ( $key, sprintf ( $src, $this->braintree_version ) );
		}
		$js_path = braintree ()->assets_path () . 'js/';
		$min = $this->get_min ();
		
		$this->register_script ( 'payment-methods', $this->assets_url ( 'js/frontend/payment-methods' . $this->get_min () . '.js' ), array( 
				'jquery', 'select2' 
		) );
		$this->register_script ( 'message-handler', $this->assets_url ( 'js/frontend/message-handler' . $this->get_min () . '.js' ), array( 
				'jquery' 
		) );
		$this->register_script ( 'form-handler', $this->assets_url ( 'js/frontend/form-handler' . $this->get_min () . '.js' ), array( 
				'jquery' 
		) );
		$this->register_script ( 'payment-method-icons', $this->assets_url ( 'js/frontend/payment-method-icons.js' ), array( 
				'jquery' 
		) );
		$this->register_script ( 'global', $this->assets_url ( 'js/frontend/wc-braintree' . $this->get_min () . '.js' ), array( 
				'jquery' 
		) );
		// register Promise Polyfill so older browsers can support it.
		$this->register_script ( 'promise-polyfill', $this->assets_url ( 'js/frontend/promise-polyfill.min.js' ) );
		
		$this->register_script ( 'client-manager', $js_path . 'frontend/client-manager' . $min . '.js', array( 
				'jquery', $this->prefix . 'client-v3', 
				$this->get_handle ( 'message-handler' ), 
				$this->get_handle ( 'payment-methods' ), 
				$this->get_handle ( 'form-handler' ), 
				$this->get_handle ( 'global' ), 
				$this->get_handle ( 'promise-polyfill' ) 
		) );
		
		$this->register_script ( 'paypal-checkout', $this->assets_url ( 'js/frontend/paypal-checkout.js' ), array() );
		
		$this->register_script ( 'change-payment-method', $this->assets_url ( 'js/frontend/change-payment-method' . $this->get_min () . '.js' ), array( 
				'jquery' 
		) );
	}

	public function assets_url($uri = '') {
		return untrailingslashit ( braintree ()->assets_path () . $uri );
	}

	public function get_min() {
		return defined ( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
	}

	public function register_script($handle, $src, $deps = array(), $version = '', $footer = true) {
		$this->registered_scripts[] = $this->get_handle ( $handle );
		wp_register_script ( $this->get_handle ( $handle ), $src, $deps, braintree ()->version, $footer );
	}

	public function register_style($handle, $src, $deps = array()) {
		$this->registered_styles[] = $this->get_handle ( $handle );
		wp_register_style ( $handle, $src, $deps, braintree ()->version );
	}

	public function enqueue_style($handle, $src, $deps = array()) {
		$handle = $this->get_handle ( $handle );
		if (! in_array ( $handle, $this->registered_styles )) {
			$this->register_style ( $handle, $src, $deps );
		}
		wp_enqueue_style ( $handle, $src, $deps, braintree ()->version );
	}

	public function enqueue_script($handle, $src = '', $deps = array(), $version = '', $footer = true) {
		$handle = $this->get_handle ( $handle );
		if (! in_array ( $handle, $this->registered_scripts )) {
			$this->register_script ( $handle, $src, $deps, $version, $footer );
		}
		$this->enqueued_scripts[] = $handle;
		wp_enqueue_script ( $handle );
	}

	public function localize_script($handle, $data) {
		$handle = $this->get_handle ( $handle );
		if (wp_script_is ( $handle, 'registered' )) {
			$name = str_replace ( $this->prefix, '', $handle );
			$data = apply_filters ( 'wc_braintree_localize_script_' . $name, $data, $name );
			if ($data) {
				$this->localized_scripts[] = $handle;
				$object_name = str_replace ( '-', '_', $handle ) . '_params';
				wp_localize_script ( $handle, $object_name, $data );
			}
		}
	}

	/**
	 * Localize scripts registered by this plugin.
	 */
	public function print_scripts() {
		global $wp;
		if (is_checkout () || is_add_payment_method_page () || is_product () || is_cart () || is_account_page () || apply_filters ( 'wc_braintree_print_scripts', false )) {
			
			$this->enqueue_style ( 'styles', $this->assets_url ( 'css/braintree.css' ) );
			
			$this->localize_script ( 'client-manager', $this->localize_client_manager () );
			
			$this->localize_script ( 'message-handler', $this->localize_message_handler () );
			
			$this->localize_script ( 'payment-methods', $this->localize_payment_methods () );
			
			$client_token = braintree ()->generate_client_token ();
			
			wp_localize_script ( $this->get_handle ( 'client-manager' ), 'wc_braintree_client_token', empty ( $client_token ) ? null : $client_token );
		}
		if (is_add_payment_method_page ()) {
			$this->enqueue_script ( 'payment-method-icons' );
			$this->localize_script ( 'payment-method-icons', $this->localize_payment_method_icons () );
		}
		if (wc_braintree_subscriptions_active () && is_account_page ()) {
			if (! empty ( $wp->query_vars[ 'view-subscription' ] )) {
				$this->enqueue_script ( 'view-subscription', $this->assets_url ( 'js/frontend/view-subscription.js' ), array( 
						'jquery', 'jquery-ui-dialog' 
				), braintree ()->version, true );
				wp_enqueue_style ( 'wp-jquery-ui-dialog' );
				$this->localize_script ( 'view-subscription', array() );
			}
			if (wcs_braintree_is_change_payment_method_request ()) {
				$this->enqueue_script ( 'change-payment-methods', $this->assets_url ( 'js/frontend/change-payment-method' . $this->get_min () . '.js' ), array( 
						'jquery' 
				) );
			}
		}
	}

	public function get_handle($handle) {
		return strpos ( $handle, $this->prefix ) === false ? $this->prefix . $handle : $handle;
	}

	public function localize_payment_methods() {
		$data[ 'cards' ] = array_keys ( wc_braintree_get_card_type_icons () );
		return $data;
	}

	public function localize_message_handler() {
		$data[ 'messages' ] = wc_braintree_get_error_messages ();
		return $data;
	}

	public function localize_payment_method_icons() {
		$data = array( 'tokens' => array(), 
				'icons' => wc_braintree_get_card_type_icons () 
		);
		$tokens = wc_get_customer_saved_methods_list ( get_current_user_id () );
		$index = 0;
		foreach ( $tokens as $type => $methods ) {
			foreach ( $methods as $method ) {
				if (isset ( $method[ 'wc_braintree_method' ] )) {
					$data[ 'tokens' ][] = array( 
							'index' => $index, 
							'card_type' => $method[ 'method_type' ] 
					);
				}
				$index ++;
			}
		}
		return $data;
	}

	public function localize_client_manager() {
		return array( 
				'url' => braintree ()->rest_api->client_token->rest_url () . 'create', 
				'_wpnonce' => wp_create_nonce ( 'wp_rest' ) 
		);
	}

	public function pre_update_payment_method() {
		$this->update_payment_method_request = true;
	}
}