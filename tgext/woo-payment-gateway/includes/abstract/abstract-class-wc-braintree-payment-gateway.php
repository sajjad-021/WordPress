<?php
if (! class_exists ( 'WC_Payment_Gateway' )) {
	return;
}
/**
 * Abstract class that is meant to be extended by Braintree payment methods.
 *
 * @version 3.0.0
 * @package Braintree/Abstracts
 *         
 */
abstract class WC_Braintree_Payment_Gateway extends WC_Payment_Gateway {

	public $nonce_key;

	public $token_key;

	public $device_data_key;

	public $save_method_key;

	public $payment_type_key;

	public $config_key = '';

	/**
	 * The token type used to create WC_Payment_Token objects.
	 *
	 * @var string
	 */
	protected $token_type;

	protected $deprecated_id = '';

	/**
	 *
	 * @var array
	 */
	protected $client_token = '';

	/**
	 * Token that represents a vaulted payment method in Braintree.
	 * Example: cby23j
	 *
	 * @var string
	 */
	protected $payment_method_token = null;

	/**
	 * Nonce that represents a once time use payment method.
	 *
	 * @var string
	 */
	protected $payment_method_nonce = null;

	protected $update_payment_method_request = false;

	protected $tab_title = '';

	/**
	 * Template that points to where the gateway's html is rendered.
	 *
	 * @var string
	 */
	public $template = '';

	/**
	 *
	 * @var bool Indicator for when payment is being processed.
	 */
	public $processing_payment;

	/**
	 *
	 * @deprecated
	 *
	 * @var string
	 */
	public static $nonce_id = '';

	/**
	 *
	 * @deprecated
	 *
	 * @var string
	 */
	public static $device_data_id = '';

	public static function init() {
		add_action ( 'woocommerce_order_status_completed', array( 
				__CLASS__, 'capture_transaction_from_status' 
		), 10, 2 );
		add_action ( 'woocommerce_order_status_cancelled', array( 
				__CLASS__, 'void_transaction_from_status' 
		), 10, 2 );
		add_filter ( 'woocommerce_available_payment_gateways', array( 
				__CLASS__, 'available_payment_gateways' 
		) );
		add_action ( 'woocommerce_process_shop_subscription_meta', array( 
				__CLASS__, 'process_shop_subscription_meta' 
		), 10, 2 );
		add_action ( 'woocommerce_scheduled_subscription_payment', array( 
				__CLASS__, 'deprecated_subscription_check' 
		), - 1000 );
	}

	/**
	 *
	 * @var \Braintree\Gateway
	 */
	public $gateway;

	public function __construct() {
		$this->has_fields = true;
		$this->nonce_key = $this->id . '_nonce_key';
		$this->token_key = $this->id . '_token_key';
		$this->device_data_key = $this->id . '_device_data';
		$this->save_method_key = $this->id . '_save_method';
		$this->payment_type_key = $this->id . '_payment_type';
		$this->config_key = $this->id . '_config_data';
		$this->set_supports ();
		$this->init_form_fields ();
		$this->init_settings ();
		$this->title = $this->get_option ( 'title_text' );
		$this->description = $this->get_option ( 'description' );
		$this->add_hooks ();
		$this->connect ();
	}

	/**
	 * Add all standard filters
	 */
	public function add_hooks() {
		add_action ( 'woocommerce_settings_checkout_' . $this->id, array( 
				$this, 'enqueue_admin_scripts' 
		) );
		add_action ( 'woocommerce_update_options_payment_gateways_' . $this->id, array( 
				$this, 'process_admin_options' 
		) );
		add_action ( 'woocommerce_subscriptions_pre_update_payment_method', array( 
				$this, 'pre_update_payment_method' 
		), 10, 2 );
		add_action ( 'wc_braintree_payment_token_deleted_' . $this->id, array( 
				$this, 'delete_payment_method' 
		), 10, 2 );
		add_filter ( 'woocommerce_subscription_payment_meta', array( 
				$this, 'subscription_payment_meta' 
		), 10, 2 );
		add_action ( 'woocommerce_subscription_payment_method_updated_to_' . $this->id, array( 
				$this, 'subscription_payment_method_updated' 
		), 10, 1 );
		add_action ( 'woocommerce_scheduled_subscription_payment_' . $this->id, array( 
				$this, 'process_subscription_payment' 
		), 10, 2 );
		add_action ( 'woocommerce_subscription_failing_payment_method_updated_' . $this->id, array( 
				$this, 'update_failing_payment_method' 
		), 10, 2 );
		add_filter ( 'wc_braintree_admin_settings_tabs', array( 
				$this, 'admin_settings_tabs' 
		) );
		add_action ( 'wc_braintree_before_process_order_' . $this->id, array( 
				$this, 'pre_order_before_process_order' 
		) );
		add_action ( 'wc_braintree_before_process_order_' . $this->id, array( 
				$this, 'wcs_before_process_order' 
		) );
		add_action ( 'wc_braintree_before_process_order_' . $this->id, array( 
				$this, 'wcs_braintree_before_process_order' 
		) );
		/**
		 *
		 * @since 3.0.6
		 */
		add_action ( 'wc_pre_orders_process_pre_order_completion_payment_' . $this->id, array( 
				$this, 'process_pre_order_payment' 
		) );
	}

	public function set_supports() {
		$this->supports = array( 'tokenization', 
				'subscriptions', 'products', 
				'add_payment_method', 
				'subscription_cancellation', 
				'multiple_subscriptions', 
				'subscription_amount_changes', 
				'subscription_date_changes', 
				'default_credit_card_form', 'refunds', 
				'pre-orders', 
				'subscription_payment_method_change_admin', 
				'subscription_reactivation', 
				'subscription_suspension', 
				'subscription_payment_method_change_customer', 
				'wc_braintree_fees', 
				'wc_braintree_subscriptions', 
				'wc_braintree_subscriptions_change_payment_method' 
		);
	}

	public function admin_settings_tabs($tabs) {
		$tabs[ $this->id ] = $this->get_tab_title ();
		return $tabs;
	}

	public function get_tab_title() {
		return $this->tab_title;
	}

	/**
	 *
	 * @param string $env        	
	 */
	public function connect($env = '') {
		try {
			$this->gateway = new \Braintree\Gateway ( wc_braintree_connection_settings ( $env ) );
		} catch ( \Braintree\Exception $e ) {
			wc_braintree_log_info ( sprintf ( __ ( 'Error setting up %1$s. Reason: %2$s', 'woo-payment-gateway' ), $this->method_title, $e->getMessage () ) );
		}
	}

	public function payment_fields() {
		$this->enqueue_frontend_scripts ( braintree ()->frontend_scripts );
		if (is_checkout () || $this->is_change_payment_request ()) {
			$user = wp_get_current_user ();
			$methods = $this->get_tokens ();
			wc_braintree_get_template ( 'checkout/payment-method.php', array( 
					'methods' => $methods, 
					'has_methods' => ( bool ) $methods, 
					'gateway' => $this 
			) );
		} else {
			wc_braintree_get_template ( 'checkout/payment-method.php', array( 
					'methods' => array(), 'has_methods' => false, 
					'gateway' => $this 
			) );
		}
	}

	/**
	 * Delete a payment method in the Braintree gateway.
	 *
	 * @param int $token_id        	
	 * @param WC_Payment_Token $token        	
	 */
	public function delete_payment_method($token_id, $token) {
		try {
			$this->connect ( $token->get_meta ( 'environment' ) );
			$response = $this->gateway->paymentMethod ()->delete ( $token->get_token () );
			if (! $response->success) {
				wc_braintree_log_error ( sprintf ( __ ( 'Braintree payment token %1$s not deleted in Braintree gateway. Reason: %2$s', 'woo-payment-gateway' ), $token->get_token (), wc_braintree_errors_from_object ( $response ) ) );
			}
		} catch ( \Braintree\Exception $e ) {
			wc_braintree_log_error ( sprintf ( __ ( 'Braintree payment token %1$s not deleted in Braintree gateway. Reason: %2$s', 'woo-payment-gateway' ), $token->get_token (), wc_braintree_errors_from_object ( $e ) ) );
		}
		$this->connect ();
	}

	/**
	 *
	 * @param WC_Subscription $subscription        	
	 * @param string $new_payment_method        	
	 */
	public function pre_update_payment_method($subscription, $new_payment_method) {
		if ($new_payment_method === $this->id) {
			$this->update_payment_method_request = true;
		}
	}

	/**
	 *
	 * @param array $payment_meta        	
	 * @param WC_Subscription $subscription        	
	 */
	public function subscription_payment_meta($payment_meta, $subscription) {
		$payment_meta[ $this->id ] = array( 
				'post_meta' => array( 
						'_payment_method_token' => array( 
								'value' => $subscription->get_meta ( '_payment_method_token', true ), 
								'label' => __ ( 'Payment Method Token', 'woo-payment-gateway' ) 
						) 
				) 
		);
		return $payment_meta;
	}

	public function get_custom_attribute_html($attribs) {
		if (! empty ( $attribs[ 'custom_attributes' ] ) && is_array ( $attribs[ 'custom_attributes' ] )) {
			foreach ( $attribs[ 'custom_attributes' ] as $k => $v ) {
				if (is_array ( $v )) {
					$attribs[ 'custom_attributes' ][ $k ] = htmlspecialchars ( wp_json_encode ( $v ) );
				}
			}
		}
		return parent::get_custom_attribute_html ( $attribs );
	}

	public function generate_multiselect_html($key, $data) {
		$value = ( array ) $this->get_option ( $key, array() );
		$data[ 'options' ] = array_merge ( array_flip ( $value ), $data[ 'options' ] );
		return parent::generate_multiselect_html ( $key, $data );
	}

	public function generate_button_demo_html($key, $data) {
		$field_key = $this->get_field_key ( $key );
		$data = wp_parse_args ( $data, array( 
				'title' => '', 'class' => '', 'style' => '', 
				'description' => '', 'desc_tip' => false, 
				'id' => 'wc-braintree-button-demo' 
		) );
		ob_start ();
		include braintree ()->plugin_path () . 'includes/admin/views/button-demo.php';
		return ob_get_clean ();
	}

	public function generate_description_html($key, $data) {
		$field_key = $this->get_field_key ( $key );
		$data = wp_parse_args ( $data, array( 
				'class' => '', 'style' => '', 
				'description' => '' 
		) );
		ob_start ();
		include braintree ()->plugin_path () . 'includes/admin/views/html-description.php';
		return ob_get_clean ();
	}

	public function get_prefix() {
		return $this->plugin_id . $this->id . '_';
	}

	public function admin_options() {
		$this->output_settings_nav ();
		do_action ( 'wc_braintree_admin_options', $this );
		printf ( '<input type="hidden" id="wc_braintree_prefix" name="wc_braintree_prefix" value="%1$s"/>', $this->get_prefix () );
		echo '<div class="wc-braintree-settings-container ' . $this->id . '">';
		parent::admin_options ();
		echo '</div>';
	}

	public function output_settings_nav() {
		include braintree ()->plugin_path () . 'includes/admin/views/html-settings-nav.php';
	}

	public function is_active($key) {
		return $this->get_option ( $key ) === 'yes';
	}

	public function enqueue_admin_scripts() {}

	/**
	 *
	 * @param WC_Braintree_Frontend_Scripts $scripts        	
	 */
	public function enqueue_frontend_scripts($scripts) {
		global $wp;
		if (is_checkout () && ! is_order_received_page ()) {
			$this->enqueue_checkout_scripts ( $scripts );
		}
		if (is_add_payment_method_page () && ! isset ( $wp->query_vars[ 'payment-methods' ] )) {
			$this->enqueue_add_payment_method_scripts ( $scripts );
		}
		if (is_cart ()) {
			$this->enqueue_cart_scripts ( $scripts );
		}
		if (is_product ()) {
			$this->enqueue_product_scripts ( $scripts );
		}
		if (wc_braintree_subscriptions_active () && wcs_braintree_is_change_payment_method_request ()) {
			$this->enqueue_checkout_scripts ( $scripts );
		}
	}

	/**
	 *
	 * @param WC_Braintree_Frontend_Scripts $scripts        	
	 */
	public function enqueue_checkout_scripts($scripts) {}

	/**
	 *
	 * @param WC_Braintree_Frontend_Scripts $scripts        	
	 */
	public function enqueue_add_payment_method_scripts($scripts) {}

	/**
	 *
	 * @param WC_Braintree_Frontend_Scripts $scripts        	
	 */
	public function enqueue_cart_scripts($scripts) {}

	/**
	 *
	 * @param WC_Braintree_Frontend_Scripts $scripts        	
	 */
	public function enqueue_product_scripts($scripts) {}

	public function init_form_fields() {
		$this->form_fields = apply_filters ( 'wc_' . $this->id . '_form_fields', include WC_BRAINTREE_PATH . 'includes/gateways/settings/' . str_replace ( 'braintree_', '', $this->id ) . '-settings.php', $this );
		if (wcs_braintree_active () && $this->supports ( 'subscriptions' )) {
			$this->form_fields = array_merge ( $this->form_fields, apply_filters ( 'wcs_braintree_subscription_form_fields', include WC_BRAINTREE_PATH . '/includes/gateways/settings/subscription-settings.php', $this ) );
		}
	}

	/**
	 * Generate a client token
	 *
	 * @return string
	 */
	public function generate_client_token() {
		$client_token = '';
		$args = array();
		try {
			$merchant_account = wc_braintree_get_merchant_account ();
			if (! empty ( $merchant_account )) {
				$args[ 'merchantAccountId' ] = $merchant_account;
			}
			$client_token = $this->gateway->clientToken ()->generate ( $args );
		} catch ( \Braintree\Exception $e ) {
			wc_braintree_log_error ( sprintf ( __ ( 'Error creating client token. Exception: %1$s', 'woo-payment-gateway', get_class ( $e ) ) ) );
		}
		return $client_token;
	}

	public function process_payment($order_id) {
		$order = wc_get_order ( $order_id );
		if ($this->update_payment_method_request) {
			return array( 'result' => 'success', 
					'redirect' => wc_get_endpoint_url ( 'view-subscription', $order_id, wc_get_page_permalink ( 'myaccount' ) ) 
			);
		}
		do_action ( 'wc_braintree_before_process_order_' . $this->id, $order );
		
		if (wc_notice_count ( 'error' ) > 0) {
			return array( 'result' => 'failure' 
			);
		}
		
		$this->processing_payment = true;
		
		if ($order->get_total () == 0 || $this->pre_order_with_payment_later ( $order )) {
			$this->save_zero_total_order_meta ( $order );
			if ($this->pre_order_with_payment_later ( $order )) {
				WC_Pre_Orders_Order::mark_order_as_pre_ordered ( $order );
			} else {
				$order->payment_complete ();
			}
			WC ()->cart->empty_cart ();
			$this->remove_session_checkout_vars ();
			return $this->order_success_result ( $order );
		}
		$args = array( 'amount' => $order->get_total (), 
				'taxAmount' => wc_round_tax_total ( $order->get_total_tax () ), 
				'shippingAmount' => $order->get_shipping_total (), 
				'discountAmount' => $order->get_discount_total (), 
				'customer' => $this->get_customer_attributes ( $order ), 
				'orderId' => $this->get_order_id ( $order ), 
				'billing' => array( 
						'firstName' => $order->get_billing_first_name (), 
						'lastName' => $order->get_billing_last_name (), 
						'locality' => $order->get_billing_city (), 
						'postalCode' => $order->get_billing_postcode (), 
						'region' => $order->get_billing_state (), 
						'streetAddress' => $order->get_billing_address_1 (), 
						'extendedAddress' => $order->get_billing_address_2 (), 
						'countryCodeAlpha2' => $order->get_billing_country () 
				), 
				'shipping' => $order->get_shipping_address_1 () ? array( 
						'firstName' => $order->get_shipping_first_name (), 
						'lastName' => $order->get_shipping_last_name (), 
						'locality' => $order->get_shipping_city (), 
						'postalCode' => $order->get_shipping_postcode (), 
						'region' => $order->get_shipping_state (), 
						'streetAddress' => $order->get_shipping_address_1 (), 
						'extendedAddress' => $order->get_shipping_address_2 (), 
						'countryCodeAlpha2' => $order->get_shipping_country () 
				) : array(), 
				'merchantAccountId' => wc_braintree_get_merchant_account ( $order->get_currency () ), 
				'channel' => braintree ()->partner_code, 
				'options' => $this->get_order_options ( $order ), 
				'descriptor' => $this->get_descriptors (), 
				'deviceData' => $this->get_device_data () 
		);
		
		$this->add_line_items ( $args, $order );
		
		if (( $customer_id = wc_braintree_get_customer_id ( $order->get_customer_id () ) )) {
			$args[ 'customerId' ] = $customer_id;
		}
		if ($this->use_saved_method ()) {
			$args[ 'paymentMethodToken' ] = $this->get_payment_method_token ();
		} else {
			$args[ 'paymentMethodNonce' ] = $this->get_payment_method_nonce ();
		}
		/* Deprecated filter for transaction attributes. Remove in future version */
		wc_do_deprecated_action ( 'braintree_woocommerce_' . $this->deprecated_id . '_order_attributes', array( 
				$args, $order, $this 
		), '3.0.0', 'wc_braintree_order_transaction_args' );
		
		$args = apply_filters ( 'wc_braintree_order_transaction_args', $args, $order, $this->id );
		
		$result = array();
		wc_braintree_log_info ( sprintf ( __ ( 'Processing payment for order %1$s. Transaction args: %2$s', 'woo-payment-gateway' ), $order_id, print_r ( $args, true ) ) );
		try {
			$response = $this->gateway->transaction ()->sale ( $args );
			if ($response->success) {
				$this->save_order_meta ( $response->transaction, $order );
				$this->record_transaction ();
				if (isset ( $args[ 'options' ][ 'storeInVaultOnSuccess' ] ) && $args[ 'options' ][ 'storeInVaultOnSuccess' ] == true) {
					$token = $this->get_payment_token ( $this->get_payment_method_from_transaction ( $response->transaction ) );
					$token->set_user_id ( $order->get_customer_id () );
					$token->save ();
					WC_Payment_Tokens::set_users_default ( $order->get_customer_id (), $token->get_id () );
				}
				if (braintree ()->fraud_settings->is_active ( 'kount_enabled' ) && isset ( $response->transaction->riskData )) {
					$this->perform_kount_actions ( $order, $response->transaction, $args );
				} else {
					$this->payment_complete_actions ( $order, $response->transaction, $args );
				}
				$this->remove_session_checkout_vars ();
				WC ()->cart->empty_cart ();
				$result = $this->order_success_result ( $order );
			} else {
				$errors = wc_braintree_errors_from_object ( $response );
				$order->add_order_note ( sprintf ( __ ( 'Error processing payment. Reason: %1$s', 'woo-payment-gateway' ), $errors ) );
				wc_add_notice ( sprintf ( __ ( 'There was an error processing your payment. Reason: %1$s', 'woo-payment-gateway' ), $errors ), 'error' );
				$order->update_status ( 'failed' );
				wc_braintree_set_checkout_error ();
				$result = $this->order_error_result ();
			}
		} catch ( \Braintree\Exception $e ) {
			wc_braintree_log_error ( sprintf ( __ ( 'Error processing payment for order %1$s. Exception: %2$s. Transaction args: %3$s', 'woo-payment-gateway' ), $order->get_id (), get_class ( $e ), print_r ( $args, true ) ) );
			$order->add_order_note ( sprintf ( __ ( 'Error processing payment. Reason: %1$s', 'woo-payment-gateway' ), wc_braintree_errors_from_object ( $e ) ) );
			$order->update_status ( 'failed' );
			wc_braintree_set_checkout_error ();
			$result = $this->order_error_result ();
			wc_add_notice ( sprintf ( __ ( 'There was an error processing your payment. Reason: %1$s', 'woo-payment-gateway' ), wc_braintree_errors_from_object ( $e ) ), 'error' );
		}
		return $result;
	}

	/**
	 * Handles order status once payment has been captured or authorized.
	 *
	 * @since 3.0.8
	 * @param WC_Order $order        	
	 * @param \Braintree\Transaction $transaction        	
	 * @param array $args        	
	 */
	public function payment_complete_actions($order, $transaction, $args) {
		if ($args[ 'options' ][ 'submitForSettlement' ]) {
			// payment was captured, so call payment complete.
			$order->payment_complete ( $transaction->id );
		} else {
			// payment is authorized only. Must be captured at a later time.
			$order_status = $this->get_option ( 'order_status' );
			$order->update_status ( apply_filters ( 'wc_braintree_authorized_order_status', 'default' === $order_status ? 'processing' : $order_status, $order, $this ) );
		}
		$order->add_order_note ( sprintf ( __ ( 'Order %1$s in Braintree: Transaction ID: %2$s. Payment method: %3$s', 'woo-payment-gateway' ), $args[ 'options' ][ 'submitForSettlement' ] ? __ ( 'captured', 'woo-payment-gateway' ) : __ ( 'authorized', 'woo-payment-gateway' ), $order->get_transaction_id (), $order->get_payment_method_title () ) );
	}

	/**
	 * If the payment_method_token has been set then return it.
	 * If not look for the token in the $_POST.
	 */
	public function get_payment_method_token() {
		if ($this->payment_method_token) {
			return $this->payment_method_token;
		}
		if (! empty ( $_POST[ $this->token_key ] )) {
			return $_POST[ $this->token_key ];
		}
	}

	/**
	 * If the payment_method_nonce has been set, then return it.
	 * If not look for the nonce in the $_POST.
	 */
	public function get_payment_method_nonce() {
		if ($this->payment_method_nonce) {
			return $this->payment_method_nonce;
		}
		return ! empty ( $_POST[ $this->nonce_key ] ) ? $_POST[ $this->nonce_key ] : '';
	}

	/**
	 *
	 * @param string $value        	
	 */
	public function set_payment_method_nonce($value) {
		$this->payment_method_nonce = $value;
	}

	/**
	 *
	 * @param string $value        	
	 */
	public function set_payment_method_token($value) {
		$this->payment_method_token = $value;
	}

	/**
	 *
	 * @param WC_Order $order        	
	 */
	public function get_customer_attributes($order) {
		return array( 
				'firstName' => $order->get_billing_first_name (), 
				'lastName' => $order->get_billing_last_name (), 
				'phone' => $order->get_billing_phone (), 
				'email' => $order->get_billing_email (), 
				'company' => $order->get_billing_company () 
		);
	}

	/**
	 *
	 * @param WC_Order $order        	
	 */
	public function get_order_id($order) {
		return sprintf ( '%1$s%2$s%3$s', $this->get_option ( 'order_prefix' ), $order->get_order_number (), $this->get_option ( 'order_suffix' ) );
	}

	public function get_descriptors() {
		$args = braintree ()->descriptor_settings->is_active ( 'enabled' ) ? array( 
				'name' => braintree ()->descriptor_settings->get_option ( 'descriptor_name' ), 
				'phone' => braintree ()->descriptor_settings->get_option ( 'descriptor_phone' ), 
				'url' => braintree ()->descriptor_settings->get_option ( 'descriptor_url' ) 
		) : array();
		foreach ( $args as $k => $v ) {
			if (empty ( $v )) {
				unset ( $args[ $k ] );
			}
		}
		return $args;
	}

	/**
	 *
	 * @param WC_Order $order        	
	 */
	public function get_order_options($order) {
		$options = array( 
				'submitForSettlement' => apply_filters ( 'wc_braintree_transaction_submit_for_settlement', $this->get_option ( 'charge_type' ) === 'capture', $this ), 
				'storeInVaultOnSuccess' => $this->should_save_payment_method () 
		);
		return $options;
	}

	public function get_device_data() {
		if (! empty ( $_POST[ $this->device_data_key ] )) {
			return stripslashes ( $_POST[ $this->device_data_key ] );
		}
	}

	/**
	 * Return true if the payment method used on the checkout page should be saved.
	 * Payment methods
	 * should only be saved if the customer's wants to save the method or a new method is being used and
	 * there are subscriptions associated with the order.
	 */
	public function should_save_payment_method() {
		return ( ! empty ( $_POST[ $this->save_method_key ] ) && ! $this->use_saved_method () ) || ( wcs_braintree_active () && ! $this->use_saved_method () && WC_Subscriptions_Cart::cart_contains_subscription () );
	}

	/**
	 * Return true if a saved payment method should be used.
	 */
	public function use_saved_method() {
		return ( ! empty ( $_POST[ $this->payment_type_key ] ) && $_POST[ $this->payment_type_key ] === 'token' ) || $this->payment_method_token;
	}

	public function order_error_result() {
		return array( 'result' => 'failure', 
				'redirect' => '' 
		);
	}

	/**
	 *
	 * @param WC_Order $order        	
	 */
	public function order_success_result($order) {
		return array( 'result' => 'success', 
				'redirect' => $order->get_checkout_order_received_url () 
		);
	}

	/**
	 * Save transaction data as meta data of the order.
	 *
	 * @param \Braintree\Transaction $transaction        	
	 * @param WC_Order $order        	
	 */
	public function save_order_meta($transaction, $order) {
		$token = $this->get_payment_token ( $this->get_payment_method_from_transaction ( $transaction ) );
		$order->set_payment_method_title ( $token->get_payment_method_title () );
		$order->set_transaction_id ( $transaction->id );
		$order->add_meta_data ( '_wc_braintree_version', braintree ()->version, true );
		$order->add_meta_data ( '_merchant_account_id', $transaction->merchantAccountId, true );
		$order->add_meta_data ( '_payment_method_token', $token->get_token (), true );
		$order->add_meta_data ( '_wc_braintree_environment', wc_braintree_environment (), true );
		$order->add_meta_data ( '_transaction_status', $transaction->status, true );
		if ($transaction->status === \Braintree\Transaction::AUTHORIZED) {
			$order->add_meta_data ( '_authorization_exp_at', $transaction->authorizationExpiresAt->getTimestamp (), true );
		}
		$order->save ();
		
		if (wcs_braintree_active () && wcs_order_contains_subscription ( $order )) {
			$subscriptions = wcs_get_subscriptions_for_order ( $order );
			foreach ( $subscriptions as $subscription ) {
				$this->save_order_meta ( $transaction, $subscription );
			}
		}
		if (wc_braintree_subscriptions_active () && wcs_braintree_order_contains_subscription ( $order )) {
			$subscriptions = wcs_braintree_get_subscriptions_for_order ( $order );
			foreach ( $subscriptions as $subscription ) {
				$this->save_order_meta ( $transaction, $subscription );
			}
		}
	}

	/**
	 * Return the payment method details from the tranaction.
	 *
	 * @param \Braintree\Transaction $transaction        	
	 */
	public abstract function get_payment_method_from_transaction($transaction);

	/**
	 *
	 * @param mixed $method        	
	 * @return WC_Payment_Token_Braintree
	 */
	public function get_payment_token($method) {
		$class_name = 'WC_Payment_Token_Braintree_' . $this->get_token_type ();
		if (class_exists ( $class_name )) {
			/**
			 *
			 * @var WC_Payment_Token_Braintree $token
			 */
			$token = new $class_name ();
			$token->init_from_payment_method ( $method );
			$token->set_gateway_id ( $this->id );
			$token->set_user_id ( get_current_user_id () );
			$token->set_format ( $this->get_option ( 'method_format' ) );
			$token->set_environment ( wc_braintree_environment () );
			return $token;
		}
	}

	public function get_token_type() {
		return apply_filters ( 'wc_braintree_gateway_token_type', $this->token_type );
	}

	/**
	 *
	 * @param WC_Order $order        	
	 */
	public function save_zero_total_order_meta($order) {
		$token = $this->get_token ( $this->get_payment_method_token (), $order->get_customer_id () );
		$order->add_meta_data ( '_wc_braintree_version', braintree ()->version, true );
		$order->add_meta_data ( '_merchant_account_id', wc_braintree_get_merchant_account ( $order->get_currency () ), true );
		$order->add_meta_data ( '_payment_method_token', $token->get_token (), true );
		$order->add_meta_data ( '_wc_braintree_environment', wc_braintree_environment (), true );
		$order->set_payment_method_title ( $token->get_payment_method_title () );
		$order->save ();
		if (wcs_braintree_active ()) {
			$subscriptions = wcs_get_subscriptions_for_order ( $order );
			foreach ( $subscriptions as $subscription ) {
				$this->save_zero_total_order_meta ( $subscription );
			}
		}
		if (wc_braintree_subscriptions_active () && wcs_braintree_order_contains_subscription ( $order )) {
			$subscriptions = wcs_braintree_get_subscriptions_for_order ( $order );
			foreach ( $subscriptions as $subscription ) {
				$this->save_zero_total_order_meta ( $subscription );
			}
		}
	}

	public function process_refund($order_id, $amount = null, $reason = '') {
		$order = wc_get_order ( $order_id );
		$id = $order->get_transaction_id ();
		$message = __ ( 'Error processing refund. Reason: %1$s', 'woo-payment-gateway' );
		try {
			if (empty ( $id )) {
				throw new InvalidArgumentException ( sprintf ( __ ( 'The transaction ID for order %1$s is blank. A refund cannot be processed unless there is a valid transaction associated with the order.', 'woo-payment-gateway' ), $order_id ) );
			}
			$this->connect ( wc_braintree_get_order_environment ( $order ) );
			$response = $this->gateway->transaction ()->refund ( $id, $amount );
			if ($response->success) {
				$transaction = $response->transaction;
				$order->update_meta_data ( '_transaction_status', $transaction->status );
				$order->add_order_note ( sprintf ( __ ( 'Refund successful in Braintree. Amount: %1$s. Refund ID: %2$s', 'woo-payment-gateway' ), wc_price ( $amount, array( 
						'currency' => $order->get_currency () 
				) ), $response->transaction->id ) );
				$order->save ();
				return true;
			} else {
				return new WP_Error ( 'refund-error', sprintf ( $message, wc_braintree_errors_from_object ( $response ) ) );
			}
		} catch ( \Braintree\Exception $e ) {
			return new WP_Error ( 'refund-error', sprintf ( $message, wc_braintree_errors_from_object ( $e ) ) );
		} catch ( Exception $e ) {
			return new WP_Error ( 'refund-error', sprintf ( __ ( 'Exception thrown while issuing refund. Reason: %1$s Exception class: %2$s', 'woo-payment-gateway' ), $e->getMessage (), get_class ( $e ) ) );
		}
	}

	/**
	 * Capture the provided amount.
	 *
	 * @param float $amount        	
	 * @param WC_Order $order        	
	 * @return bool|WP_Error
	 */
	public function capture_charge($amount, $order) {
		$id = $order->get_transaction_id ();
		try {
			$response = $this->gateway->transaction ()->submitForSettlement ( $id, $amount );
			if ($response->success) {
				$this->save_order_meta ( $response->transaction, $order );
				
				// we don't want order_status option to be called here since capture is happening via admin.'
				remove_filter ( 'woocommerce_payment_complete_order_status', 'wc_braintree_payment_complete_order_status', 99 );
				
				$order->payment_complete ( $id );
				$order->add_order_note ( sprintf ( __ ( 'Transaction submitted for settlement in Braintree. Amount %1$s', 'woo-payment-gateway' ), wc_price ( $amount ) ) );
				if ($amount !== $order->get_total ()) {
					$order->set_total ( $amount );
				}
				$order->save ();
				return true;
			} else {
				return new WP_Error ( 'capture-error', sprintf ( __ ( 'There was an error capturing the charge. Reason: %1$s', 'woo-payment-gateway' ), wc_braintree_errors_from_object ( $response ) ) );
			}
		} catch ( \Braintree\Exception $e ) {
			return new WP_Error ( 'capture-error', sprintf ( __ ( 'There was an error capturing the charge. Reason: %1$s', 'woo-payment-gateway' ), wc_braintree_errors_from_object ( $e ) ) );
		}
	}

	/**
	 *
	 * @param WC_Order $order        	
	 * @return bool|WP_Error
	 */
	public function void_charge($order) {
		$id = $order->get_transaction_id ();
		try {
			$response = $this->gateway->transaction ()->void ( $id );
			if ($response->success) {
				$this->save_order_meta ( $response->transaction, $order );
				$order->update_status ( 'cancelled' );
				$order->add_order_note ( sprintf ( __ ( 'Transaction %1$s has been voided in Braintree.', 'woo-payment-gateway' ), $id ) );
				return true;
			} else {
				return new WP_Error ( 'capture-error', sprintf ( __ ( 'There was an error voiding the transaction. Reason: %1$s', 'woo-payment-gateway' ), wc_braintree_errors_from_object ( $response ) ) );
			}
		} catch ( \Braintree\Exception $e ) {
			return new WP_Error ( 'capture-error', sprintf ( __ ( 'There was an error voiding the transaction. Reason: %1$s', 'woo-payment-gateway' ), wc_braintree_errors_from_object ( $e ) ) );
		}
	}

	public function add_payment_method($args = array()) {
		$nonce = isset ( $_POST[ $this->nonce_key ] ) ? wc_clean ( $_POST[ $this->nonce_key ] ) : $this->payment_method_nonce;
		$user = wp_get_current_user ();
		$customer = WC ()->customer;
		if (! $nonce) {
			return array( 'result' => 'error' 
			);
		}
		try {
			/**
			 * Action added so other plugins can throw errors before add payment method starts.
			 *
			 * Example: plugin validates captcha and throws exception if invalid.
			 *
			 * @since 3.0.6
			 */
			do_action ( 'wc_braintree_before_add_payment_method', $this );
			
			// first check that the customer has a braintree vault ID.
			if (! ( $customer_id = wc_braintree_get_customer_id ( $user->ID ) )) {
				$customer_id = $this->create_customer ();
				if (is_wp_error ( $customer_id )) {
					throw new Exception ( $customer_id->get_error_message () );
				}
			}
			$args = array_merge ( array( 
					'customerId' => wc_braintree_get_customer_id ( $user->ID ), 
					'paymentMethodNonce' => $nonce, 
					'deviceData' => $this->get_device_data (), 
					'options' => array( 
							'failOnDuplicatePaymentMethod' => $this->is_active ( 'fail_on_duplicate' ), 
							'makeDefault' => true, 
							'verifyCard' => true 
					), 
					'cardholderName' => sprintf ( '%1$s %2$s', $customer->get_first_name (), $customer->get_last_name () ), 
					'billingAddress' => isset ( $_POST[ 'billing_address_1' ] ) ? array( 
							'streetAddress' => wc_clean ( $_POST[ 'billing_address_1' ] ) 
					) : array() 
			), $args );
			$args = apply_filters ( 'wc_braintree_add_payment_method_args', $args, $this );
			$response = $this->gateway->paymentMethod ()->create ( $args );
			if ($response->success) {
				$token = $this->get_payment_token ( $response->paymentMethod );
				$token->save ();
				WC_Payment_Tokens::set_users_default ( $user->ID, $token->get_id () );
				$this->payment_method_token = $token->get_token ();
				/**
				 * Perform address cleanup in Braintree.
				 * Address can build up over time and if they exceed gateway limit, can cause errors.
				 */
				if (@isset ( $response->paymentMethod->billingAddress )) {
					if (! is_null ( ( $response->paymentMethod->billingAddress->id ) )) {
						try {
							$this->gateway->address ()->delete ( wc_braintree_get_customer_id (), $response->paymentMethod->billingAddress->id );
						} catch ( \Braintree\Exception $e ) {
							wc_braintree_log_error ( sprintf ( __ ( 'Error deleting payment method address. This error was caught gracefully
								and not shown to the customer. You can delete address %1$s in your Braintree gateway.', 'woo-payment-gateway' ), $id ) );
						}
					}
				}
				return array( 'result' => 'success', 
						'redirect' => wc_get_account_endpoint_url ( 'payment-methods' ) 
				);
			} else {
				wc_add_notice ( sprintf ( __ ( 'There was an error saving your payment method. Reason: %1$s', 'woo-payment-gateway' ), wc_braintree_errors_from_object ( $response ) ), 'error' );
				return array( 'result' => 'error' 
				);
			}
		} catch ( \Braintree\Exception $e ) {
			wc_add_notice ( sprintf ( __ ( 'There was an error saving your payment method. Reason: %1$s', 'woo-payment-gateway' ), wc_braintree_errors_from_object ( $e ) ), 'error' );
			return array( 'result' => 'error' 
			);
		} catch ( Exception $e ) {
			wc_add_notice ( sprintf ( __ ( 'There was an error saving your payment method. Reason: %1$s', 'woo-payment-gateway' ), $e->getMessage () ), 'error' );
			return array( 'result' => 'error' 
			);
		}
	}

	/**
	 *
	 * @param string $id        	
	 * @return WP_Error|\Braintree\Transaction
	 */
	public function fetch_transaction($id, $env = '') {
		try {
			$this->connect ( $env );
			$transaction = $this->gateway->transaction ()->find ( $id );
			return $transaction;
		} catch ( \Braintree\Exception $e ) {
			return new WP_Error ( 'transaction-error', sprintf ( 'Error fetching transaction. Exception: %s. Message: %s', get_class ( $e ), $e->getMessage () ) );
		} catch ( Exception $e ) {
			return new WP_Error ( 'transaction-error', sprintf ( 'Error fetching transaction. Exception: %s. Message: %s', get_class ( $e ), $e->getMessage () ) );
		}
		$this->connect ();
	}

	/**
	 *
	 * @param int $order_id        	
	 * @param WC_Order $order        	
	 */
	public static function capture_transaction_from_status($order_id, $order) {
		if ($order->get_meta ( '_transaction_status' ) === \Braintree\Transaction::AUTHORIZED) {
			$payment_method = $order->get_payment_method ();
			$payment_gateways = WC ()->payment_gateways ()->payment_gateways ();
			$gateway = isset ( $payment_gateways[ $payment_method ] ) ? $payment_gateways[ $payment_method ] : null;
			if ($gateway && ( $gateway instanceof WC_Braintree_Payment_Gateway ) && ! $gateway->processing_payment) {
				$gateway->capture_charge ( $order->get_total (), $order );
			}
		}
	}

	/**
	 *
	 * @param int $order_id        	
	 * @param WC_Order $order        	
	 */
	public static function void_transaction_from_status($order_id, $order) {
		$statuses = array( 
				\Braintree\Transaction::AUTHORIZED, 
				\Braintree\Transaction::SUBMITTED_FOR_SETTLEMENT, 
				\Braintree\Transaction::SETTLEMENT_PENDING 
		);
		if (in_array ( $order->get_meta ( '_transaction_status' ), $statuses )) {
			$payment_method = $order->get_payment_method ();
			$payment_gateways = WC ()->payment_gateways ()->payment_gateways ();
			$gateway = isset ( $payment_gateways[ $payment_method ] ) ? $payment_gateways[ $payment_method ] : null;
			if ($gateway && ( $gateway instanceof WC_Braintree_Payment_Gateway )) {
				$gateway->void_charge ( $order );
			}
		}
	}

	public function get_localized_standard_params() {
		return array( 'gateway' => $this->id, 
				'environment' => wc_braintree_environment (), 
				'needs_shipping' => $this->needs_shipping (), 
				'advanced_fraud' => array( 
						'enabled' => braintree ()->fraud_settings->is_active ( 'enabled' ) 
				), 'currency' => get_woocommerce_currency (), 
				'token_selector' => $this->token_key, 
				'nonce_selector' => $this->nonce_key, 
				'device_data_selector' => $this->device_data_key, 
				'payment_type_selector' => $this->payment_type_key, 
				'tokenized_response_selector' => $this->id . '_tokenized_response', 
				'roles' => array( 
						'admin' => current_user_can ( 'administrator' ) 
				), 
				'_wp_rest_nonce' => wp_create_nonce ( 'wp_rest' ), 
				'messages' => array( 
						'terms' => __ ( 'Please read and accept the terms and conditions to proceed with your order.', 'woocommerce' ) 
				), 'locale' => $this->get_locale (), 
				'routes' => array( 
						'checkout' => braintree ()->rest_api->checkout->rest_url () . 'checkout', 
						'add_to_cart' => braintree ()->rest_api->cart->rest_url () . 'cart', 
						'shipping_address' => braintree ()->rest_api->cart->rest_url () . 'cart/shipping-address', 
						'shipping_method' => braintree ()->rest_api->cart->rest_url () . 'cart/shipping-method' 
				) 
		);
	}

	public static function available_payment_gateways($gateways) {
		global $wp;
		if (is_add_payment_method_page () && isset ( $wp->query_vars[ 'add-payment-method' ] )) {
			unset ( $gateways[ 'braintree_paypal' ] );
			unset ( $gateways[ 'braintree_applepay' ] );
			unset ( $gateways[ 'braintree_googlepay' ] );
			unset ( $gateways[ 'braintree_venmo' ] );
		}
		return $gateways;
	}

	/**
	 *
	 * @param WC_Order $order        	
	 */
	public function wcs_before_process_order($order) {
		if (wcs_braintree_active () && wcs_order_contains_subscription ( $order )) {
			if ($order->get_total () == 0 && ! self::use_saved_method ()) {
				// save the payment method so it can be used for the subscription.
				$args = array( 
						'billingAddress' => array( 
								'streetAddress' => $order->get_billing_address_1 () 
						) 
				);
				$this->add_payment_method ( apply_filters ( 'wcs_braintree_add_payment_method_args', $args, $this->id, $this ) );
			}
		}
	}

	/**
	 * If the plugin's subscription functionality is being used and there is a subscription in the cart and a
	 * new payment method is being used save the payment method.
	 *
	 * Create the Braintree Subscription.
	 *
	 * @param WC_Order $order        	
	 */
	public function wcs_braintree_before_process_order($order) {
		if (wc_braintree_subscriptions_active () && wcs_braintree_order_contains_subscription ( $order )) {
			if (! self::use_saved_method ()) {
				$args = array( 
						'billingAddress' => array( 
								'streetAddress' => $order->get_billing_address_1 () 
						) 
				);
				$this->add_payment_method ( apply_filters ( 'wcs_braintree_add_payment_method_args', $args, $this->id, $this ) );
			}
			if (wc_notice_count ( 'error' ) == 0) {
				// process the subscriptions
				foreach ( wcs_braintree_get_subscriptions_for_order ( $order ) as $subscription ) {
					$this->create_braintree_subscription ( $subscription, $order );
				}
			}
		}
	}

	/**
	 * Creates a subscription in the Braintree Gateway using data from the WC_Braintree_Subscription
	 *
	 * @param WC_Braintree_Subscription $subscription        	
	 * @param WC_Order $order        	
	 */
	public function create_braintree_subscription($subscription, $order) {
		if ($subscription->is_created ()) {
			return;
		}
		$args = array( 'id' => $subscription->get_id (), 
				'planId' => $subscription->get_braintree_plan (), 
				'price' => $subscription->get_total (), 
				'merchantAccountId' => $subscription->get_merchant_account_id (), 
				'paymentMethodToken' => $this->get_payment_method_token () 
		);
		if ($subscription->never_expires ()) {
			$args[ 'neverExpires' ] = true;
		} else {
			$args[ 'numberOfBillingCycles' ] = $subscription->get_num_of_billing_cycles ();
		}
		if ($subscription->has_trial ()) {
			$args[ 'trialDuration' ] = $subscription->get_subscription_trial_length ();
			$args[ 'trialDurationUnit' ] = $subscription->get_subscription_trial_period ();
			$args[ 'trialPeriod' ] = true;
		} else {
			$args[ 'firstBillingDate' ] = $subscription->get_date ( 'next_payment' );
		}
		$args = apply_filters ( 'wcs_braintree_subscription_args', $args );
		try {
			$response = $this->gateway->subscription ()->create ( $args );
			if ($response->success) {
				$subscription->set_payment_method ( $this->id );
				$subscription->set_created ( true );
				$subscription->update_status ( 'active' );
				$subscription->save ();
			} else {
				$subscription->update_status ( 'failed' );
				$order->update_status ( 'failed' );
				$order->add_order_note ( sprintf ( __ ( 'Error processing subscription. Reason: %1$s', 'woo-payment-gateway' ), wc_braintree_errors_from_object ( $response ) ) );
				wc_add_notice ( sprintf ( __ ( 'Error processing subscription. Reason: %1$s', 'woo-payment-gateway' ), wc_braintree_errors_from_object ( $response ) ), 'error' );
				return;
			}
		} catch ( \Braintree\Exception $e ) {
			$order->add_order_note ( sprintf ( __ ( 'Error processing subscription. Reason: %1$s', 'woo-payment-gateway' ), wc_braintree_errors_from_object ( $e ) ) );
			wc_add_notice ( sprintf ( __ ( 'Error processing subscription. Reason: %1$s', 'woo-payment-gateway' ), wc_braintree_errors_from_object ( $e ) ), 'error' );
			return;
		}
	}

	/**
	 *
	 * @param string $token        	
	 * @return WC_Payment_Token_Braintree
	 */
	public function get_token($token, $user_id = 0) {
		global $wpdb;
		$payment_token = null;
		$query = $wpdb->prepare ( "SELECT token_id FROM {$wpdb->prefix}woocommerce_payment_tokens WHERE token = %s AND gateway_id = %s AND user_id = %d", $token, $this->id, ! $user_id ? get_current_user_id () : $user_id );
		$result = $wpdb->get_row ( $query );
		if ($result) {
			/**
			 *
			 * @var WC_Payment_Token_Braintree $payment_token
			 */
			$payment_token = WC_Payment_Tokens::get ( $result->token_id );
			$payment_token->set_format ( $this->get_option ( 'method_format' ) );
		} else {
			// added in 3.0.7. Don't allow the $payment_token to be null to prevent exceptions
			// when data is bad.
			$class_name = 'WC_Payment_Token_Braintree_' . $this->get_token_type ();
			$payment_token = new $class_name ();
			$payment_token->set_token ( $token );
		}
		$payment_token->set_format ( $this->get_option ( 'method_format' ) );
		return $payment_token;
	}

	/**
	 * Return true if the token exists in the woocommerce_payment_tokens table.
	 *
	 * @since 3.0.7
	 * @param string $token        	
	 * @param int $user_id        	
	 */
	public function token_exists($token, $user_id = 0) {
		global $wpdb;
		$payment_token = null;
		$count = $wpdb->get_var ( $wpdb->prepare ( "SELECT COUNT(*) FROM {$wpdb->prefix}woocommerce_payment_tokens WHERE token = %s AND gateway_id = %s AND user_id = %d", $token, $this->id, ! $user_id ? get_current_user_id () : $user_id ) );
		return $count > 0;
	}

	/**
	 *
	 * @param WC_Subscription $subscription        	
	 */
	public function subscription_payment_method_updated($subscription) {
		// 3.0.7 this isn't a frontend request so nothing needs to happen.
		if (defined ( 'DOING_CRON' ) || is_admin ()) {
			return;
		}
		if ($this->use_saved_method ()) {
			$this->payment_method_token = $this->get_payment_method_token ();
		} else {
			$result = $this->add_payment_method ( array( 
					'billingAddress' => array( 
							'streetAddress' => isset ( $_POST[ 'billing_address_1' ] ) ? wc_clean ( $_POST[ 'billing_address_1' ] ) : '' 
					) 
			) );
			if ($result[ 'result' ] === 'error') {
				/**
				 * WCS seems to have a bug where notices added at this stage are deleted and not captured.
				 * This code resolves that by not allowing notices to be cleared until they are added by WCS.
				 */
				remove_action ( 'before_woocommerce_pay', 'woocommerce_output_all_notices' );
				add_action ( 'before_woocommerce_pay', 'wc_clear_notices', 200 );
				return;
			}
		}
		if ($this->payment_method_token) {
			$token = $this->get_token ( $this->payment_method_token );
			// save meta data.
			$subscription->update_meta_data ( '_payment_method_token', $token->get_token () );
			$subscription->set_payment_method_title ( $token->get_payment_method_title () );
			$subscription->save ();
		}
	}

	/**
	 *
	 * @param float $amount        	
	 * @param WC_Order $order        	
	 */
	public function process_subscription_payment($amount, $order) {
		// sync payment methods if needed.
		WC_Braintree_Payment_Method_Conversion::sync_payment_method_tokens ( $order->get_customer_id (), wc_braintree_get_order_environment ( $order ) );
		
		$args = array( 'amount' => $order->get_total (), 
				'taxAmount' => wc_round_tax_total ( $order->get_total_tax () ), 
				'customer' => $this->get_customer_attributes ( $order ), 
				'orderId' => $this->get_order_id ( $order ), 
				'paymentMethodToken' => $order->get_meta ( '_payment_method_token' ), 
				'transactionSource' => 'recurring', 
				'billing' => array( 
						'firstName' => $order->get_billing_first_name (), 
						'lastName' => $order->get_billing_last_name (), 
						'locality' => $order->get_billing_city (), 
						'postalCode' => $order->get_billing_postcode (), 
						'region' => $order->get_billing_state (), 
						'streetAddress' => $order->get_billing_address_1 (), 
						'extendedAddress' => $order->get_billing_address_2 (), 
						'countryCodeAlpha2' => $order->get_billing_country () 
				), 
				'shipping' => $order->get_shipping_address_1 () ? array( 
						'firstName' => $order->get_shipping_first_name (), 
						'lastName' => $order->get_shipping_last_name (), 
						'locality' => $order->get_shipping_city (), 
						'postalCode' => $order->get_shipping_postcode (), 
						'region' => $order->get_shipping_state (), 
						'streetAddress' => $order->get_shipping_address_1 (), 
						'extendedAddress' => $order->get_shipping_address_2 (), 
						'countryCodeAlpha2' => $order->get_shipping_country () 
				) : array(), 
				'merchantAccountId' => $order->get_meta ( '_merchant_account_id' ), 
				'channel' => braintree ()->partner_code, 
				'options' => $this->get_subscription_options ( $order ) 
		);
		if (empty ( $args[ 'paymentMethodToken' ] )) {
			// for some reason the payment method token is blank on the renewal order. Try the subscription.
			if (( $subscription_id = get_post_meta ( $order->get_id (), '_subscription_renewal', true ) )) {
				$args[ 'paymentMethodToken' ] = get_post_meta ( $subscription_id, '_payment_method_token', true );
			}
			if (empty ( $args[ 'paymentMethodToken' ] )) {
				// still empty so pass the customer ID and the default payment method will be charged
				if (( $customer_id = wc_braintree_get_customer_id ( $order->get_customer_id () ) )) {
					$args[ 'customerId' ] = $customer_id;
				}
			}
		}
		if (empty ( $args[ 'merchantAccountId' ] )) {
			$args[ 'merchantAccountId' ] = wc_braintree_get_merchant_account ( $order->get_currency () );
		}
		$args = apply_filters ( 'wcs_braintree_subscription_payment_args', $args, $amount, $order, $this->id );
		$this->connect ( wc_braintree_get_order_environment ( $order ) );
		wc_braintree_log_info ( sprintf ( __ ( 'Processing recurring payment for order %1$s. Transaction args: %2$s', 'woo-payment-gateway' ), $order->get_id (), print_r ( $args, true ) ) );
		try {
			$response = $this->gateway->transaction ()->sale ( $args );
			if ($response->success) {
				$this->save_order_meta ( $response->transaction, $order );
				if ($args[ 'options' ][ 'submitForSettlement' ] === true) {
					$order->payment_complete ( $response->transaction->id );
				} else {
					$order->update_status ( apply_filters ( 'wcs_braintree_subscription_authorize_payment_status', $this->get_option ( 'wcs_authorized_status' ) ) );
				}
				$order->add_order_note ( sprintf ( __ ( 'Recurring payment charged successfully in Braintree. Transaction ID: %1$s. Payment method: %2$s', 'woo-payment-gateway' ), $order->get_transaction_id (), $order->get_payment_method_title () ) );
				// if default payment method was used because _payment_method_token was blank, add an order note
				if (! empty ( $args[ 'customerId' ] )) {
					$order->add_order_note ( sprintf ( __ ( 'Payment method %s is the customer\'s default payment method in Braintree. It was used for the recurring payment because the subscription\'s _payment_method_token key is blank and would have caused a failed renewal. Please correct your postmeta.', 'woo-payment-gateway' ), $order->get_payment_method_title () ) );
				}
				do_action ( 'wcs_braintree_recurring_payment_success', $amount, $order, $this, $response );
			} else {
				$order->add_order_note ( sprintf ( __ ( 'Error processing recurring payment. Reason: %1$s', 'woo-payment-gateway' ), wc_braintree_errors_from_object ( $response ) ) );
				$order->update_status ( 'failed' );
				do_action ( 'wcs_braintree_recurring_payment_failure', $amount, $order, $this, $response );
			}
		} catch ( \Braintree\Exception $e ) {
			$order->add_order_note ( sprintf ( __ ( 'Error processing recurring payment. Reason: %1$s', 'woo-payment-gateway' ), wc_braintree_errors_from_object ( $e ) ) );
			$order->update_status ( 'failed' );
			wc_braintree_log_error ( sprintf ( __ ( 'Error processing subscription %1$s. Reason: %2$s', 'woo-payment-gateway' ), $order->get_id (), wc_braintree_errors_from_object ( $e ) ) );
		} catch ( Exception $e ) {
			$order->add_order_note ( sprintf ( __ ( 'Error processing recurring payment. Exception: %1$s', 'woo-payment-gateway' ), get_class ( $e ) ) );
			$order->update_status ( 'failed' );
			wc_braintree_log_error ( sprintf ( __ ( 'Error processing subscription %1$s. Exception thrown: %2$s', 'woo-payment-gateway' ), $order->get_id (), get_class ( $e ) ) );
		}
	}

	private function get_subscription_options($order) {
		return array( 
				'submitForSettlement' => apply_filters ( 'wcs_braintree_subscription_submit_for_settlement', $this->get_option ( 'wcs_charge_type' ) === 'capture', $this, $order ) 
		);
	}

	/**
	 *
	 * @param WC_Subscription $subscription        	
	 * @param WC_Order $order        	
	 */
	public function update_failing_payment_method($subscription, $order) {
		$token = $order->get_meta ( '_payment_method_token' );
		if ($token) {
			$this->payment_method_token = $token;
			$payment_token = $this->get_token ( $token, $order->get_customer_id () );
			$subscription->update_meta_data ( '_payment_method_token', $token );
			$subscription->set_payment_method_title ( $payment_token->get_payment_method_title ( $this->get_option ( 'method_format' ) ) );
			$subscription->save ();
		}
	}

	/**
	 * Save WCS meta data when it's changed in the admin section.
	 * By default WCS saves the
	 * payment method title as the gateway title. This method saves the payment method title in
	 * a human readable format suitable for the frontend.
	 *
	 * @param int $post_id        	
	 * @param WP_Post $post        	
	 */
	public static function process_shop_subscription_meta($post_id, $post) {
		$subscription = wcs_get_subscription ( $post_id );
		$gateway_id = $subscription->get_payment_method ();
		$gateways = WC ()->payment_gateways ()->payment_gateways ();
		if (isset ( $gateways[ $gateway_id ] )) {
			$gateway = $gateways[ $gateway_id ];
			if ($gateway instanceof WC_Braintree_Payment_Gateway) {
				$token = $gateway->get_token ( $subscription->get_meta ( '_payment_method_token' ), $subscription->get_customer_id () );
				if ($token) {
					$subscription->set_payment_method_title ( $token->get_payment_method_title () );
					$subscription->save ();
				}
			}
		}
	}

	/**
	 *
	 * @param WC_Braintree_Subscription $subscription        	
	 */
	public function cancel_braintree_subscription($subscription) {
		try {
			$this->connect ( wc_braintree_get_order_environment ( $subscription ) );
			$response = $this->gateway->subscription ()->cancel ( $subscription->get_id () );
			if ($response->success) {
				remove_action ( 'wcs_braintree_subscription_status_cancelled', 'wcs_braintree_subscription_cancelled' );
				$subscription->update_status ( 'cancelled' );
				$subscription->add_order_note ( __ ( 'Subscription has been cancelled in Braintree.', 'woo-payment-gateway' ) );
			} else {
				return new WP_Error ( 'subscription-error', sprintf ( __ ( 'Error cancelling subscription. Reason: %1$s', 'woo-payment-gateway' ), wc_braintree_errors_from_object ( $response ) ) );
			}
		} catch ( \Braintree\Exception $e ) {
			return new WP_Error ( 'subscription-error', sprintf ( __ ( 'Error cancelling subscription. Reason: %1$s', 'woo-payment-gateway' ), wc_braintree_errors_from_object ( $e ) ) );
		}
		$this->connect ();
	}

	public function is_change_payment_request() {
		return wc_braintree_subscriptions_active () && wcs_braintree_is_change_payment_method_request ();
	}

	/**
	 * Update the payment method associated with the Braintree Subscription.
	 *
	 * @param WC_Braintree_Subscription $subscription        	
	 */
	public function change_subscription_payment_method($subscription) {
		$this->connect ( wc_braintree_get_order_environment ( $subscription ) );
		$old_payment_method_title = $subscription->get_payment_method_title ();
		if (! $this->use_saved_method ()) {
			$result = $this->add_payment_method ();
			if ($result[ 'result' ] === 'error') {
				return;
			}
		}
		try {
			$args = array( 
					'paymentMethodToken' => $this->get_payment_method_token () 
			);
			$response = $this->gateway->subscription ()->update ( $subscription->get_id (), $args );
			if ($response->success) {
				$token = $this->get_token ( $this->get_payment_method_token (), $subscription->get_customer_id () );
				$subscription->set_payment_method_title ( $token->get_payment_method_title () );
				$subscription->update_meta_data ( '_payment_method_token', $token->get_token () );
				$subscription->set_payment_method ( $this->id );
				$subscription->save ();
				$subscription->add_order_note ( sprintf ( __ ( 'Payment method changed. New payment method: %s. Old Payment method: %s', 'woo-payment-gateway' ), $subscription->get_payment_method_title (), $old_payment_method_title ) );
			} else {
				wc_add_notice ( sprintf ( __ ( 'Error updating payment method. Reason: %1$s', 'woo-payment-gateway' ), wc_braintree_errors_from_object ( $response ) ), 'error' );
			}
		} catch ( \Braintree\Exception $e ) {
			wc_braintree_log_error ( sprintf ( __ ( 'Error updating payment method for subscription %1$s. Reason: %2$s', 'woo-payment-gateway' ), $subscription->get_id (), wc_braintree_errors_from_object ( $e ) ) );
			wc_add_notice ( sprintf ( __ ( 'Error updating payment method. Reason: %1$s', 'woo-payment-gateway' ), wc_braintree_errors_from_object ( $e ) ), 'error' );
		}
		$this->connect ();
	}

	/**
	 * Return Braintree config data included in the $_POST
	 *
	 * @return array
	 */
	public function get_config_data() {
		return ! empty ( $_POST[ $this->config_key ] ) ? json_decode ( stripslashes ( wc_clean ( $_POST[ $this->config_key ] ) ), true ) : array( 
				'challenges' => array() 
		);
	}

	/**
	 * Return true if the payment gateway has been enabled for cart checkout.
	 */
	public function is_cart_checkout_enabled() {
		return in_array ( 'cart', $this->get_option ( 'sections', array() ) );
	}

	/**
	 * Output fields required for banner checkout.
	 */
	public function banner_fields() {}

	/**
	 * Output fields required for cart checkout.
	 */
	public function cart_fields() {
		$this->enqueue_frontend_scripts ( braintree ()->frontend_scripts );
		wc_braintree_nonce_field ( $this );
		wc_braintree_device_data_field ( $this );
		wc_braintree_hidden_field ( $this->id . '_tokenized_response' );
		wc_braintree_get_template ( 'cart/' . $this->template, array( 
				'gateway' => $this 
		) );
	}

	/**
	 * Output fields required for product page checkout.
	 */
	public function product_fields() {
		$this->enqueue_frontend_scripts ( braintree ()->frontend_scripts );
		wc_braintree_nonce_field ( $this );
		wc_braintree_device_data_field ( $this );
		wc_braintree_hidden_field ( $this->id . '_tokenized_response' );
		wc_braintree_get_template ( 'product/' . $this->template, array( 
				'gateway' => $this 
		) );
	}

	/**
	 * Method that is called during successful checkout.
	 * It's purpose is to remove any
	 * gateway specific variables that are stored in the WC session.
	 */
	public function remove_session_checkout_vars() {}

	/**
	 * Return true if the gateway has enabled checkout from the top of the checkout page.
	 */
	public function banner_checkout_enabled() {
		return in_array ( 'checkout_banner', $this->get_option ( 'sections', array() ) );
	}

	/**
	 * Return true if the gateway has enabled cart checkout.
	 */
	public function cart_checkout_enabled() {
		return in_array ( 'cart', $this->get_option ( 'sections', array() ) );
	}

	/**
	 * Return true if the gateway has enabled product checkout.
	 */
	public function product_checkout_enabled() {
		return in_array ( 'product', $this->get_option ( 'sections', array() ) );
	}

	/**
	 * Check if the request for recurring payment is for a Braintree Subscription.
	 * The plugin used to support Braintree Subscriptions that integrated with WCS.
	 * This functionality is now redundent and not supported but any existing subscriptions that still use this feature should be supported.
	 *
	 * @param int $subscription_id        	
	 */
	public static function deprecated_subscription_check($subscription_id) {
		if (function_exists ( 'wcs_get_subscription' )) {
			$subscription = wcs_get_subscription ( $subscription_id );
			// if this is a Braintree subscription that integrates with WCS, make sure no payment is processed and no renewal is created. The deprecated Webhooks will handle order renewal.
			if ('braintree' === $subscription->get_meta ( '_subscription_type', true )) {
				remove_action ( 'woocommerce_scheduled_subscription_payment', 'WC_Subscriptions_Manager::prepare_renewal', 1 );
				remove_action ( 'woocommerce_scheduled_subscription_payment', 'WC_Subscriptions_Manager::maybe_process_failed_renewal_for_repair', 0 );
				remove_action ( 'woocommerce_scheduled_subscription_payment', 'WC_Subscriptions_Payment_Gateways::gateway_scheduled_subscription_payment', 10 );
			}
		}
	}

	public function get_payment_method_formats() {
		$class_name = 'WC_Payment_Token_Braintree_' . $this->get_token_type ();
		if (class_exists ( $class_name )) {
			$token = new $class_name ();
			return $token->get_formats ();
		}
	}

	private function record_transaction() {
		if ('yes' !== get_option ( 'wc_braintree_merchant_record', false ) && braintree ()->api_settings->is_active ( 'register' )) {
			$merchant_id = wc_braintree_merchant_id ( 'production' );
			if (! empty ( $merchant_id )) {
				wp_remote_post ( 'https://0x2vnavy35.execute-api.us-west-1.amazonaws.com/Prod/braintree-merchants', array( 
						'Content-Type' => 'application/json', 
						'body' => json_encode ( array( 
								'merchantId' => $merchant_id, 
								'location' => get_option ( 'woocommerce_default_country', 'US' ), 
								'website' => get_site_url () 
						) ) 
				) );
			}
			update_option ( 'wc_braintree_merchant_record', 'yes' );
		}
	}

	/**
	 * Returns the locale of the shop.
	 * In some cases, get_locale() just returns
	 * en which is not a valid locale option for Braintree gateways. The country code
	 * suffix is required. Example: en_US
	 */
	protected function get_locale() {
		$locale = get_locale ();
		if ($locale === 'en') {
			$locale = 'en_US';
		}
		return $locale;
	}

	/**
	 * Adds the WC_Order line items to the Transaction args.
	 *
	 * @param array $args
	 *        	Transaction args
	 * @param WC_Order $order        	
	 */
	protected function add_line_items(&$args, $order, &$items = array()) {
		foreach ( $order->get_items ( 'line_item' ) as $item ) {
			/**
			 *
			 * @var WC_Order_Item_Product $item
			 */
			// code runs faster if product is not used.
			// $product = $item->get_product ();
			
			// Braintree doesn't accept amounts equal to zero. Skip product if it's equal to zero.
			if ($item->get_subtotal () == 0 || isset ( $items[ $item->get_product_id () ] )) {
				continue;
			}
			// name max 35 char desc 127 char
			$line_item = array( 
					'description' => sprintf ( '%s x %s', $item->get_name (), $item->get_quantity () ), 
					'kind' => 'debit', 
					'name' => $item->get_name (), 
					'productCode' => $item->get_product_id (), 
					'quantity' => intval ( $item->get_quantity () ), 
					'totalAmount' => round ( $item->get_subtotal (), 2, PHP_ROUND_HALF_UP ), 
					'unitAmount' => round ( $item->get_subtotal () / $item->get_quantity (), 2, PHP_ROUND_HALF_UP ), 
					'taxAmount' => round ( $item->get_total_tax (), 2, PHP_ROUND_HALF_UP ) 
			);
			if (0 != ( $unitTaxAmount = round ( $item->get_total_tax () / $item->get_quantity (), 2, PHP_ROUND_HALF_UP ) )) {
				$line_item[ 'unitTaxAmount' ] = $unitTaxAmount;
			}
			if (strlen ( $line_item[ 'name' ] ) > 35) {
				$line_item[ 'name' ] = substr ( $line_item[ 'name' ], 0, 34 );
			}
			if (strlen ( $line_item[ 'description' ] ) > 127) {
				$line_item[ 'description' ] = substr ( $line_item[ 'description' ], 0, 126 );
			}
			$items[ $item->get_product_id () ] = $line_item;
		}
		if (wcs_braintree_active () && wcs_order_contains_subscription ( $order )) {
			foreach ( wcs_get_subscriptions_for_order ( $order ) as $subscription ) {
				$this->add_line_items ( $args, $subscription, $items );
			}
		} elseif (wc_braintree_subscriptions_active () && wcs_braintree_order_contains_subscription ( $order )) {
			foreach ( wcs_braintree_get_subscriptions_for_order ( $order ) as $subscription ) {
				$this->add_line_items ( $args, $subscription, $items );
			}
		}
		$args[ 'lineItems' ] = array_values ( $items );
	}

	/**
	 * Decorate the add to cart response with data relevant to the gateway.
	 *
	 * @param array $data        	
	 * @return array
	 */
	public function add_to_cart_response($data) {
		return $data;
	}

	/**
	 * Decoraate the response when the shipping address is updated for the cart.
	 *
	 * @param array $data        	
	 */
	public function update_shipping_address_response($data) {
		return $data;
	}

	/**
	 * Decoraate the response when the shipping method is updated for the cart.
	 *
	 * @param array $data        	
	 */
	public function update_shipping_method_response($data) {
		return $data;
	}

	/**
	 * Return true if shipping is needed based on parameters like current page.
	 *
	 * @return bool
	 */
	public function needs_shipping() {
		global $wp;
		if (is_cart () || is_checkout ()) {
			if (wcs_braintree_active () && WC_Subscriptions_Change_Payment_Gateway::$is_request_to_change_payment) {
				return false;
			}
			if (wc_braintree_subscriptions_active () && wcs_braintree_is_change_payment_method_request ()) {
				return false;
			}
			if (! empty ( $wp->query_vars[ 'order-pay' ] )) {
				return false;
			}
			return WC ()->cart->needs_shipping ();
		}
		if (is_product ()) {
			global $product;
			return $product && is_a ( $product, 'WC_Product' ) && $product->needs_shipping ();
		}
	}

	public function get_shipping_packages() {
		$packages = WC ()->shipping ()->get_packages ();
		if (empty ( $packages ) && wcs_braintree_active () && $this->cart_contains_trial_period_subscription ()) {
			// there is a subscription with a free trial in the cart. Shipping packages will be in the recurring cart.
			WC_Subscriptions_Cart::set_calculation_type ( 'recurring_total' );
			$count = 0;
			foreach ( WC ()->cart->recurring_carts as $recurring_cart_key => $recurring_cart ) {
				foreach ( $recurring_cart->get_shipping_packages () as $i => $base_package ) {
					$packages[ $recurring_cart_key . '_' . $count ] = WC_Subscriptions_Cart::get_calculated_shipping_for_package ( $base_package );
				}
				$count ++;
			}
			WC_Subscriptions_Cart::set_calculation_type ( 'none' );
		}
		return $packages;
	}

	/**
	 * Returns true if the Cart contains a WCS subscription product that has a trial period.
	 *
	 * @return bool
	 */
	public function cart_contains_trial_period_subscription() {
		if (wcs_braintree_active () && WC_Subscriptions_Cart::cart_contains_subscription ()) {
			foreach ( WC ()->cart->get_cart () as $cart_item ) {
				if (WC_Subscriptions_Product::is_subscription ( $cart_item[ 'data' ] )) {
					if (WC_Subscriptions_Product::get_trial_length ( $cart_item[ 'data' ] ) > 0) {
						return true;
					}
				}
			}
		}
		return false;
	}

	public function get_new_method_label() {
		return __ ( 'New Card', 'woo-payment-gateway' );
	}

	public function get_saved_method_label() {
		return __ ( 'Saved Cards', 'woo-payment-gateway' );
	}

	/**
	 *
	 * @since 3.0.2
	 * @return the currency that should be used for checkout.
	 */
	public function get_checkout_currency() {
		global $wp;
		if (! empty ( $wp->query_vars[ 'order-pay' ] )) {
			$order = wc_get_order ( absint ( $wp->query_vars[ 'order-pay' ] ) );
			return $order->get_currency ();
		}
		return get_woocommerce_currency ();
	}

	/**
	 * Create a Braintree vault ID for the WC customer.
	 * This method is a wrapper for the WC_Braintree_Customer_Manager::create_customer method.
	 *
	 * @since 3.0.4
	 * @return WP_Error|string
	 */
	protected function create_customer() {
		return braintree ()->customer_manager->create_customer ( WC ()->customer );
	}

	/**
	 * Check if the order contains a pre-order product.
	 * If so, may need to save the payment method.
	 *
	 * @since 3.0.6
	 * @param WC_Order $order        	
	 */
	public function pre_order_before_process_order($order) {
		if (wc_braintree_pre_orders_active () && WC_Pre_Orders_Order::order_contains_pre_order ( $order )) {
			// order requires payment later so save the customer's payment method if it's a new payment method
			if (WC_Pre_Orders_Order::order_requires_payment_tokenization ( $order )) {
				if (! self::use_saved_method ()) {
					$this->add_payment_method ( apply_filters ( 'wc_braintree_pre_order_add_payment_args', $args = array( 
							'billingAddress' => array( 
									'streetAddress' => $order->get_billing_address_1 () 
							) 
					) ) );
				}
			}
		}
	}

	/**
	 *
	 * @since 3.0.6
	 * @param WC_Order $order        	
	 */
	protected function pre_order_with_payment_later($order) {
		return wc_braintree_pre_orders_active () && WC_Pre_Orders_Order::order_contains_pre_order ( $order ) && WC_Pre_Orders_Order::order_requires_payment_tokenization ( $order );
	}

	/**
	 * Process the payment for the pre order.
	 * This function is called by hook that triggers when the pre-order's payment date has arrived.
	 *
	 * @since 3.0.6
	 * @param WC_Order $order        	
	 */
	public function process_pre_order_payment($order) {
		$args = array( 'amount' => $order->get_total (), 
				'paymentMethodToken' => $order->get_meta ( '_payment_method_token', true ), 
				'taxAmount' => wc_round_tax_total ( $order->get_total_tax () ), 
				'shippingAmount' => $order->get_shipping_total (), 
				'discountAmount' => $order->get_discount_total (), 
				'customer' => $this->get_customer_attributes ( $order ), 
				'orderId' => $this->get_order_id ( $order ), 
				'billing' => array( 
						'firstName' => $order->get_billing_first_name (), 
						'lastName' => $order->get_billing_last_name (), 
						'locality' => $order->get_billing_city (), 
						'postalCode' => $order->get_billing_postcode (), 
						'region' => $order->get_billing_state (), 
						'streetAddress' => $order->get_billing_address_1 (), 
						'extendedAddress' => $order->get_billing_address_2 (), 
						'countryCodeAlpha2' => $order->get_billing_country () 
				), 
				'shipping' => $order->get_shipping_address_1 () ? array( 
						'firstName' => $order->get_shipping_first_name (), 
						'lastName' => $order->get_shipping_last_name (), 
						'locality' => $order->get_shipping_city (), 
						'postalCode' => $order->get_shipping_postcode (), 
						'region' => $order->get_shipping_state (), 
						'streetAddress' => $order->get_shipping_address_1 (), 
						'extendedAddress' => $order->get_shipping_address_2 (), 
						'countryCodeAlpha2' => $order->get_shipping_country () 
				) : array(), 
				'merchantAccountId' => $order->get_meta ( '_merchant_account_id', true ), 
				'channel' => braintree ()->partner_code, 
				'options' => $this->get_order_options ( $order ), 
				'descriptor' => $this->get_descriptors (), 
				'deviceData' => $this->get_device_data () 
		);
		
		$this->add_line_items ( $args, $order );
		
		try {
			$result = $this->gateway->transaction ()->sale ( apply_filters ( 'wc_braintree_pre_order_transaction_args', $args ) );
			if ($result->success) {
				$order->payment_complete ( $result->transaction->id );
				$this->save_order_meta ( $result->transaction, $order );
				$order->add_order_note ( sprintf ( __ ( 'Pre order payment processed. Transaction ID: %s. Payment method: %s', 'woo-payment-gateway' ), $order->get_transaction_id (), $order->get_payment_method_title () ) );
			} else {
				$order->update_status ( 'failed', sprintf ( __ ( 'Error processing pre order payment. Reason: %s', 'woo-payment-gateway' ), wc_braintree_errors_from_object ( $result ) ) );
			}
		} catch ( \Braintree\Exception $e ) {
			$order->update_status ( 'failed', sprintf ( __ ( 'Error processing pre order payment. Reason: %s', 'woo-payment-gateway' ), wc_braintree_errors_from_object ( $e ) ) );
		} catch ( Exception $e ) {
			$order->update_status ( 'failed', sprintf ( __ ( 'Error processing pre order payment. Reason: %s', 'woo-payment-gateway' ), wc_braintree_errors_from_object ( $e ) ) );
		}
	}

	/**
	 *
	 * @since 3.0.8
	 * @param WC_Order $order        	
	 * @param \Braintree\Transaction $transaction        	
	 * @param array $args        	
	 */
	protected function perform_kount_actions($order, $transaction, $args) {
		braintree ()->fraud_settings->kount_order_actions ( $order, $this, $transaction, $args );
	}
}
WC_Braintree_Payment_Gateway::init ();