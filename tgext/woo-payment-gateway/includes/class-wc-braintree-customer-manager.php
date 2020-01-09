<?php
/**
 * Class that manages customer create and update within the Braintree vault.
 * @since 3.0.0
 * @package Braintree/Classes
 *
 */
class WC_Braintree_Customer_Manager {

	public function __construct() {
		add_action ( 'woocommerce_checkout_update_customer', array( 
				$this, 'checkout_update_customer' 
		), 10, 2 );
	}

	/**
	 *
	 * @param WC_Customer $customer        	
	 * @param array $data        	
	 */
	public function checkout_update_customer($customer, $data) {
		if ($this->should_create_customer ( $customer->get_id () )) {
			$result = $this->create_customer ( $customer );
			if (is_wp_error ( $result )) {
				wc_add_notice ( sprintf ( __ ( 'Error creating customer in Braintree. Reason: %s', 'woo-payment-gateway' ), $result->get_error_message () ) );
			}
		} elseif ($this->should_update_customer ( $customer )) {
			$result = $this->update_customer ( $customer );
			if (is_wp_error ( $result )) {
				wc_add_notice ( sprintf ( __ ( 'Error updating customer in Braintree. Reason: %s', 'woo-payment-gateway' ), $result->get_error_message () ) );
			}
		}
	}

	/**
	 *
	 * @since 3.0.4
	 * @param WC_Customer $customer        	
	 */
	public function create_customer($customer) {
		if (braintree ()->gateway ()) {
			try {
				$args = apply_filters ( 'wc_braintree_create_customer_args', $this->get_customer_args ( $customer ) );
				$response = braintree ()->gateway ()->customer ()->create ( $args );
				if ($response->success) {
					wc_braintree_save_customer ( $customer->get_id (), $response->customer->id );
					return $response->customer->id;
				} else {
					throw new Exception ( sprintf ( '%1$s' ), wc_braintree_errors_from_object ( $response ) );
				}
			} catch ( \Braintree\Exception $e ) {
				wc_braintree_log_error ( sprintf ( 'Error creating Braintree customer. User ID: %s. Exception: %s', $customer->get_id (), get_class ( $e ) ) );
				return new WP_Error ( 'customer-error', sprintf ( '%1$s', 'woo-payment-gateway' ), wc_braintree_errors_from_object ( $e ) );
			}
		}
	}

	/**
	 *
	 * @since 3.0.4
	 * @param WC_Customer $customer        	
	 */
	public function update_customer($customer) {
		if (braintree ()->gateway ()) {
			try {
				$vault_id = wc_braintree_get_customer_id ( $customer->get_id () );
				$response = braintree ()->gateway ()->customer ()->update ( $vault_id, apply_filters ( 'wc_braintree_update_customer_args', $this->get_customer_args ( $customer ) ) );
				if (! $response->success) {
					wc_braintree_log_error ( sprintf ( __ ( 'Error updating customer %1$s in gateway. Reason: %2$s', 'woo-payment-gateway' ), $customer->get_id (), wc_braintree_errors_from_object ( $response ) ) );
					throw new Exception ( sprintf ( '%1$s' ), wc_braintree_errors_from_object ( $response ) );
				}
				return true;
			} catch ( \Braintree\Exception $e ) {
				wc_braintree_log_error ( sprintf ( 'Error updating Braintree customer. User ID: %s. Exception: %s', $customer->get_id (), get_class ( $e ) ) );
				return new WP_Error ( 'customer-error', sprintf ( '%s' ), wc_braintree_errors_from_object ( $e ) );
			}
		}
	}

	/**
	 * Returns true if a vault ID should be created in Braintree for the customer.
	 *
	 * @param int $user_id        	
	 */
	private function should_create_customer($user_id) {
		$vault_id = wc_braintree_get_customer_id ( $user_id );
		return empty ( $vault_id ) && $user_id > 0;
	}

	/**
	 * Should the customer be updated in Braintree?
	 *
	 * @param WC_Customer $customer        	
	 */
	private function should_update_customer($customer) {
		$vault_id = wc_braintree_get_customer_id ( $customer->get_id () );
		if ($vault_id) {
			$changes = $customer->get_changes ();
			if (! empty ( $changes ) && array_intersect_key ( $changes, array_flip ( $this->get_customer_keys () ) )) {
				return true;
			}
		}
		return false;
	}

	private function get_customer_keys() {
		return array( 'first_name', 'last_name', 'email' 
		);
	}

	/**
	 *
	 * @param WC_Customer $customer        	
	 */
	private function get_customer_args($customer) {
		return array( 
				'firstName' => $customer->get_first_name (), 
				'lastName' => $customer->get_last_name (), 
				'company' => $customer->get_billing_company (), 
				'email' => $customer->get_email (), 
				'phone' => $customer->get_billing_phone () 
		);
	}
}