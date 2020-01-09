<?php
/**
 * Controller that intercepts Apple Pay REST requests. Used for updating Apple Pay Wallet elements
 * such as line items, shipping, etc.
 * @since 3.0.0
 * @package Braintree/API
 *
 */
class WC_Braintree_Controller_ApplePay extends WC_Braintree_Rest_Controller {

	protected $namespace = 'applepay/';

	/**
	 *
	 * @var WP_REST_Request
	 */
	private $request;

	/**
	 *
	 * @var WP_Error
	 */
	private $error;

	public function register_routes() {
		register_rest_route ( $this->rest_uri (), 'payment-method', array( 
				'methods' => WP_REST_Server::CREATABLE, 
				'callback' => array( $this, 
						'payment_method' 
				) 
		) );
		register_rest_route ( $this->rest_uri (), 'domain-association', array( 
				'methods' => WP_REST_Server::CREATABLE, 
				'callback' => array( $this, 
						'regiser_domain_association' 
				), 
				'permission_callback' => array( $this, 
						'admin_permission_check' 
				) 
		) );
	}

	/**
	 *
	 * @param WP_REST_Request $request        	
	 */
	public function payment_method($request) {
		WC ()->cart->calculate_totals ();
		/**
		 *
		 * @var WC_Braintree_ApplePay_Payment_Gateway $gateway
		 */
		$gateway = WC ()->payment_gateways ()->payment_gateways ()[ 'braintree_applepay' ];
		return rest_ensure_response ( array( 
				'success' => true, 
				'data' => array( 
						'newLineItems' => $gateway->get_line_items (), 
						'newTotal' => array( 
								'type' => 'final', 
								'label' => $gateway->get_option ( 'store_name' ), 
								'amount' => $gateway->get_cart_total () 
						) 
				) 
		) );
	}

	/**
	 *
	 * @since 3.0.9
	 * @param WP_REST_Request $request        	
	 */
	public function regiser_domain_association($request) {
		try {
			// try to add domain association file.
			if (isset ( $_SERVER[ 'DOCUMENT_ROOT' ] )) {
				$path = $_SERVER[ 'DOCUMENT_ROOT' ] . DIRECTORY_SEPARATOR . '.well-known';
				$file = $path . DIRECTORY_SEPARATOR . 'apple-developer-merchantid-domain-association';
				require_once ( ABSPATH . '/wp-admin/includes/file.php' );
				if (function_exists ( 'WP_Filesystem' ) && ( WP_Filesystem () )) {
					/**
					 *
					 * @var WP_Filesystem_Base $wp_filesystem
					 */
					global $wp_filesystem;
					if (! $wp_filesystem->is_dir ( $path )) {
						$wp_filesystem->mkdir ( $path );
					}
					$contents = $wp_filesystem->get_contents ( WC_BRAINTREE_PATH . 'apple-developer-merchantid-domain-association' );
					if ($wp_filesystem->put_contents ( $file, $contents, 0755 )) {
						return rest_ensure_response ( array( 
								'data' => array( 
										'message' => sprintf ( __ ( 'The %sapple-developer-merchantid-domain-association%s file has been added to your root folder. You may now register your domains in the Braintree control panel.', 'woo-payment-gateway' ), '<b>', '</b>' ) . sprintf ( '<p><a target="_blank" href="%s">%s</a></p>', 'https://docs.paymentplugins.com/wc-braintree/config/#/braintree_applepay', __ ( 'Apple Pay Integration Guide', 'woo-payment-gateway' ) ) 
								) 
						) );
					} else {
						throw new Exception ( sprintf ( __ ( 'The %sapple-developer-merchantid-domain-association%s file could not be added to your root folder. You will need to 
									add the file manually.', 'woo-payment-gateway' ), '<b>', '</b>' ) . sprintf ( '<p><a target="_blank" href="%s">%s</a></p>', 'https://docs.paymentplugins.com/wc-braintree/config/#/braintree_applepay', __ ( 'Apple Pay Integration Guide', 'woo-payment-gateway' ) ) );
					}
				}
			}
		} catch ( Exception $e ) {
			return new WP_Error ( 'apple-error', $e->getMessage (), array( 
					'status' => 200, 
					'message' => $e->getMessage () 
			) );
		}
	}
}