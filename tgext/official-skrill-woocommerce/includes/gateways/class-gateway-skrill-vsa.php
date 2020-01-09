<?php
/**
 * Skrill Visa
 *
 * This gateway is used for Skrill Visa.
 * Copyright (c) Skrill
 *
 * @class       Gateway_Skrill_VSA
 * @extends     Skrill_Payment_Gateway
 * @package     Skrill/Classes
 * @located at  /includes/gateways
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Gateway_Skrill_VSA
 */
class Gateway_Skrill_VSA extends Skrill_Payment_Gateway {

	/**
	 * Id
	 *
	 * @var string
	 */
	public $id = 'skrill_vsa';

	/**
	 * Payment method logo
	 *
	 * @var string
	 */
	public $payment_method_logo = 'vsa.png';

	/**
	 * Payment method
	 *
	 * @var string
	 */
	public $payment_method = 'VSA';

	/**
	 * Payment brand
	 *
	 * @var string
	 */
	public $payment_brand = 'VSA';

	/**
	 * True when payment method is one of the credit card list
	 *
	 * @var array
	 */
	protected $is_payment_method_in_credit_card_list = true;

	/**
	 * Get payment title.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Visa', 'wc-skrill' );
	}
}

$obj = new Gateway_Skrill_VSA();
