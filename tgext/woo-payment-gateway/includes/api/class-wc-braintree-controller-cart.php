<?php
/**
 *
 * @since 3.0.0
 * @package Braintree/API
 *
 */
class WC_Braintree_Controller_Cart extends WC_Braintree_Rest_Controller {

	public function register_routes() {
		register_rest_route ( $this->rest_uri (), 'cart', array( 
				'methods' => WP_REST_Server::CREATABLE, 
				'callback' => array( $this, 'add_to_cart' 
				), 
				'args' => array( 
						'product_id' => array( 
								'type' => 'number', 
								'required' => true 
						), 
						'payment_method' => array( 
								'required' => true 
						), 
						'qty' => array( 'required' => true 
						) 
				) 
		) );
		register_rest_route ( $this->rest_uri (), 'cart/shipping-method', array( 
				'methods' => WP_REST_Server::CREATABLE, 
				'callback' => array( $this, 
						'update_shipping_method' 
				), 
				'args' => array( 
						'shipping_methods' => array( 
								'required' => true 
						), 
						'payment_method' => array( 
								'required' => true 
						) 
				) 
		) );
		register_rest_route ( $this->rest_uri (), 'cart/shipping-address', array( 
				'methods' => WP_REST_Server::CREATABLE, 
				'callback' => array( $this, 
						'update_shipping_address' 
				), 
				'args' => array( 
						'address' => array( 
								'validate_callback' => array( 
										$this, 
										'validate_address' 
								) 
						), 
						'payment_method' => array( 
								'required' => true 
						) 
				) 
		) );
	}

	/**
	 *
	 * @param WP_REST_Request $request        	
	 */
	public function add_to_cart($request) {
		// empty the cart since we only care about adding the product selected by the customer
		WC ()->cart->empty_cart ();
		$payment_method = $request->get_param ( 'payment_method' );
		/**
		 *
		 * @var WC_Braintree_Payment_Gateway $gateway
		 */
		$gateway = WC ()->payment_gateways ()->payment_gateways ()[ $payment_method ];
		$product_id = $request->get_param ( 'product_id' );
		$qty = $request->get_param ( 'qty' );
		$variation_id = $request->get_param ( 'variation_id' ) == null ? 0 : $request->get_param ( 'variation_id' );
		if (WC ()->cart->add_to_cart ( $product_id, $qty, $variation_id ) == false) {
			return rest_ensure_response ( array( 
					'success' => false, 
					'messages' => $this->get_error_messages () 
			) );
		} else {
			// add to cart was successful. Send a new X-WP-Nonce since it will be different now that a WC session exists.
			rest_get_server ()->send_header ( 'X-WP-Nonce', wp_create_nonce ( 'wp_rest' ) );
			
			return rest_ensure_response ( array( 
					'success' => true, 
					'data' => $gateway->add_to_cart_response ( array( 
							'total' => WC ()->cart->total 
					) ) 
			) );
		}
	}

	/**
	 * Add the selected shipping method to the WC session
	 *
	 * @param WP_REST_Request $request        	
	 */
	public function update_shipping_method($request) {
		$payment_method = $request->get_param ( 'payment_method' );
		/**
		 *
		 * @var WC_Braintree_Payment_Gateway $gateway
		 */
		$gateway = WC ()->payment_gateways ()->payment_gateways ()[ $payment_method ];
		// constant added for WC 3.0.0 backwards compatability
		wc_maybe_define_constant ( 'WOOCOMMERCE_CART', true );
		// using the chosen shipping methods, add it to the session so it can be used during order creation.
		$chosen_shipping_methods = WC ()->session->get ( 'chosen_shipping_methods', array() );
		$shipping_methods = $request->get_param ( 'shipping_methods' );
		foreach ( $shipping_methods as $i => $chosen_method ) {
			$chosen_shipping_methods[ $i ] = $chosen_method;
			/**
			 * Hack that keeps WCS shipping methods in sync with non-shippable products
			 */
			if ($gateway->cart_contains_trial_period_subscription ()) {
				foreach ( $chosen_shipping_methods as $n => $method ) {
					if (strlen ( $n ) > 1 && ( substr ( $n, - 1 ) == $i )) {
						$chosen_shipping_methods[ $n ] = $chosen_method;
					}
				}
			}
		}
		WC ()->session->set ( 'chosen_shipping_methods', $chosen_shipping_methods );
		WC ()->cart->calculate_totals ();
		return rest_ensure_response ( apply_filters ( 'wc_braintree_update_shipping_method_response', array( 
				'data' => $gateway->update_shipping_method_response ( array( 
						'chosen_shipping_methods' => $chosen_shipping_methods 
				) ) 
		), $payment_method, $request ) );
	}

	/**
	 * Update the customer's shipping address.
	 *
	 * @param WP_REST_Request $request        	
	 */
	public function update_shipping_address($request) {
		$payment_method = $request->get_param ( 'payment_method' );
		/**
		 *
		 * @var WC_Braintree_Payment_Gateway $gateway
		 */
		$gateway = WC ()->payment_gateways ()->payment_gateways ()[ $payment_method ];
		// update the shipping address so the list of shipping methods available can be sent back.
		$address = $request->get_param ( 'address' );
		// constant added for WC 3.0.0 backwards compatability
		wc_maybe_define_constant ( 'WOOCOMMERCE_CART', true );
		try {
			wc_braintree_update_customer_location ( $address );
			// re-calculate cart totals which includes shipping
			WC ()->cart->calculate_totals ();
			$packages = $gateway->get_shipping_packages ();
			// if the rates are empty then this shipping address is not supported.
			if (! $this->has_shipping_methods ( $packages )) {
				throw new Exception ( __ ( 'No shipping methods available for address.', 'woo-payment-gateway' ) );
			}
			return rest_ensure_response ( apply_filters ( 'wc_braintree_update_address_response', array( 
					'subtotal' => WC ()->cart->get_cart_subtotal (), 
					'total' => WC ()->cart->get_cart_total (), 
					'packages' => $packages, 
					'shipping_address' => WC ()->countries->get_formatted_address ( $address ), 
					'data' => $gateway->update_shipping_address_response ( array( 
							'chosen_shipping_methods' => WC ()->session->get ( 'chosen_shipping_methods', array() ) 
					) ) 
			), $payment_method, $request ) );
		} catch ( Exception $e ) {
			return apply_filters ( 'wc_braintree_update_address_error', new WP_Error ( 'shipping-address', $e->getMessage (), array( 
					'status' => 200 
			) ), $payment_method, $request );
		}
	}

	/**
	 *
	 * @param string $postcode        	
	 * @param WP_REST_Request $request        	
	 */
	public function validate_address($address, $request) {
		return apply_filters ( 'wc_braintree_cart_controller_validate_address', true, $address, $request );
	}

	/**
	 * Return true if the provided packages have shipping methods.
	 *
	 * @param array $packages        	
	 */
	private function has_shipping_methods($packages) {
		foreach ( $packages as $i => $package ) {
			if (! empty ( $package[ 'rates' ] )) {
				return true;
			}
		}
		return false;
	}
}