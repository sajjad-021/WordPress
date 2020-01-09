<?php
/**
 * Controller class that is called when a local payment gateway is used.
 * This controller stores
 * the payer data for later use by webhooks so the payment can be completed.
 *
 * @since 3.0.0
 * @package Braintree/API
 * @author User
 *        
 */
class WC_Braintree_Controller_Local_Payment extends WC_Braintree_Rest_Controller {

	protected $namespace = 'local-payment/';

	public function register_routes() {
		register_rest_route ( $this->rest_uri (), 'payment-data', array( 
				'methods' => WP_REST_Server::CREATABLE, 
				'callback' => array( $this, 
						'save_payment_data' 
				) 
		) );
	}

	/**
	 *
	 * @param WP_REST_Request $request        	
	 */
	public function process($request) {
		$gateway = braintree ()->gateway ();
		$result = $gateway->transaction ()->sale ( array( 
				'amount' => 1, 
				'options' => [ 
						'submitForSettlement' => True 
				], 
				'paypalAccount' => [ 
						'paymentId' => $request->get_param ( 'paymentId' ), 
						'payerId' => $request->get_param ( 'payerId' ) 
				] 
		) );
	}

	/**
	 * Store the payer Id against the order so it can be used later on to process the payment.
	 *
	 * @param WP_REST_Request $request        	
	 */
	public function save_payment_data($request) {
		try {
			// get the order;
			$order = wc_get_order ( $request->get_param ( 'order_id' ) );
			$order->update_meta_data ( 'wc_braintree_payment_id', $request->get_param ( 'payment_id' ) );
			$order->save ();
			return rest_ensure_response ( array( 
					'redirect_url' => $order->get_checkout_order_received_url () 
			) );
		} catch ( Exception $e ) {
			return new WP_Error ( 'order-error', $e->getMessage (), array( 
					'status' => 200 
			) );
		}
	}
}