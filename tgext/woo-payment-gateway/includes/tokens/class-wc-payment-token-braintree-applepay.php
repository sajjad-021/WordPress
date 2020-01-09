<?php
if (! defined ( 'ABSPATH' )) {
	exit (); // Exit if accessed directly
}
if (! class_exists ( 'WC_Payment_Token_Braintree_CC' )) {
	exit ();
}
/**
 *
 * @since 3.0.0
 * @package Braintree/Classes/PaymentTokens
 *         
 */
class WC_Payment_Token_Braintree_ApplePay extends WC_Payment_Token_Braintree_CC {

	protected $type = 'Braintree_ApplePay';

	protected $braintree_data = array( 
			'payment_instrument_name' => '' 
	);

	public function __construct($token = '') {
		$this->braintree_data = array_merge ( parent::get_braintree_data ( $this ), $this->braintree_data );
		parent::__construct ( $token );
	}

	/**
	 *
	 * @param \Braintree\ApplePayCard|\Braintree\Transaction\ApplePayCardDetails $method        	
	 * {@inheritDoc}
	 *
	 * @see WC_Payment_Token_Braintree_CC::init_from_payment_method()
	 */
	public function init_from_payment_method($method) {
		$this->set_card_type ( $method->cardType );
		$this->set_method_type ( 'ApplePay' );
		$this->set_expiry_month ( $method->expirationMonth );
		$this->set_expiry_year ( $method->expirationYear );
		$this->set_card_holder ( $method->cardholderName );
		$this->set_bin ( $method->bin );
		$this->set_last4 ( $method->last4 );
		$this->set_masked_number ( $this->get_bin () . '******' . $this->get_last4 () );
		$this->set_payment_instrument_name ( $method->paymentInstrumentName );
		$this->set_payment_instrument_type ( \Braintree\PaymentInstrumentType::APPLE_PAY_CARD );
		$this->set_token ( $method->token );
	}

	public function set_payment_instrument_name($value) {
		$this->set_prop ( 'payment_instrument_name', $value );
	}

	public function get_payment_instrument_name() {
		return $this->get_prop ( 'payment_instrument_name' );
	}

	public function get_formats() {
		return array( 
				'apple_type_last4' => array( 
						'label' => __ ( 'Type and Last Four', 'woo-payment-gateway' ), 
						'example' => 'Apple Pay - Discover 2928', 
						'format' => 'Apple Pay - {payment_instrument_name}' 
				), 
				'type_last4' => array( 
						'label' => __ ( 'Type and Last Four', 'woo-payment-gateway' ), 
						'example' => 'Discover 2928', 
						'format' => '{payment_instrument_name}' 
				) 
		);
	}
}