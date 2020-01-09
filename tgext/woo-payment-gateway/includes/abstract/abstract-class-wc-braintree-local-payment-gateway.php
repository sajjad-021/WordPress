<?php
if (! class_exists ( 'WC_Braintree_Payment_Gateway' )) {
	return;
}
/**
 * Abstract class that should be extended by local payment gateways.
 *
 * @version 3.0.0
 * @package Braintree/Abstracts
 *         
 */
abstract class WC_Braintree_Local_Payment_Gateway extends WC_Braintree_Payment_Gateway {

	/**
	 *
	 * @var array currencies that this payment method accepts
	 */
	protected $currencies = array();

	public $default_title = '';

	public $payment_id_key;

	public function __construct() {
		$this->template = 'local-payment.php';
		$this->token_type = 'Local_Payment';
		parent::__construct ();
		$this->settings[ 'method_format' ] = 'source_and_payer';
		// must always be capture per Braintree's documentation
		$this->settings[ 'charge_type' ] = 'capture';
		
		$this->payment_id_key = $this->id . '_payment_id';
	}

	public function add_hooks() {
		parent::add_hooks ();
		remove_filter ( 'wc_braintree_admin_settings_tabs', array( 
				$this, 'admin_settings_tabs' 
		) );
		add_filter ( 'wc_braintree_local_gateways_tab', array( 
				$this, 'admin_settings_tabs' 
		) );
		add_action ( 'woocommerce_settings_checkout_braintree_local_gateways_' . $this->id, array( 
				$this, 'admin_options' 
		) );
		add_action ( 'wc_braintree_settings_before_options_braintree_local_gateways_' . $this->id, array( 
				$this, 'navigation_menu' 
		) );
		add_action ( 'woocommerce_update_options_checkout_braintree_local_gateways_' . $this->id, array( 
				$this, 'process_admin_options' 
		) );
	}

	public function set_supports() {
		$this->supports = array( 'products', 'refunds', 
				'wc_braintree_fees' 
		);
	}

	public function init_form_fields() {
		$this->form_fields = apply_filters ( 'wc_' . $this->id . '_form_fields', include WC_BRAINTREE_PATH . 'includes/gateways/settings/local-payment-settings.php', $this );
	}

	/**
	 * Return true only if the gateway requirements for being displayed are met.
	 *
	 * @return bool
	 */
	public function is_local_payment_available() {
		$customer = WC ()->customer;
		return in_array ( $customer->get_billing_country (), $this->countries ) && in_array ( get_woocommerce_currency (), $this->currencies );
	}

	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see WC_Braintree_Payment_Gateway::enqueue_checkout_scripts()
	 */
	public function enqueue_checkout_scripts($scripts) {}

	public function localize_script_params() {
		return array_merge ( $this->get_localized_standard_params (), array( 
				'payment_key' => $this->payment_id_key, 
				'payment_type' => str_replace ( 'braintree_', '', $this->id ), 
				'button_text' => $this->order_button_text, 
				'return_url' => wc_get_checkout_url (), 
				'routes' => array( 
						'payment_data' => braintree ()->rest_api->local_payment->rest_url () . 'payment-data' 
				) 
		) );
	}

	public function output_settings_nav() {
		global $current_section, $wc_braintree_subsection;
		parent::output_settings_nav ();
		include braintree ()->plugin_path () . 'includes/admin/views/html-local-gateways-nav.php';
	}

	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see WC_Braintree_Payment_Gateway::get_payment_method_from_transaction()
	 */
	public function get_payment_method_from_transaction($transaction) {
		return $transaction->localPayment;
	}

	public function get_gateway_supports_description() {
		$text = '';
		if ($this->currencies) {
			$text = sprintf ( __ ( '%s is available when store currency is %s', 'woo-payment-gateway' ), $this->get_title (), '<b>' . implode ( ', ', $this->currencies ) . '</b>' );
		}
		if ($this->countries) {
			$text .= sprintf ( __ ( ' and billing country is %s', 'woo-payment-gateway' ), '<b>' . implode ( ', ', $this->countries ) . '</b>' );
		}
		return $text;
	}

	public function process_payment($order_id) {
		$order = wc_get_order ( $order_id );
		$nonce = $this->get_payment_method_nonce ();
		$payment_id = $order->get_meta ( 'wc_braintree_payment_id', true );
		if ($payment_id) {
			// no nonce so customer must have closed window. Force new payment ID.
			if (! $nonce) {
				return array( 'result' => 'success', 
						'redirect' => $this->get_order_created_url ( $order ) 
				);
			} else {
				// order has a payment ID so process it
				// check that the payment wasn't processed using webhooks
				if (! $this->has_order_lock ( $order )) {
					$this->set_order_lock ( $order );
					$result = parent::process_payment ( $order_id );
					$this->remove_order_lock ( $order );
					return $result;
				}
			}
		} else {
			// check if the order was processed via webhook already.
			$payment_id = isset ( $_POST[ $this->payment_id_key ] ) ? $_POST[ $this->payment_id_key ] : '';
			if ($payment_id) {
				$order2 = wc_braintree_get_order_for_payment_id ( $payment_id );
				if ($order2 && $order2->get_id () != $order->get_id ()) {
					$order->delete ();
					return array( 'result' => 'success', 
							'redirect' => $order2->get_checkout_order_received_url () 
					);
				}
			}
			return array( 'result' => 'success', 
					'redirect' => $this->get_order_created_url ( $order ) 
			);
		}
	}

	/**
	 *
	 * @param WC_Order $order        	
	 */
	private function get_order_created_url($order) {
		// timestamp added so url is always unoque
		return sprintf ( '#local_payment=%s&order_id=%s&timestamp=%s', $order->get_payment_method (), $order->get_id (), time () );
	}

	/**
	 *
	 * @since 3.0.8
	 * @param WC_Order $order        	
	 */
	public function set_order_lock($order) {
		update_post_meta ( $order->get_id (), '_wc_braintree_lock', 1 );
	}

	/**
	 *
	 * @since 3.0.8
	 * @param WC_Order $order        	
	 */
	public function remove_order_lock($order) {
		delete_post_meta ( $order->get_id (), '_wc_braintree_lock' );
	}

	/**
	 *
	 * @since 3.0.8
	 * @param WC_Order $order        	
	 */
	public function has_order_lock($order) {
		return $order->get_meta ( '_wc_braintree_lock', true ) == 1;
	}
}