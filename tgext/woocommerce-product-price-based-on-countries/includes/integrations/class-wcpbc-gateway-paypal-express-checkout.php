<?php
/**
 * Fix currency issues with PayPal Express Checkout by WooCommerce.
 *
 * @since 1.8.16
 * @package WCPBC/Integrations
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WCPBC_Gateway_Paypal_Express_Checkout' ) ) :

	/**
	 * WCPBC_Gateway_Paypal_Express_Checkout class.
	 */
	class WCPBC_Gateway_Paypal_Express_Checkout {
		/**
		 * Hook actions and filters
		 */
		public static function init() {
			add_action( 'wc_price_based_country_before_frontend_init', array( __CLASS__, 'maybe_return_from_paypal' ), 0 );
			add_filter( 'woocommerce_paypal_express_checkout_request_body', array( __CLASS__, 'paypal_express_checkout_request_body' ) );
		}

		/**
		 * Return form PayPal Express. Stores the customer country in a session.
		 */
		public static function maybe_return_from_paypal() {
			if ( empty( $_GET['woo-paypal-return'] ) || empty( $_GET['token'] ) || empty( $_GET['PayerID'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				return;
			}

			$session = WC()->session->get( 'paypal' );

			if ( ! empty( $session ) && 'WC_Gateway_PPEC_Session_Data' === get_class( $session ) ) {
				if ( empty( $session->wcpbc_country ) ) {
					$session->wcpbc_country = wcpbc_get_woocommerce_country();
					WC()->session->set( 'paypal', $session );
				}
				add_action( 'wp', array( __CLASS__, 'update_customer_country' ), 1000 );
			}
		}

		/**
		 * Update the customer country with the original value.
		 */
		public static function update_customer_country() {
			$session = WC()->session->get( 'paypal' );
			if ( ! empty( $session->wcpbc_country ) ) {
				wcpbc_set_woocommerce_country( $session->wcpbc_country );
			}
		}

		/**
		 * Update the params with the order currency.
		 *
		 * @param array $params PayPal express request body.
		 * @return array
		 */
		public static function paypal_express_checkout_request_body( $params ) {

			if ( isset( $params['PAYMENTREQUEST_0_CUSTOM'] ) || isset( $params['CUSTOM'] ) ) {

				$prefix = isset( $params['PAYMENTREQUEST_0_CUSTOM'] ) ? 'PAYMENTREQUEST_0_' : '';
				$data   = json_decode( $params[ $prefix . 'CUSTOM' ], true );

				if ( $data && ! empty( $data['order_id'] ) ) {
					$order = wc_get_order( absint( $data['order_id'] ) );
					if ( $order && isset( $params[ $prefix . 'CURRENCYCODE' ] ) ) {
						$params[ $prefix . 'CURRENCYCODE' ] = version_compare( WC_VERSION, '3.0', '<' ) ? $order->get_order_currency() : $order->get_currency();
					}
				}
			}
			return $params;
		}
	}

	WCPBC_Gateway_Paypal_Express_Checkout::init();

endif;
