<?php
/**
 * Skrill Paysafecash
 *
 * This gateway is used for Skrill Paysafecash.
 * Copyright (c) Skrill
 *
 * @class       Gateway_Skrill_PCH
 * @extends     Skrill_Payment_Gateway
 * @package     Skrill/Classes
 * @located at  /includes/gateways
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Gateway_Skrill_PCH
 */
class Gateway_Skrill_PCH extends Skrill_Payment_Gateway {

	/**
	 * Id
	 *
	 * @var string
	 */
	public $id = 'skrill_pch';

	/**
	 * Payment method logo
	 *
	 * @var string
	 */
	public $payment_method_logo = 'pch.png';

	/**
	 * Payment method
	 *
	 * @var string
	 */
	public $payment_method = 'PCH';

	/**
	 * Payment brand
	 *
	 * @var string
	 */
	public $payment_brand = 'PCH';

	/**
	 * Allowed countries
	 *
	 * @var array
	 */
	protected $allowed_countries = array( 'AUT','CHE','GRC','HRV','HUN','ITA','MLT','PRT','ROU','SVN','ESP' );

	/**
	 * Payment method description
	 *
	 * @var string
	 */
	public $payment_method_description = 'Austria, Croatia, Greece, Hungary, Italy, Malta, Portugal, Romania, Slovenia, Spain, Switzerland';

	/**
	 * Get payment title.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Paysafecash', 'wc-skrill' );
	}
}

$obj = new Gateway_Skrill_PCH();
