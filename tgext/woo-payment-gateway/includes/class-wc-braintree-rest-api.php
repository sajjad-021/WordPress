<?php
/**
 * @since 3.0.0
 * @package Braintree/Classes
 * 
 * @property WC_Braintree_Rest_Controller $checkout
 * @property WC_Braintree_Rest_Controller $cart
 * @property WC_Braintree_Rest_Controller $applepay
 * @property WC_Braintree_Rest_Controller $merchant_account
 * @property WC_Braintree_Rest_Controller $webhook
 * @property WC_Braintree_Rest_Controller $local_payment
 * @property WC_Braintree_Rest_Controller $data_migration
 * @property WC_Braintree_Rest_Controller $googlepay
 */
class WC_Braintree_Rest_API {

	/**
	 *
	 * @var array
	 */
	private $controllers = array();

	public function __construct() {
		$this->include_classes ();
		add_action ( 'rest_api_init', array( $this, 
				'register_routes' 
		) );
	}

	public static function init() {
		add_filter ( 'woocommerce_is_rest_api_request', array( 
				__CLASS__, 'is_rest_api_request' 
		) );
	}

	/**
	 *
	 * @param WC_Braintree_Rest_Controller $key        	
	 */
	public function __get($key) {
		$controller = isset ( $this->controllers[ $key ] ) ? $this->controllers[ $key ] : '';
		if (empty ( $controller )) {
			wc_doing_it_wrong ( __FUNCTION__, sprintf ( __ ( '%1$s is an invalid controller name.', 'woo-payment-gateway' ), $key ), braintree ()->version );
		}
		return $controller;
	}

	public function __set($key, $value) {
		$this->controllers[ $key ] = $value;
	}

	private function include_classes() {
		include_once WC_BRAINTREE_PATH . 'includes/api/class-wc-braintree-rest-webhook-authentication.php';
		include_once WC_BRAINTREE_PATH . 'includes/abstract/abstract-class-wc-braintree-rest-controller.php';
		include_once WC_BRAINTREE_PATH . 'includes/api/class-wc-braintree-controller-3ds.php';
		include_once WC_BRAINTREE_PATH . 'includes/api/class-wc-braintree-controller-order-actions.php';
		include_once WC_BRAINTREE_PATH . 'includes/api/class-wc-braintree-controller-client-token.php';
		include_once WC_BRAINTREE_PATH . 'includes/api/class-wc-braintree-controller-payment-tokens.php';
		include_once WC_BRAINTREE_PATH . 'includes/api/class-wc-braintree-controller-plan.php';
		include_once WC_BRAINTREE_PATH . 'includes/api/class-wc-braintree-controller-kount.php';
		include_once WC_BRAINTREE_PATH . 'includes/api/class-wc-braintree-controller-webhook.php';
		include_once WC_BRAINTREE_PATH . 'includes/api/class-wc-braintree-controller-checkout.php';
		include_once WC_BRAINTREE_PATH . 'includes/api/class-wc-braintree-controller-cart.php';
		include_once WC_BRAINTREE_PATH . 'includes/api/class-wc-braintree-controller-applepay.php';
		include_once WC_BRAINTREE_PATH . 'includes/api/class-wc-braintree-controller-googlepay.php';
		include_once WC_BRAINTREE_PATH . 'includes/api/class-wc-braintree-controller-merchant-account.php';
		include_once WC_BRAINTREE_PATH . 'includes/api/class-wc-braintree-controller-local-payment.php';
		include_once WC_BRAINTREE_PATH . 'includes/api/class-wc-braintree-controller-data-migration.php';
		
		/**
		 * In versions prior to 3.0.0, it was possible to create a WCS that used Braintree's recurring billing engine.
		 * This functionality has been removed, but the API must still be able to create renewal orders from the webhooks in case
		 * merchants still have active subscriptions that use this model.
		 */
		include_once WC_BRAINTREE_PATH . 'includes/deprecated/api/class-wcs-braintree-controller.php';
		
		foreach ( $this->get_controllers () as $key => $class_name ) {
			if (class_exists ( $class_name )) {
				$this->{$key} = new $class_name ();
			}
		}
	}

	public function register_routes() {
		WC ()->payment_gateways ();
		foreach ( $this->controllers as $key => $controller ) {
			if (is_callable ( array( $controller, 
					'register_routes' 
			) )) {
				$controller->{ 'register_routes' } ();
			}
		}
	}

	public function get_controllers() {
		$controllers = array( 
				'checkout' => 'WC_Braintree_Controller_Checkout', 
				'cart' => 'WC_Braintree_Controller_Cart', 
				'_3ds' => 'WC_Braintree_Controller_3ds', 
				'order_actions' => 'WC_Braintree_Controller_Order_Actions', 
				'client_token' => 'WC_Braintree_Controller_Client_Token', 
				'tokens' => 'WC_Braintree_Controller_Payment_Tokens', 
				'plans' => 'WC_Braintree_Controller_Plan', 
				'kount' => 'WC_Braintree_Controller_Kount', 
				'applepay' => 'WC_Braintree_Controller_ApplePay', 
				'merchant_account' => 'WC_Braintree_Controller_Merchant_Accounts', 
				'local_payment' => 'WC_Braintree_Controller_Local_Payment', 
				'data_migration' => 'WC_Braintree_Controller_Data_Migration', 
				'googlepay' => 'WC_Braintree_Controller_GooglePay' 
		);
		if (wcs_braintree_active ()) {
			// deprecated functionality when the plugin used to allow WCS that processed in Braintree
			$controllers[ 'webhook' ] = 'WCS_Braintree_Subscription_Controller';
		} else {
			$controllers[ 'webhook' ] = 'WC_Braintree_Controller_Webhook';
		}
		return apply_filters ( 'wc_braintree_api_controllers', $controllers );
	}

	public function rest_url() {
		return braintree ()->rest_url ();
	}

	public function rest_uri() {
		return braintree ()->rest_uri ();
	}

	/**
	 * Added after WC 3.6 so Cart, Customer, and Session are loaded for Braintree rest requests.
	 *
	 * @param unknown $bool        	
	 */
	public static function is_rest_api_request($bool) {
		if (! empty ( $_SERVER[ 'REQUEST_URI' ] ) && strpos ( $_SERVER[ 'REQUEST_URI' ], braintree ()->rest_uri () ) !== - 1) {
			$bool = false;
		}
		return $bool;
	}
}
WC_Braintree_Rest_API::init ();