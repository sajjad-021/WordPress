<?php
/**
 * Skrill MasterCard
 *
 * This gateway is used for Skrill MasterCard.
 * Copyright (c) Skrill
 *
 * @class       Gateway_Skrill_MSC
 * @extends     Skrill_Payment_Gateway
 * @package     Skrill/Classes
 * @located at  /includes/gateways
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Gateway_Skrill_MSC
 */
class Gateway_Skrill_MSC extends Skrill_Payment_Gateway {

	/**
	 * Id
	 *
	 * @var string
	 */
	public $id = 'skrill_msc';

	/**
	 * Payment method logo
	 *
	 * @var string
	 */
	public $payment_method_logo = 'msc.png';

	/**
	 * Payment method
	 *
	 * @var string
	 */
	public $payment_method = 'MSC';

	/**
	 * Payment brand
	 *
	 * @var string
	 */
	public $payment_brand = 'MSC';

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
		return __( 'MasterCard', 'wc-skrill' );
	}
}

$obj = new Gateway_Skrill_MSC();
