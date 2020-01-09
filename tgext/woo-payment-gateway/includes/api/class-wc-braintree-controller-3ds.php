<?php
/**
 * 
 * @since 3.0.0
 * @package Braintree/API
 *
 */
class WC_Braintree_Controller_3ds extends WC_Braintree_Rest_Controller {

	protected $namespace = '3ds/';

	public function __construct() {}

	public function register_routes() {
		register_rest_route ( $this->rest_uri (), 'vaulted_nonce', array( 
				'methods' => WP_REST_Server::CREATABLE, 
				'callback' => array( $this, 
						'get_vaulted_nonce' 
				) 
		) );
	}

	/**
	 *
	 * @param WP_REST_Request $request        	
	 */
	public function get_vaulted_nonce($request) {
		$response = new WP_REST_Response ();
		$token = $request->get_param ( 'token' );
		$version = $request->get_param ( 'version' );
		try {
			$gateway = new \Braintree\Gateway ( wc_braintree_connection_settings () );
			$result = $gateway->paymentMethodNonce ()->create ( $token );
			$response->set_data ( array( 
					'success' => true, 
					'data' => array( 
							'nonce' => $result->paymentMethodNonce->nonce, 
							'details' => $result->paymentMethodNonce->details 
					) 
			) );
			$response->set_status ( 200 );
		} catch ( \Braintree\Exception $e ) {
			$response->set_data ( array( 
					'success' => false, 
					'message' => wc_braintree_errors_from_object ( $e ) 
			) );
		}
		return $response;
	}
}