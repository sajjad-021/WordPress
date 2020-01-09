<?php
/**
 * @since 3.0.0
 * @author Payment Plugins
 * @package Braintree/Classes
 */
class WC_Braintree_Field_Manager {

	/**
	 *
	 * @since 3.1.1
	 * @var string
	 */
	private static $_output_checkout_fields = false;

	public static function init() {
		add_action ( 'woocommerce_review_order_after_order_total', array( 
				__CLASS__, 'output_checkout_fields' 
		) );
		add_action ( 'woocommerce_after_template_part', array( 
				__CLASS__, 'after_template_part' 
		), 10, 2 );
		add_action ( 'woocommerce_checkout_update_order_review', array( 
				__CLASS__, 'checkout_update_order_review' 
		) );
		add_action ( 'before_woocommerce_pay', array( 
				__CLASS__, 'change_payment_request' 
		) );
		add_action ( 'wcs_braintree_before_change_payment_method_gateways', array( 
				__CLASS__, 'change_braintree_payment_method' 
		) );
		add_action ( 'wcs_braintree_subscription_pay_form', array( 
				__CLASS__, 'output_subscription_pay_fields' 
		) );
		add_action ( 'wc_braintree_cart_form_fields', array( 
				__CLASS__, 'output_cart_fields' 
		) );
		add_action ( 'woocommerce_before_add_to_cart_button', array( 
				__CLASS__, 'output_product_checkout_fields' 
		) );
		add_action ( 'woocommerce_pay_order_after_submit', array( 
				__CLASS__, 'pay_order_fields' 
		) );
	}

	/**
	 * Added because some themes aren't 100% compatible with WC and they skip template hooks.
	 * This was added to ensure necessary fields are output to pages when required actions
	 * aren't triggered.
	 *
	 * @since 3.1.1
	 * @param string $template_name        	
	 * @param string $template_path        	
	 */
	public static function after_template_part($template_name, $template_path) {
		if (empty ( $template_path ) && $template_name == 'checkout/review-order.php' && is_checkout () && ! self::$_output_checkout_fields) {
			self::output_checkout_fields ();
		}
	}

	public static function output_checkout_fields() {
		printf ( '<input type="hidden" id="wc_braintree_cart_total" value="%1$s"/>', self::get_cart_total () );
		printf ( '<input type="hidden" id="wc_braintree_cart_currency" value="%1$s"/>', get_woocommerce_currency () );
		printf ( '<input type="hidden" id="wc_braintree_merchant_account" value="%1$s"/>', wc_braintree_get_merchant_account () );
		
		self::recurring_cart_total ( WC ()->cart->total );
		
		do_action ( 'wc_braintree_output_checkout_fields' );
		
		self::$_output_checkout_fields = true;
	}

	public static function output_cart_fields() {
		printf ( '<input type="hidden" id="wc_braintree_cart_total" value="%1$s"/>', self::get_cart_total () );
		printf ( '<input type="hidden" id="wc_braintree_cart_currency" value="%1$s"/>', get_woocommerce_currency () );
		printf ( '<input type="hidden" id="wc_braintree_merchant_account" value="%1$s"/>', wc_braintree_get_merchant_account () );
		if (WC ()->cart->needs_shipping ()) {
			$fields = WC ()->checkout ()->get_checkout_fields ( 'shipping' );
			foreach ( $fields as $key => $field ) {
				printf ( '<input type="hidden" id="%1$s" name="%1$s" value="%2$s" data-required="%3$s"/>', $key, WC ()->checkout ()->get_value ( $key ), ! empty ( $field[ 'required' ] ) );
			}
			self::output_needs_shipping ( true );
		} else {
			self::output_needs_shipping ( false );
		}
		$fields = WC ()->checkout ()->get_checkout_fields ( 'billing' );
		foreach ( $fields as $key => $field ) {
			printf ( '<input type="hidden" id="%1$s" name="%1$s" value="%2$s" data-required="%3$s"/>', $key, WC ()->checkout ()->get_value ( $key ), ! empty ( $field[ 'required' ] ) );
		}
		printf ( '<input type="hidden" id="%1$s" name="%1$s" value=""/>', 'payment_method' );
	}

	public static function checkout_update_order_review() {
		if (is_ajax ()) {
			// initialze payment gateways.
			WC ()->payment_gateways ();
		}
	}

	public static function change_payment_request() {
		if (wcs_braintree_active () && WC_Subscriptions_Change_Payment_Gateway::$is_request_to_change_payment) {
			$subscription = wcs_get_subscription ( absint ( $_GET[ 'change_payment_method' ] ) );
			printf ( '<input type="hidden" id="wc_braintree_cart_total" value="%1$s"/>', $subscription->get_total () );
			printf ( '<input type="hidden" id="wc_braintree_cart_currency" value="%1$s"/>', $subscription->get_currency () );
			do_action ( 'wc_braintree_output_change_payment_method_fields' );
		}
	}

	/**
	 *
	 * @param WC_Braintree_Subscription $subscription        	
	 */
	public static function change_braintree_payment_method($subscription) {
		printf ( '<input type="hidden" id="wc_braintree_cart_total" value="%1$s"/>', $subscription->get_total () );
		printf ( '<input type="hidden" id="wc_braintree_cart_currency" value="%1$s"/>', $subscription->get_currency () );
		printf ( '<input type="hidden" id="wc_braintree_merchant_account" value="%1$s"/>', wc_braintree_get_merchant_account () );
	}

	/**
	 *
	 * @param WC_Order $order        	
	 */
	public static function output_subscription_pay_fields($order) {
		printf ( '<input type="hidden" id="wc_braintree_cart_total" value="%1$s"/>', $order->get_total () );
		printf ( '<input type="hidden" id="wc_braintree_cart_currency" value="%1$s"/>', $order->get_currency () );
	}

	public static function output_product_checkout_fields() {
		global $product;
		printf ( '<input type="hidden" id="wc_braintree_cart_total" value="%1$s"/>', WC ()->cart->total );
		printf ( '<input type="hidden" id="wc_braintree_merchant_account" value="%1$s"/>', wc_braintree_get_merchant_account () );
		printf ( '<input type="hidden" id="wc_braintree_cart_currency" value="%1$s"/>', get_woocommerce_currency () );
		printf ( '<input type="hidden" id="wc_braintree_product_price" value="%1$s"/>', $product->get_price () );
		printf ( '<input type="hidden" id="wc_braintree_product_data" data-product="%1$s"/>', htmlspecialchars ( wp_json_encode ( array( 
				'price' => $product->get_price (), 
				'title' => $product->get_title (), 
				'post_id' => $product->get_id (), 
				'needs_shipping' => $product->needs_shipping () 
		) ) ) );
		if ($product->needs_shipping ()) {
			$fields = WC ()->checkout ()->get_checkout_fields ( 'shipping' );
			foreach ( $fields as $key => $field ) {
				printf ( '<input type="hidden" id="%1$s" name="%1$s" value="%2$s" data-required="%3$s"/>', $key, WC ()->checkout ()->get_value ( $key ), ! empty ( $field[ 'required' ] ) );
			}
		}
		$fields = WC ()->checkout ()->get_checkout_fields ( 'billing' );
		foreach ( $fields as $key => $field ) {
			printf ( '<input type="hidden" id="%1$s" name="%1$s" value="%2$s" data-required="%3$s"/>', $key, WC ()->checkout ()->get_value ( $key ), ! empty ( $field[ 'required' ] ) );
		}
		printf ( '<input type="hidden" id="%1$s" name="%1$s" value=""/>', 'payment_method' );
		do_action ( 'wc_braintree_output_product_checkout_fields' );
	}

	public static function pay_order_fields() {
		global $wp;
		$order = wc_get_order ( absint ( $wp->query_vars[ 'order-pay' ] ) );
		
		printf ( '<input type="hidden" id="wc_braintree_cart_total" value="%1$s"/>', $order->get_total () );
		printf ( '<input type="hidden" id="wc_braintree_cart_currency" value="%1$s"/>', $order->get_currency () );
		printf ( '<input type="hidden" id="wc_braintree_merchant_account" value="%1$s"/>', wc_braintree_get_merchant_account ( $order->get_currency () ) );
		
		self::recurring_cart_total ( $order->get_total () );
	}

	public static function output_needs_shipping($needs_shipping) {
		printf ( '<input type="hidden" id="wc_braintree_needs_shipping" data-value="%s"/>', $needs_shipping );
	}

	public static function get_cart_total() {
		$total = WC ()->cart->total;
		return $total;
	}

	/**
	 * 3DS doesn't like $0.00 totals which can happen if only product in cart is subscription product with trial.
	 *
	 * @since 3.0.6
	 * @param float $total        	
	 *
	 */
	public static function recurring_cart_total($total) {
		if (0 == $total) {
			if (( wcs_braintree_active () && WC_Subscriptions_Cart::cart_contains_subscription () ) || ( wc_braintree_subscriptions_active () && wcs_braintree_cart_contains_subscription () )) {
				$total = call_user_func ( function ($carts) {
					$sum = 0;
					foreach ( $carts as $cart ) {
						$sum += $cart->total;
					}
					printf ( '<input type="hidden" id="wc_braintree_recurring_cart_total" value="%1$s"/>', $sum );
				}, WC ()->cart->recurring_carts );
			}
		}
	}
}
if (! is_admin ()) {
	WC_Braintree_Field_Manager::init ();
}