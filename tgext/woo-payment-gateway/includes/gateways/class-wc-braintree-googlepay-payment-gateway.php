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
class WC_Braintree_GooglePay_Payment_Gateway extends WC_Braintree_Payment_Gateway {

	public $shipping_method_id;

	public function __construct() {
		$this->id = 'braintree_googlepay';
		$this->deprecated_id = 'braintree_googlepay_gateway';
		$this->token_type = 'GooglePay';
		$this->template = 'googlepay.php';
		$this->method_title = __ ( 'Braintree Google Pay Gateway', 'woo-payment-gateway' );
		$this->tab_title = __ ( 'Google Pay', 'woo-payment-gateway' );
		$this->method_description = __ ( 'Gateway that integrates Google Pay with your Braintree account.', 'woo-payment-gateway' );
		parent::__construct ();
		$this->icon = braintree ()->assets_path () . 'img/googlepay/' . $this->get_option ( 'icon' ) . '.svg';
	}

	public function add_hooks() {
		add_filter ( 'woocommerce_payment_methods_list_item', array( 
				$this, 'payment_methods_list_item' 
		), 10, 2 );
		parent::add_hooks ();
	}

	public function enqueue_admin_scripts() {
		wp_register_script ( 'wc-braintree-googlepay', 'https://pay.google.com/gp/p/js/pay.js', array(), braintree ()->version );
		
		wp_enqueue_script ( 'wc-braintree-googlepay-settings', braintree ()->assets_path () . 'js/admin/googlepay-settings.js', array( 
				'wc-braintree-admin-settings', 
				'wc-braintree-googlepay' 
		), braintree ()->version, true );
	}

	public function set_supports() {
		parent::set_supports ();
		$this->supports[] = 'wc_braintree_cart_checkout';
		$this->supports[] = 'wc_braintree_product_checkout';
		$this->supports[] = 'wc_braintree_banner_checkout';
	}

	/**
	 *
	 * @param array $item        	
	 * @param WC_Payment_Token $payment_token        	
	 */
	public function payment_methods_list_item($item, $payment_token) {
		if ('Braintree_GooglePay' !== $payment_token->get_type ()) {
			return $item;
		}
		$card_type = $payment_token->get_card_type ();
		$item[ 'method' ][ 'last4' ] = $payment_token->get_last4 ();
		$item[ 'method' ][ 'brand' ] = ( ! empty ( $card_type ) ? ucfirst ( $card_type ) : esc_html__ ( 'Credit card', 'woocommerce' ) );
		$item[ 'expires' ] = $payment_token->get_expiry_month () . '/' . substr ( $payment_token->get_expiry_year (), - 2 );
		$item[ 'method_type' ] = $payment_token->get_method_type ();
		$item[ 'wc_braintree_method' ] = true;
		return $item;
	}

	public function output_checkout_fields() {
		if ($this->is_available ()) {
			printf ( '<input type="hidden" id="wc_braintree_googlepay_environment" value="%1$s"/>', wc_braintree_production_active () ? 'PRODUCTION' : 'TEST' );
			printf ( '<input type="hidden" id="wc_braintree_googlepay_merchant_id" value="%1$s"/>', wc_braintree_production_active () ? $this->get_option ( 'merchant_id' ) : '' );
			printf ( '<input type="hidden" data-items="%s" id="wc_braintree_googlepay_display_items"/>', $this->get_display_items ( true ) );
		}
	}

	public function localize_googlepay_params() {
		$data = array_merge_recursive ( $this->get_localized_standard_params (), array( 
				'form' => array( 'submit' => false 
				), 
				'button_options' => array( 
						'buttonColor' => $this->get_option ( 'button_color' ), 
						'buttonType' => $this->get_option ( 'button_type' ) 
				), 
				'banner_enabled' => $this->banner_checkout_enabled (), 
				'price_label' => __ ( 'Total', 'woocommerce' ), 
				'dynamic_price' => $this->is_active ( 'dynamic_price' ), 
				'routes' => array( 
						'shipping_data' => braintree ()->rest_api->googlepay->rest_url () . 'shipping-address' 
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
		$scripts->enqueue_script ( 'googlepay', $scripts->assets_url ( 'js/frontend/googlepay.js' ), array( 
				$scripts->get_handle ( 'client-manager' ), 
				$scripts->get_handle ( 'data-collector-v3' ), 
				$scripts->get_handle ( 'googlepay-v3' ), 
				$scripts->get_handle ( 'googlepay-pay' ) 
		) );
		$scripts->localize_script ( 'googlepay', $this->localize_googlepay_params () );
	}

	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see WC_Braintree_Payment_Gateway::enqueue_cart_scripts()
	 */
	public function enqueue_cart_scripts($scripts) {
		$scripts->enqueue_script ( 'googlepay-cart', $scripts->assets_url ( 'js/frontend/googlepay-cart.js' ), array( 
				$scripts->get_handle ( 'client-manager' ), 
				$scripts->get_handle ( 'data-collector-v3' ), 
				$scripts->get_handle ( 'googlepay-v3' ), 
				$scripts->get_handle ( 'googlepay-pay' ) 
		) );
		$scripts->localize_script ( 'googlepay-cart', $this->localize_googlepay_params () );
	}

	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see WC_Braintree_Payment_Gateway::enqueue_product_scripts()
	 */
	public function enqueue_product_scripts($scripts) {
		$scripts->enqueue_script ( 'googlepay-product', $scripts->assets_url ( 'js/frontend/googlepay-product.js' ), array( 
				$scripts->get_handle ( 'client-manager' ), 
				$scripts->get_handle ( 'data-collector-v3' ), 
				$scripts->get_handle ( 'googlepay-v3' ), 
				$scripts->get_handle ( 'googlepay-pay' ) 
		) );
		$scripts->localize_script ( 'googlepay-product', $this->localize_googlepay_params () );
	}

	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see WC_Braintree_Payment_Gateway::get_payment_method_from_transaction()
	 */
	public function get_payment_method_from_transaction($transaction) {
		return $transaction->androidPayCardDetails;
	}

	/**
	 * Return an array of items formatted for the Google Pay sheet.
	 *
	 * @return array
	 */
	public function get_display_items($encode = false) {
		$data = array();
		global $wp;
		if (is_product ()) {
			global $product;
			$data[] = array( 
					'label' => $product->get_name (), 
					'type' => 'LINE_ITEM', 
					'price' => strval ( $product->get_price () ), 
					'status' => 'FINAL' 
			);
		} elseif (wcs_braintree_active () && WC_Subscriptions_Change_Payment_Gateway::$is_request_to_change_payment) {
			$subscription = wcs_get_subscription ( absint ( $wp->query_vars[ 'order-pay' ] ) );
			$data[] = array( 
					'label' => __ ( 'Subscription', 'woo-payment-gateway' ), 
					'type' => 'SUBTOTAL', 
					'price' => strval ( $subscription->get_total () ), 
					'status' => 'FINAL' 
			);
		} elseif (wc_braintree_subscriptions_active () && wcs_braintree_is_change_payment_method_request ()) {
			$subscription = wc_get_order ( absint ( $wp->query_vars[ 'change-payment-method' ] ) );
			$data[] = array( 
					'label' => __ ( 'Subscription', 'woo-payment-gateway' ), 
					'type' => 'SUBTOTAL', 
					'price' => strval ( $subscription->get_total () ), 
					'status' => 'FINAL' 
			);
		} elseif (! empty ( $wp->query_vars[ 'order-pay' ] )) {
			$order = wc_get_order ( absint ( $wp->query_vars[ 'order-pay' ] ) );
			// line items
			foreach ( $order->get_items ( 'line_item' ) as $item ) {
				/**
				 *
				 * @var WC_Order_Item_Product $item
				 */
				$data[] = array( 
						'label' => $item->get_name () . ' x ' . $item->get_quantity (), 
						'type' => 'LINE_ITEM', 
						'price' => strval ( $item->get_total () ), 
						'status' => 'FINAL' 
				);
			}
			if (0 != $order->get_shipping_total ()) {
				$data[] = array( 
						'label' => __ ( 'Shipping', 'woocommerce' ), 
						'type' => 'LINE_ITEM', 
						'price' => strval ( $order->get_shipping_total () ), 
						'status' => 'FINAL' 
				);
			}
			if (( $fees = $order->get_fees () )) {
				foreach ( $fees as $fee ) {
					/**
					 *
					 * @var WC_Order_Item_Fee $fee
					 */
					$data[] = array( 
							'label' => $fee->get_name (), 
							'type' => 'LINE_ITEM', 
							'price' => strval ( $fee->get_total () ), 
							'status' => 'FINAL' 
					);
				}
			}
			if (0 != $order->get_total_tax ()) {
				$data[] = array( 
						'label' => __ ( 'Tax', 'woocommerce' ), 
						'type' => 'LINE_ITEM', 
						'price' => strval ( $order->get_total_tax () ), 
						'status' => 'FINAL' 
				);
			}
		} else {
			// products
			foreach ( WC ()->cart->get_cart () as $key => $cart_item ) {
				$data[] = array( 
						'label' => $cart_item[ 'data' ]->get_name () . ' x ' . $cart_item[ 'quantity' ], 
						'type' => 'LINE_ITEM', 
						'price' => strval ( $cart_item[ 'data' ]->get_price () * $cart_item[ 'quantity' ] ), 
						'status' => 'FINAL' 
				);
			}
			if (WC ()->cart->needs_shipping () && WC ()->cart->show_shipping ()) {
				$data[] = array( 
						'label' => __ ( 'Shipping', 'woocommerce' ), 
						'type' => 'SUBTOTAL', 
						'price' => strval ( WC ()->cart->shipping_total ), 
						'status' => 'FINAL' 
				);
			}
			
			// fees
			foreach ( WC ()->cart->get_fees () as $fee ) {
				$data[] = array( 
						'label' => esc_html ( $fee->name ), 
						'type' => 'SUBTOTAL', 
						'price' => strval ( $fee->total ), 
						'status' => 'FINAL' 
				);
			}
			if (0 != WC ()->cart->discount_cart) {
				$data[] = array( 
						'label' => __ ( 'Discounts', 'woocommerce' ), 
						'type' => 'SUBTOTAL', 
						'price' => strval ( - 1 * abs ( WC ()->cart->discount_cart ) ) 
				);
			}
			
			// show tax separately
			if (wc_tax_enabled ()) {
				$data[] = array( 
						'label' => __ ( 'Tax', 'woocommerce' ), 
						'type' => 'SUBTOTAL', 
						'price' => strval ( WC ()->cart->get_taxes_total () ) 
				);
			}
			if (wcs_braintree_active () && WC_Subscriptions_Cart::cart_contains_subscription ()) {
				$this->add_recurring_display_items ( $data );
			} elseif (wc_braintree_subscriptions_active () && wcs_braintree_cart_contains_subscription ()) {
				$this->add_recurring_display_items ( $data );
			}
		}
		return apply_filters ( 'wc_braintree_googlepay_get_line_items', $encode ? htmlspecialchars ( wp_json_encode ( $data ) ) : $data );
	}

	protected function add_recurring_display_items(&$data) {
		if (0 == WC ()->cart->total) {
			// reset data so that only recurring totals are shown. Only keep product line item
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
								'type' => 'SUBTOTAL', 
								'price' => strval ( $recurring_cart->shipping_total ), 
								'status' => 'FINAL' 
						);
					}
					if (wc_tax_enabled ()) {
						$data[] = array( 
								'label' => sprintf ( _n ( 'Recurring Tax', 'Recurring Tax: %s', $index, 'woo-payment-gateway' ), $index ), 
								'type' => 'SUBTOTAL', 
								'price' => strval ( $recurring_cart->get_taxes_total () ) 
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
								'type' => 'SUBTOTAL', 
								'price' => strval ( - 1 * $discount_total ) 
						);
					}
					$data[] = array( 
							'label' => sprintf ( _n ( 'Recurring Total', 'Recurring Total: %s', $index, 'woo-payment-gateway' ), $index ), 
							'type' => 'SUBTOTAL', 
							'price' => strval ( $recurring_cart->total ) 
					);
					$data[] = array( 
							'label' => sprintf ( _n ( 'Free Trial', 'Free Trial: %s', $index, 'woo-payment-gateway' ), $index ), 
							'type' => 'SUBTOTAL', 
							'price' => strval ( - 1 * $recurring_cart->total ) 
					);
				} else {
					$data[] = array( 
							'label' => sprintf ( _n ( 'Recurring Total', 'Recurring Total: %s', $index, 'woo-payment-gateway' ), $index ), 
							'type' => 'SUBTOTAL', 
							'price' => strval ( $recurring_cart->total ) 
					);
				}
				$index ++;
			}
		}
	}

	/**
	 *
	 * @param array $shipping_methods        	
	 */
	public function get_default_shipping_method($shipping_methods = array()) {
		$chosen_shipping_methods = WC ()->session->get ( 'chosen_shipping_methods', array() );
		if (empty ( $shipping_methods )) {
			reset ( $chosen_shipping_methods );
			$key = key ( $chosen_shipping_methods );
			return sprintf ( '%s:%s', $key, $chosen_shipping_methods[ $key ] );
		} else {
			if (is_null ( $this->shipping_method_id )) {
				$this->shipping_method_id = sprintf ( '%s:%s', key ( $chosen_shipping_methods ), current ( $chosen_shipping_methods ) );
				return $this->shipping_method_id;
			} else {
				foreach ( $shipping_methods as $method ) {
					if ($method[ 'id' ] === $this->shipping_method_id) {
						return $this->shipping_method_id;
					}
				}
			}
			reset ( $shipping_methods );
			return $shipping_methods[ key ( $shipping_methods ) ][ 'id' ];
		}
	}

	/**
	 * Return shipping options and the selected shipping method in the payment sheet format.
	 *
	 * @return array
	 */
	public function get_shipping_options() {
		$data = array();
		$chosen_shipping = WC ()->session->get ( 'chosen_shipping_methods', array() );
		$data[ 'shippingOptions' ] = array();
		foreach ( $this->get_shipping_packages () as $i => $package ) {
			foreach ( $package[ 'rates' ] as $id => $rate ) {
				/**
				 *
				 * @var WC_Shipping_Rate $rate
				 */
				$data[ 'shippingOptions' ][] = array( 
						'id' => sprintf ( '%s:%s', $i, esc_attr ( $rate->id ) ), 
						'label' => $this->get_shipping_method_label ( $rate ), 
						'description' => '' 
				);
			}
		}
		$default_shipping_method = $this->get_default_shipping_method ( $data[ 'shippingOptions' ] );
		if ($default_shipping_method) {
			$data[ 'defaultSelectedOptionId' ] = $default_shipping_method;
		}
		return $data;
	}

	/**
	 * Return a formatted shipping method label.
	 * <strong>Example</strong>&nbsp;5 Day shipping: 5 USD
	 *
	 * @param WC_Shipping_Rate $rate        	
	 * @return
	 *
	 */
	public function get_shipping_method_label($rate) {
		$total = $rate->cost;
		return sprintf ( '%s: %s %s', $rate->get_label (), $total, get_woocommerce_currency () );
	}

	public function add_to_cart_response($data) {
		$data[ 'displayItems' ] = $this->get_display_items ();
		$data[ 'shippingOptions' ] = $this->get_shipping_options ();
		return $data;
	}
}