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
class WC_Braintree_PayPal_Payment_Gateway extends WC_Braintree_Payment_Gateway {

	public function __construct() {
		$this->id = 'braintree_paypal';
		$this->deprecated_id = 'braintree_paypal_payments';
		$this->token_type = 'PayPal';
		parent::__construct ();
		$this->template = 'paypal.php';
		$this->method_title = __ ( 'Braintree PayPal Gateway', 'woo-payment-gateway' );
		$this->tab_title = __ ( 'PayPal', 'woo-payment-gateway' );
		$this->method_description = __ ( 'Gateway that integrates your PayPal account with Braintree.', 'woo-payment-gateway' );
		$this->icon = braintree ()->assets_path () . 'img/paypal/paypal_long.svg';
	}

	public function add_hooks() {
		parent::add_hooks ();
		add_filter ( 'woocommerce_payment_methods_list_item', array( 
				$this, 'payment_methods_list_item' 
		), 10, 2 );
		add_filter ( 'wc_braintree_after_checkout_validation_notice', array( 
				$this, 'after_checkout_validation_notice' 
		), 10, 2 );
	}

	public function enqueue_admin_scripts() {
		wp_register_script ( 'wc-braintree-paypal-objects', braintree ()->assets_path () . 'js/frontend/paypal-checkout.js', array(), braintree ()->version );
		wp_enqueue_script ( 'wc-braintree-paypal-settings', braintree ()->assets_path () . 'js/admin/paypal-settings.js', array( 
				'wc-braintree-admin-settings', 
				'wc-braintree-paypal-objects' 
		), braintree ()->version, true );
	}

	public function set_supports() {
		parent::set_supports ();
		$this->supports[] = 'wc_braintree_cart_checkout';
		$this->supports[] = 'wc_braintree_banner_checkout';
		$this->supports[] = 'wc_braintree_product_checkout';
	}

	/**
	 *
	 * @param array $item        	
	 * @param WC_Payment_Token $payment_token        	
	 */
	public function payment_methods_list_item($item, $payment_token) {
		if ('Braintree_PayPal' !== $payment_token->get_type ()) {
			return $item;
		}
		$item[ 'method' ][ 'brand' ] = $payment_token->get_payment_method_title ( $this->get_option ( 'method_format' ) );
		$item[ 'expires' ] = __ ( 'N/A', 'woo-payment-gateway' );
		$item[ 'method_type' ] = $payment_token->get_method_type ();
		$item[ 'wc_braintree_method' ] = true;
		return $item;
	}

	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see WC_Braintree_Payment_Gateway::enqueue_checkout_scripts()
	 */
	public function enqueue_checkout_scripts($scripts) {
		$scripts->enqueue_script ( 'paypal', $scripts->assets_url ( 'js/frontend/paypal.js' ), array( 
				$scripts->get_handle ( 'client-manager' ), 
				$scripts->get_handle ( 'data-collector-v3' ), 
				$scripts->get_handle ( 'paypal-checkout-v3' ), 
				$scripts->get_handle ( 'paypal-checkout' ) 
		) );
		$scripts->localize_script ( 'paypal', $this->localize_paypal_params () );
	}

	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see WC_Braintree_Payment_Gateway::enqueue_cart_scripts()
	 */
	public function enqueue_cart_scripts($scripts) {
		if ($this->cart_checkout_enabled ()) {
			$scripts->enqueue_script ( 'paypal-cart', $scripts->assets_url ( 'js/frontend/paypal-cart.js' ), array( 
					$scripts->get_handle ( 'client-manager' ), 
					$scripts->get_handle ( 'paypal-checkout' ), 
					$scripts->get_handle ( 'data-collector-v3' ), 
					$scripts->get_handle ( 'paypal-checkout-v3' ) 
			) );
			$scripts->localize_script ( 'paypal-cart', $this->localize_paypal_params () );
		}
	}

	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see WC_Braintree_Payment_Gateway::enqueue_product_scripts()
	 */
	public function enqueue_product_scripts($scripts) {
		if ($this->product_checkout_enabled ()) {
			$scripts->enqueue_script ( 'paypal-product', $scripts->assets_url ( 'js/frontend/paypal-product.js' ), array( 
					$scripts->get_handle ( 'client-manager' ), 
					$scripts->get_handle ( 'paypal-checkout' ), 
					$scripts->get_handle ( 'data-collector-v3' ), 
					$scripts->get_handle ( 'paypal-checkout-v3' ) 
			) );
			$scripts->localize_script ( 'paypal-product', $this->localize_paypal_params () );
		}
	}

	public function localize_paypal_params() {
		if ($this->is_available ()) {
			$needs_shipping = $this->needs_shipping ();
			$data = array_merge_recursive ( $this->get_localized_standard_params (), array( 
					'locale' => $this->get_user_locale (), 
					'button_style' => $this->get_button_options (), 
					'card_icons' => $this->is_active ( 'smartbutton_cards' ), 
					'tokenize_response' => WC ()->session->get ( $this->id . '_tokenized_response' ), 
					'banner_enabled' => $this->banner_checkout_enabled (), 
					'locales' => array_values ( $this->get_supported_locales () ), 
					'options' => array( 
							'flow' => $this->get_paypal_flow (), 
							'intent' => $this->get_option ( 'charge_type' ), 
							'currency' => $this->get_checkout_currency (), 
							'locale' => $this->get_user_locale (), 
							'displayName' => $this->get_option ( 'display_name' ), 
							'enableShippingAddress' => $needs_shipping, 
							'shippingAddressEditable' => $needs_shipping, 
							'offerCredit' => $this->is_paypal_credit_active () 
					), 
					'messages' => array( 
							'invalid_locale' => sprintf ( __ ( 'PayPal does not support locale %s. You can change the locale in your Wordpress settings', 'woo-payment-gateway' ), $this->get_option ( 'locale' ) ) 
					), 
					'shipping_window_url' => add_query_arg ( 'wc-braintree', 'shipping-methods', get_site_url () ) 
			) );
		}
		$data[ 'locale' ] = $this->get_user_locale ();
		return apply_filters ( 'wc_braintree_localized_paypal_params', $data );
	}

	public function get_button_options() {
		return array( 
				'size' => $this->get_option ( 'smartbutton_size' ), 
				'color' => $this->get_option ( 'smartbutton_color' ), 
				'shape' => $this->get_option ( 'smartbutton_shape' ), 
				'layout' => $this->get_option ( 'smartbutton_layout' ) 
		);
	}

	public function is_paypal_credit_active() {
		return $this->is_active ( 'credit_enabled' ) && wc_braintree_evaluate_condition ( $this->get_option ( 'credit_conditions' ) );
	}

	/**
	 * Returns either "checkout" or "vault" depending on conditions such as if the cart contains subscriptions etc.
	 *
	 * @return string
	 */
	public function get_paypal_flow() {
		if (is_add_payment_method_page ()) {
			return 'vault';
		}
		if (is_checkout () || is_cart ()) {
			if (wcs_braintree_active () && ( WC_Subscriptions_Cart::cart_contains_subscription () || WC_Subscriptions_Change_Payment_Gateway::$is_request_to_change_payment )) {
				return 'vault';
			}
			if (wc_braintree_subscriptions_active () && wcs_braintree_cart_contains_subscription ()) {
				return 'vault';
			}
			return 'checkout';
		}
		if ($this->is_change_payment_request ()) {
			return 'vault';
		}
		if (wcs_braintree_active () && WC_Subscriptions_Change_Payment_Gateway::$is_request_to_change_payment) {
			return 'vault';
		}
		if (is_product ()) {
			global $product;
			if (wcs_braintree_active () && is_a ( $product, 'WC_Product' ) && WC_Subscriptions_Product::is_subscription ( $product )) {
				return 'vault';
			}
			if (wc_braintree_subscriptions_active () && is_a ( $product, 'WC_Product' ) && wcs_braintree_product_is_subscription ( $product )) {
				return 'vault';
			}
			return 'checkout';
		}
		return 'checkout';
	}

	public function remove_session_checkout_vars() {
		unset ( WC ()->session->{$this->id . '_tokenized_response'} );
	}

	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see WC_Braintree_Payment_Gateway::get_payment_method_from_transaction()
	 */
	public function get_payment_method_from_transaction($transaction) {
		return $transaction->paypalDetails;
	}

	/**
	 * Method that adds to the validation notice when the selected payment method is PayPal.
	 *
	 * @param string $notice        	
	 * @param array $data        	
	 */
	public function after_checkout_validation_notice($notice, $data) {
		if ($this->id === $data[ 'payment_method' ]) {
			if (current_user_can ( 'administrator' )) {
				$notice .= sprintf ( ' ' . __ ( 'Admin Notice: For virtual products, PayPal must send back the billing address. Click %shere%s to read how to enable this functionality.', 'woo-payment-gateway' ), '<a target="_blank" href="https://docs.paymentplugins.com/wc-braintree/config/#/braintree_paypal?id=enable-billing-address">', '</a>' );
			}
		}
		return $notice;
	}

	/**
	 * Decorate the response with data specific to PayPal.
	 *
	 * @param array $data        	
	 */
	public function update_shipping_method_response($data) {
		$data[ 'cart_totals' ] = wc_braintree_get_template_html ( 'paypal-cart-totals.php', array( 
				'gateway' => $this 
		) );
		return $data;
	}

	public function add_line_items(&$args, $order, &$items = array()) {
		// PayPal fails validation when line items are used under scenarios when coupons are used.
		// Better to just not add line items for PayPal.
	}

	/**
	 * Returns an array of locales supported by the PayPal smartbuttons.
	 *
	 * @since 3.0.2
	 */
	public function get_supported_locales() {
		return apply_filters ( 'wc_braintree_paypal_supported_locales', array( 
				'en_US' => 'en_US', 'en_AU' => 'en_AU', 
				'en_GB' => 'en_GB', 'fr_CA' => 'fr_CA', 
				'es_ES' => 'es_ES', 'it_IT' => 'it_IT', 
				'fr_FR' => 'fr_FR', 'de_DE' => 'de_DE', 
				'pt_BR' => 'pt_BR', 'zh_CN' => 'zh_CN', 
				'da_DK' => 'da_DK', 'zh_HK' => 'zh_HK', 
				'id_ID' => 'id_ID', 'he_IL' => 'he_IL', 
				'ja_JP' => 'ja_JP', 'ko_KR' => 'ko_KR', 
				'nl_NL' => 'nl_NL', 'no_NO' => 'no_NO', 
				'pl_PL' => 'pl_PL', 'pt_PT' => 'pt_PT', 
				'ru_RU' => 'ru_RU', 'sv_SE' => 'sv_SE', 
				'th_TH' => 'th_TH', 'zh_TW' => 'zh_TW' 
		) );
	}

	/**
	 * Determine the user's locale based on their browser settings.
	 *
	 * @since 3.0.4
	 */
	public function get_user_locale() {
		$langs = isset ( $_SERVER[ 'HTTP_ACCEPT_LANGUAGE' ] ) ? $_SERVER[ 'HTTP_ACCEPT_LANGUAGE' ] : '';
		preg_match_all ( '/([\w]{2}[\-\_][\w]{2})/', $langs, $matches );
		$user_locale = $this->get_option ( 'locale' );
		if (isset ( $matches[ 0 ] )) {
			for($i = 0; $i < count ( $matches[ 0 ] ); $i ++) {
				$locale = str_replace ( '-', '_', $matches[ 0 ][ $i ] );
				if (in_array ( $locale, $this->get_supported_locales () )) {
					$user_locale = $locale;
					break;
				}
			}
		}
		return $user_locale;
	}
}