<?php
/**
 * Skrill American Express
 *
 * This gateway is used for Skrill American Express.
 * Copyright (c) Skrill
 *
 * @class       Gateway_Skrill_AMX
 * @extends     Skrill_Payment_Gateway
 * @package     Skrill/Classes
 * @located at  /includes/gateways
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Gateway_Skrill_AMX
 */
class Gateway_Skrill_AMX extends Skrill_Payment_Gateway {

	/**
	 * Id
	 *
	 * @var string
	 */
	public $id = 'skrill_amx';

	/**
	 * Payment method logo
	 *
	 * @var string
	 */
	public $payment_method_logo = 'amx.png';

	/**
	 * Payment method
	 *
	 * @var string
	 */
	public $payment_method = 'AMX';

	/**
	 * Payment brand
	 *
	 * @var string
	 */
	public $payment_brand = 'AMX';

	/**
	 * True when payment method is one of the credit card list
	 *
	 * @var array
	 */
	protected $is_payment_method_in_credit_card_list = true;

	/**
	 * Excepted countries
	 *
	 * @var array
	 */
	protected $excepted_countries = array( 'USA' );

	/**
	 * Payment method description
	 *
	 * @var string
	 */
	public $payment_method_description = 'All Countries (excluding United States Of America)';

	/**
	 * Get payment title.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'American Express', 'wc-skrill' );
	}
}

$obj = new Gateway_Skrill_AMX();
