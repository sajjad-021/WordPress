<?php
if (! class_exists ( 'WC_Braintree_Payment_Gateway' )) {
	return;
}
/**
 *
 * @since 3.0.0
 * @package Braintree/Classes/Gateways
 *         
 */
class WC_Braintree_ApplePay_Payment_Gateway extends WC_Braintree_Payment_Gateway {

	/**
	 * Error that is set during validation of Apple Wallet address.
	 *
	 * @var WP_Error
	 */
	private $api_error;

	public function __construct() {
		$this->id = 'braintree_applepay';
		$this->deprecated_id = 'braintree_applepay_payments';
		$this->template = 'applepay.php';
		$this->token_type = 'ApplePay';
		parent::__construct ();
		$this->method_title = __ ( 'Braintree Apple Pay Gateway', 'woo-payment-gateway' );
		$this->tab_title = __ ( 'Apple Pay', 'woo-payment-gateway' );
		$this->method_description = __ ( 'Gateway that integrates Apple Pay with your Braintree account.', 'woo-payment-gateway' );
		$this->icon = braintree ()->assets_path () . 'img/applepay/apple_pay_mark.svg';
	}

	public function add_hooks() {
		parent::add_hooks ();
		add_filter ( 'woocommerce_payment_methods_list_item', array( 
				$this, 'payment_methods_list_item' 
		), 10, 2 );
		add_filter ( 'wc_braintree_cart_controller_validate_address', array( 
				$this, 'validate_address' 
		), 10, 3 );
		add_filter ( 'wc_braintree_update_address_error', array( 
				$this, 'update_address_error' 
		), 10, 3 );
	}

	public function set_supports() {
		parent::set_supports ();
		$this->supports[] = 'wc_braintree_cart_checkout';
		$this->supports[] = 'wc_braintree_banner_checkout';
		$this->supports[] = 'wc_braintree_product_checkout';
	}

	public function enqueue_admin_scripts() {
		wp_localize_script ( 'wc-braintree-admin-settings', 'wc_braintree_applepay_params', array( 
				'routes' => array( 
						'domain_association' => braintree ()->rest_api->rest_url () . 'applepay/domain-association' 
				) 
		) );
	}

	/**
	 *
	 * @return array array of line items in the Apple Wallet format
	 */
	public function get_line_items($encode = false) {
		global $wp;
		$line_items = array();
		$cart = WC ()->cart;
		if (is_product ()) {
			global $product;
			$line_items[] = array( 
					'label' => $product->get_name (), 
					'type' => 'final', 
					'amount' => $product->get_price () 
			);
		} elseif (wcs_braintree_active () && WC_Subscriptions_Change_Payment_Gateway::$is_request_to_change_payment) {
			$subscription = wcs_get_subscription ( absint ( $wp->query_vars[ 'order-pay' ] ) );
			$line_items[] = array( 
					'label' => __ ( 'Subscription', 'woo-payment-gateway' ), 
					'type' => 'final', 
					'amount' => $subscription->get_total () 
			);
		} elseif (wc_braintree_subscriptions_active () && wcs_braintree_is_change_payment_method_request ()) {
			$subscription = wc_get_order ( absint ( $wp->query_vars[ 'change-payment-method' ] ) );
			$line_items[] = array( 
					'label' => __ ( 'Subscription', 'woo-payment-gateway' ), 
					'type' => 'final', 
					'amount' => $subscription->get_total () 
			);
		} elseif (! empty ( $wp->query_vars[ 'order-pay' ] )) {
			$order = wc_get_order ( absint ( $wp->query_vars[ 'order-pay' ] ) );
			// line items
			foreach ( $order->get_items ( 'line_item' ) as $item ) {
				/**
				 *
				 * @var WC_Order_Item_Product $item
				 */
				$line_items[] = array( 
						'label' => $item->get_name () . ' x ' . $item->get_quantity (), 
						'type' => 'final', 
						'amount' => $item->get_total () 
				);
			}
			if (0 != $order->get_shipping_total ()) {
				$line_items[] = array( 
						'label' => __ ( 'Shipping', 'woocommerce' ), 
						'type' => 'final', 
						'amount' => $order->get_shipping_total () 
				);
			}
			if (( $fees = $order->get_fees () )) {
				foreach ( $fees as $fee ) {
					/**
					 *
					 * @var WC_Order_Item_Fee $fee
					 */
					$line_items[] = array( 
							'label' => $fee->get_name (), 
							'type' => 'final', 
							'amount' => $fee->get_total () 
					);
				}
			}
			if (0 != $order->get_total_tax ()) {
				$line_items[] = array( 
						'label' => __ ( 'Tax', 'woocommerce' ), 
						'type' => 'final', 
						'amount' => $order->get_total_tax () 
				);
			}
		} else {
			// products
			foreach ( $cart->get_cart () as $cart_item ) {
				/**
				 *
				 * @var WC_Product $product
				 */
				$product = $cart_item[ 'data' ];
				$line_items[] = array( 'type' => 'final', 
						'label' => $cart_item[ 'quantity' ] > 1 ? sprintf ( '%s x %s', esc_attr ( $product->get_name () ), $cart_item[ 'quantity' ] ) : esc_attr ( $product->get_name () ), 
						'amount' => round ( $product->get_price (), wc_get_rounding_precision () ) * $cart_item[ 'quantity' ] 
				);
			}
			// fees
			if (count ( $cart->get_fees () ) > 0) {
				$line_items[] = array( 'type' => 'final', 
						'label' => __ ( 'Fees' ), 
						'amount' => $cart->fee_total 
				);
			}
			// shipping
			if ($cart->needs_shipping ()) {
				$line_items[] = array( 'type' => 'final', 
						'label' => __ ( 'Shipping', 'woocommerce' ), 
						'amount' => $cart->shipping_total 
				);
			}
			if (0 != WC ()->cart->discount_cart) {
				$line_items[] = array( 
						'label' => __ ( 'Discounts', 'woocommerce' ), 
						'type' => 'final', 
						'amount' => - 1 * abs ( WC ()->cart->discount_cart ) 
				);
			}
			// taxes
			if (wc_tax_enabled ()) {
				$line_items[] = array( 'type' => 'final', 
						'label' => __ ( 'Tax', 'woocommerce' ), 
						'amount' => $cart->get_taxes_total () 
				);
			}
			if (wcs_braintree_active () && WC_Subscriptions_Cart::cart_contains_subscription ()) {
				$this->add_recurring_display_items ( $line_items );
			} elseif (wc_braintree_subscriptions_active () && wcs_braintree_cart_contains_subscription ()) {
				$this->add_recurring_display_items ( $line_items );
			}
		}
		return apply_filters ( 'wc_braintree_applepay_get_line_items', $encode ? htmlspecialchars ( wp_json_encode ( $line_items ) ) : $line_items );
	}

	protected function add_recurring_display_items(&$data) {
		if (0 == WC ()->cart->total) {
			$data = array( array_shift ( $data ) 
			);
		}
		$index = 1;
		foreach ( WC ()->cart->recurring_carts as $recurring_cart ) {
			/**
			 *
			 * @var WC_Cart $recurring_cart
			 */
			foreach ( $recurring_cart->get_cart () as $cart_item ) {
				if (( wcs_braintree_active () && WC_Subscriptions_Product::get_trial_length ( $cart_item[ 'data' ] ) > 0 ) || wc_braintree_subscriptions_active () && $cart_item[ 'data' ]->has_trial ()) {
					if ($recurring_cart->needs_shipping ()) {
						$data[] = array( 
								'label' => sprintf ( _n ( 'Recurring Shipping', 'Recurring Shipping: %s', $index, 'woo-payment-gateway' ), $index ), 
								'type' => 'final', 
								'amount' => $recurring_cart->shipping_total 
						);
					}
					if (wc_tax_enabled ()) {
						$data[] = array( 
								'label' => sprintf ( _n ( 'Recurring Tax', 'Recurring Tax: %s', $index, 'woo-payment-gateway' ), $index ), 
								'type' => 'final', 
								'amount' => $recurring_cart->get_taxes_total () 
						);
					}
					$discount_total = 0;
					foreach ( $recurring_cart->get_coupons () as $code => $coupon ) {
						/**
						 *
						 * @var WC_Coupon $coupon
						 */
						$discount_total += $recurring_cart->get_coupon_discount_amount ( $coupon->get_code (), false );
					}
					if ($discount_total) {
						$data[] = array( 
								'label' => sprintf ( _n ( 'Recurring Discounts', 'Recurring Discounts: %s', $index, 'woo-payment-gateway' ), $index ), 
								'type' => 'final', 
								'amount' => - 1 * $discount_total 
						);
					}
					$data[] = array( 
							'label' => sprintf ( _n ( 'Recurring Total', 'Recurring Total: %s', $index, 'woo-payment-gateway' ), $index ), 
							'type' => 'final', 
							'amount' => $recurring_cart->total 
					);
					$data[] = array( 
							'label' => sprintf ( _n ( 'Free Trial', 'Free Trial: %s', $index, 'woo-payment-gateway' ), $index ), 
							'type' => 'final', 
							'amount' => - 1 * $recurring_cart->total 
					);
				} else {
					$data[] = array( 
							'label' => sprintf ( _n ( 'Recurring Total', 'Recurring Total: %s', $index, 'woo-payment-gateway' ), $index ), 
							'type' => 'final', 
							'amount' => $recurring_cart->total 
					);
				}
				$index ++;
			}
		}
	}

	public function get_shipping_methods() {
		$methods = array();
		if (WC ()->cart->needs_shipping ()) {
			foreach ( $this->get_shipping_packages () as $i => $package ) {
				foreach ( $package[ 'rates' ] as $method ) {
					/**
					 *
					 * @var WC_Shipping_Rate $method
					 */
					$methods[] = array( 
							'label' => $method->get_label (), 
							'detail' => '', 
							'amount' => $method->cost, 
							'identifier' => sprintf ( '%s:%s', $i, esc_attr ( $method->id ) ) 
					);
				}
			}
		}
		return $methods;
	}

	public function localize_applepay_params() {
		$data = array_merge_recursive ( $this->get_localized_standard_params (), array( 
				'store_name' => $this->get_option ( 'store_name' ), 
				'button_html' => wc_braintree_get_template_html ( 'applepay-button.php', array( 
						'gateway' => $this, 
						'button' => $this->get_option ( 'button' ), 
						'type' => $this->get_option ( 'button_type_checkout' ) 
				) ), 
				'banner_enabled' => $this->banner_checkout_enabled (), 
				'messages' => array( 
						'errors' => array( 
								'invalid_administrativeArea' => __ ( 'Invalid state provided. Use uppercase state abbreviation. Example: California = CA' ) 
						) 
				), 
				'states' => WC ()->countries->get_states (), 
				'routes' => array( 
						'applepay_payment_method' => braintree ()->rest_api->applepay->rest_url () . 'payment-method' 
				) 
		) );
		return $data;
	}

	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see WC_Braintree_Payment_Gateway::enqueue_checkout_scripts()
	 */
	public function enqueue_checkout_scripts($scripts) {
		$scripts->enqueue_script ( 'applepay', $scripts->assets_url ( 'js/frontend/applepay.js' ), array( 
				$scripts->get_handle ( 'client-manager' ), 
				$scripts->get_handle ( 'data-collector-v3' ), 
				$scripts->get_handle ( 'applepay-v3' ) 
		) );
		$scripts->localize_script ( 'applepay', $this->localize_applepay_params () );
	}

	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see WC_Braintree_Payment_Gateway::enqueu_cart_scripts()
	 */
	public function enqueue_cart_scripts($scripts) {
		$scripts->enqueue_script ( 'applepay-cart', $scripts->assets_url ( 'js/frontend/applepay-cart.js' ), array( 
				$scripts->get_handle ( 'client-manager' ), 
				$scripts->get_handle ( 'data-collector-v3' ), 
				$scripts->get_handle ( 'applepay-v3' ) 
		) );
		$scripts->localize_script ( 'applepay-cart', $this->localize_applepay_params () );
	}

	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see WC_Braintree_Payment_Gateway::enqueue_product_scripts()
	 */
	public function enqueue_product_scripts($scripts) {
		$scripts->enqueue_script ( 'applepay-product', $scripts->assets_url ( 'js/frontend/applepay-product.js' ), array( 
				$scripts->get_handle ( 'client-manager' ), 
				$scripts->get_handle ( 'data-collector-v3' ), 
				$scripts->get_handle ( 'applepay-v3' ) 
		) );
		$scripts->localize_script ( 'applepay-product', $this->localize_applepay_params () );
	}

	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see WC_Braintree_Payment_Gateway::get_payment_method_from_transaction()
	 */
	public function get_payment_method_from_transaction($transaction) {
		return $transaction->applePayCardDetails;
	}

	/**
	 *
	 * @param array $data        	
	 */
	public function update_shipping_address_response($data) {
		$data[ 'shippingContactUpdate' ] = array( 
				'newLineItems' => $this->get_line_items (), 
				'newTotal' => array( 'type' => 'final', 
						'label' => $this->get_option ( 'store_name' ), 
						'amount' => $this->get_cart_total () 
				), 
				'newShippingMethods' => $this->get_shipping_methods () 
		);
		return $data;
	}

	public function get_cart_total() {
		$total = WC ()->cart->total;
		return $total;
	}

	/**
	 *
	 * @param bool $valid        	
	 * @param array $address        	
	 * @param WP_REST_Request $request        	
	 */
	public function validate_address($valid, $address, $request) {
		if ($request->get_param ( 'payment_method' ) === $this->id) {
			$states = WC ()->countries->get_states ( $address[ 'country' ] );
			$state = isset ( $address[ 'state' ] ) ? $address[ 'state' ] : null;
			if ($states && $state) {
				if (! isset ( $states[ $state ] )) {
					// customer needs to update their wallet's shipping state.
					$this->set_error_callback ( new WP_Error ( 'shippingContactInvalid', sprintf ( __ ( '%s is not a valid state. Use uppercase state abbreviation. California = CA', 'woo-payment-gateway' ), $state ), array( 
							'status' => 200, 
							'newTotal' => array( 
									'type' => 'final', 
									'label' => $this->get_option ( 'store_name' ), 
									'amount' => $this->get_cart_total () 
							), 
							'newShippingMethods' => $this->get_shipping_methods (), 
							'contactField' => 'administrativeArea' 
					) ) );
					return false;
				}
			}
		}
		return $valid;
	}

	/**
	 * Manipulate the request callback error so data is sent in a format Apple wallet can use.
	 *
	 * @param WP_Error $error        	
	 */
	private function set_error_callback($error) {
		$this->api_error = $error;
		add_action ( 'rest_request_before_callbacks', array( 
				$this, 'request_before_callbacks' 
		) );
	}

	/**
	 * Send back a resposne that contains the error message from the validation callback.
	 *
	 * @param WP_HTTP_Response $response        	
	 */
	public function request_before_callbacks($response) {
		return rest_ensure_response ( $this->api_error );
	}

	/**
	 *
	 * @param WP_Error $error        	
	 * @param string $payment_method        	
	 * @param WP_REST_Request $request        	
	 */
	public function update_address_error($error, $payment_method, $request) {
		if ($payment_method === $this->id) {
			if ($error->get_error_code () === 'shipping-address') {
				$error = new WP_Error ( 'addressUnserviceable', $error->get_error_message (), array( 
						'status' => 200, 
						'shippingContactUpdate' => array( 
								'newLineItems' => $this->get_line_items (), 
								'newTotal' => array( 
										'type' => 'final', 
										'label' => $this->get_option ( 'store_name' ), 
										'amount' => $this->get_cart_total () 
								), 
								'newShippingMethods' => $this->get_shipping_methods () 
						) 
				) );
			}
		}
		return $error;
	}

	/**
	 *
	 * @param array $data        	
	 */
	public function update_shipping_method_response($data) {
		$data[ 'shippingMethodUpdate' ] = array( 
				'newLineItems' => $this->get_line_items (), 
				'newTotal' => array( 'type' => 'final', 
						'label' => $this->get_option ( 'store_name' ), 
						'amount' => $this->get_cart_total () 
				) 
		);
		return $data;
	}

	/**
	 *
	 * @param array $item        	
	 * @param WC_Payment_Token $payment_token        	
	 */
	public function payment_methods_list_item($item, $payment_token) {
		if ('Braintree_ApplePay' !== $payment_token->get_type ()) {
			return $item;
		}
		$item[ 'method' ][ 'brand' ] = $payment_token->get_payment_method_title ( $this->get_option ( 'method_format' ) );
		$item[ 'expires' ] = $payment_token->get_expiry_month () . '/' . substr ( $payment_token->get_expiry_year (), - 2 );
		$item[ 'method_type' ] = $payment_token->get_method_type ();
		$item[ 'wc_braintree_method' ] = true;
		return $item;
	}

	/**
	 * Outputs the Apple Pay line items used to build a PaymentRequest
	 */
	public function output_line_items() {
		printf ( '<input type="hidden" id="%1$s" data-items="%2$s"/>', 'wc_braintree_applepay_line_items', $this->get_line_items ( true ) );
	}

	public function add_to_cart_response($data) {
		$data[ 'lineItems' ] = $this->get_line_items ();
		return $data;
	}
}