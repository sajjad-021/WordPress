<?php
/**
 *
 * @since 3.0.0
 * @package Braintree/API
 *
 */
class WC_Braintree_Controller_Client_Token extends WC_Braintree_Rest_Controller {

	protected $namespace = 'client-token/';

	public function register_routes() {
		register_rest_route ( $this->rest_uri (), 'create', array( 
				array( 
						'methods' => WP_REST_Server::CREATABLE, 
						'callback' => array( $this, 
								'get_client_token' 
						) 
				) 
		) );
	}

	/**
	 *
	 * @param WP_REST_Request $request        	
	 */
	public function get_client_token($request) {
		$client_token = $this->generate_client_token ();
		$response = rest_ensure_response ( $client_token );
		return $response;
	}

	/**
	 * Generate a client token
	 *
	 * @return string
	 */
	private function generate_client_token() {
		$client_token = braintree ()->generate_client_token ();
		if (empty ( $client_token )) {
			return new WP_Error ( 'client-token-error', __ ( 'Error creating client token.', 'woo-payment-gateway' ), array( 
					'status' => 400 
			) );
		}
		return $client_token;
	}
}