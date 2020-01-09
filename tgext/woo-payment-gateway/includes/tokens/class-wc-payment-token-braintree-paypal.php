<?php
if (! defined ( 'ABSPATH' )) {
	exit (); // Exit if accessed directly
}
if (! class_exists ( 'WC_Payment_Token' )) {
	exit ();
}
/**
 *
 * @since 3.0.0
 * @package Braintree/Classes/PaymentTokens
 *
 */
class WC_Payment_Token_Braintree_PayPal extends WC_Payment_Token_Braintree {

	protected $type = 'Braintree_PayPal';

	protected $braintree_data = array( 
			'method_type' => 'PayPal', 'email' => '', 
			'authorization_id' => '', 'first_name' => '', 
			'last_name' => '' 
	);

	/**
	 *
	 * @param \Braintree\PayPalAccount|\Braintree\Transaction\PayPalDetails $method        	
	 * {@inheritDoc}
	 *
	 * @see WC_Payment_Token_Braintree::init_from_payment_method()
	 */
	public function init_from_payment_method($method) {
		$this->set_method_type ( 'PayPal' );
		if ($method instanceof \Braintree\Transaction\PayPalDetails) {
			$this->set_email ( $method->payerEmail );
			$this->set_first_name ( $method->payerFirstName );
			$this->set_last_name ( $method->payerLastName );
		} else {
			$this->set_email ( $method->email );
		}
		$this->set_payment_instrument_type ( \Braintree\PaymentInstrumentType::PAYPAL_ACCOUNT );
		$this->set_token($method->token);
	}

	public function set_email($value) {
		$this->set_prop ( 'email', $value );
	}

	public function get_email() {
		return $this->get_prop ( 'email' );
	}

	public function set_authorization_id($value) {
		$this->set_prop ( 'authorization_id', $value );
	}

	public function get_authorization_id() {
		return $this->get_prop ( 'authorization_id' );
	}

	public function set_first_name($value) {
		$this->set_prop ( 'first_name', $value );
	}

	public function get_first_name() {
		return $this->get_prop ( 'first_name' );
	}

	public function set_last_name($value) {
		$this->set_prop ( 'last_name', $value );
	}

	public function get_last_name($value) {
		return $this->get_prop ( 'last_name' );
	}

	public function get_formats() {
		return array( 
				'paypal_and_email' => array( 
						'label' => __ ( 'PayPal & Email', 'woo-payment-gateway' ), 
						'example' => 'PayPal - john@example.com', 
						'format' => 'PayPal - {email}' 
				), 
				'email' => array( 
						'label' => __ ( 'Email', 'woo-payment-gateway' ), 
						'example' => 'john@example.com', 
						'format' => '{email}' 
				), 
				'paypal' => array( 
						'label' => __ ( 'PayPal', 'woo-payment-gateway' ), 
						'example' => 'PayPal', 
						'format' => __ ( 'PayPal', 'woo-payment-gateway' ) 
				) 
		);
	}
}
