<?php
/**
 *
 * @since 3.0.0
 * @package Braintree/Admin
 *
 */
if (! class_exists ( 'WC_Braintree_Advanced_Settings_API' )) {
	return;
}
class WC_Braintree_Fee_Settings extends WC_Braintree_Advanced_Settings_API {

	public function __construct() {
		$this->id = 'braintree_fee';
		$this->tab_title = __ ( 'Fee Settings', 'woo-payment-gateway' );
		parent::__construct ();
		add_action ( 'woocommerce_after_calculate_totals', array( 
				$this, 'after_calculate_totals' 
		) );
		add_action ( 'wp_enqueue_scripts', array( $this, 
				'enqueue_frontend_scripts' 
		) );
	}

	public function init_form_fields() {
		$this->form_fields = apply_filters ( 'wc_braintree_fee_form_fields', array( 
				'title' => array( 
						'title' => __ ( 'Fees', 'woo-payment-gateway' ), 
						'type' => 'title', 
						'description' => __ ( 'You can add a fee to the customer\'s order such as a convenience fee for accepting credit cards.
								<a target="_blank" href="https://docs.paymentplugins.com/wc-braintree/config/#/braintree_advanced?id=fee-settings">Fee Guide & Examples</a>', 'woo-payment-gateway' ) 
				), 
				'enabled' => array( 'type' => 'checkbox', 
						'title' => __ ( 'Enabled', 'woo-payment-gateway' ), 
						'default' => 'no', 'value' => 'yes', 
						'desc_tip' => true, 
						'description' => __ ( 'If enabled, you can charge fees on the checkout page.', 'woo-payment-gateway' ) 
				), 
				'fees' => array( 'type' => 'fee', 
						'title' => '' 
				) 
		) );
	}

	public function enqueue_admin_scripts() {
		wp_enqueue_script ( 'wc-braintree-fee-settings', braintree ()->assets_path () . 'js/admin/fee-settings.js', array( 
				'wc-braintree-admin-settings', 
				'wc-enhanced-select', 'underscore', 
				'backbone' 
		), braintree ()->version, true );
	}

	public function generate_fee_html($key, $data) {
		$field_key = $this->get_field_key ( $key );
		$data = wp_parse_args ( $data, array( 
				'title' => '', 'class' => '', 
				'desc_tip' => true 
		) );
		$gateways = wc_braintree_get_fee_gateways ();
		ob_start ();
		include braintree ()->plugin_path () . 'includes/admin/views/fee-template.php';
		return ob_get_clean ();
	}

	public function validate_fee_field($key, $value) {
		$value = is_array ( $value ) ? $value : array();
		return $value;
	}

	public function enqueue_frontend_scripts() {
		if ($this->is_active ( 'enabled' ) && is_checkout () && $this->has_fees ()) {
			$scripts = braintree ()->frontend_scripts;
			$scripts->enqueue_script ( 'checkout-fees', $scripts->assets_url ( 'js/frontend/checkout-fees.js' ), array( 
					'jquery' 
			) );
		}
	}

	/**
	 *
	 * @param WC_Cart $cart        	
	 */
	public function after_calculate_totals($cart) {
		if ($this->is_active ( 'enabled' ) && $this->has_fees ()) {
			WC ()->session->set ( 'wc_braintree_cart_total', $cart->total );
			remove_action ( 'woocommerce_after_calculate_totals', array( 
					$this, 'after_calculate_totals' 
			) );
			
			add_action ( 'woocommerce_cart_calculate_fees', array( 
					$this, 'calculate_fees' 
			) );
			$cart->calculate_totals ();
		}
	}

	/**
	 *
	 * @param WC_Cart $cart        	
	 */
	public function calculate_fees($cart) {
		$fees = $this->get_option ( 'fees' );
		$gateway_id = WC ()->session->get ( 'chosen_payment_method', false );
		if (! empty ( $fees ) && $gateway_id) {
			foreach ( $fees as $fee ) {
				/**
				 * Allow other plugins to adjust the fee such as the name, calculation, etc.
				 *
				 * @var array
				 */
				$fee = apply_filters ( 'wc_braintree_fee_attributes', $fee );
				if (in_array ( $gateway_id, $fee[ 'gateways' ] )) {
					$taxable = $fee[ 'tax_status' ] === 'taxable';
					$cart->add_fee ( $fee[ 'name' ], $this->calculate_amount ( $fee, $cart ), $taxable );
				}
			}
		}
	}

	/**
	 *
	 * @param array $fee        	
	 * @param WC_Cart $cart        	
	 */
	private function calculate_amount($fee, $cart) {
		if (! class_exists ( 'WC_Eval_Math' )) {
			include_once WC ()->plugin_path () . '/includes/libraries/class-wc-eval-math.php';
		}
		$args = $this->get_args ( $cart );
		$calculation = str_replace ( array_keys ( $args ), $args, $fee[ 'calculation' ] );
		return WC_Eval_Math::evaluate ( $calculation );
	}

	/**
	 *
	 * @param WC_Cart $cart        	
	 */
	private function get_args($cart) {
		return apply_filters ( 'wc_braintree_fee_args', array( 
				'[qty]' => $cart->get_cart_contents_count (), 
				'[cost]' => WC ()->session->get ( 'wc_braintree_cart_total', 0 ) 
		) );
	}

	/**
	 * Returns true if there are configured fees.
	 */
	private function has_fees() {
		$fees = $this->get_option ( 'fees' );
		return ! empty ( $fees );
	}
}