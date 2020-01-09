<?php
if (! defined ( 'ABSPATH' )) {
	exit ();
}
/**
 *
 * @since 3.0.0
 * @package Braintree/Classes/DataStore
 *         
 */
class WC_Braintree_Subscription_Data_Store_CPT extends WC_Order_Data_Store_CPT {

	const data_type = 'braintree_subscription';

	private $subscription_internal_meta_keys = array( 
			'_merchant_account_id', '_trial_end_date', 
			'_subscription_trial_length', 
			'_subscription_trial_period', '_start_date', 
			'_first_payment_date', '_next_payment_date', 
			'_end_date', '_previous_payment_date', 
			'_braintree_plan', '_subscription_period', 
			'_subscription_period_interval', 
			'_subscription_length', '_created_in_braintree', 
			'_recurring_cart_key' 
	);

	public static function init() {
		add_filter ( 'woocommerce_data_stores', function ($data_stores) {
			$data_stores[ self::data_type ] = __CLASS__;
			return $data_stores;
		} );
	}

	public function update_post_meta(&$order) {
		$id = $order->get_id ();
		parent::update_post_meta ( $order );
		foreach ( $this->get_props_to_update ( $order, $this->meta_keys_to_props () ) as $meta_key => $prop ) {
			$value = $order->{"get_$prop"} ( 'edit' );
			update_post_meta ( $id, $meta_key, $value );
		}
	}

	private function meta_keys_to_props() {
		$keys = array();
		foreach ( $this->subscription_internal_meta_keys as $key ) {
			$keys[ $key ] = substr ( $key, 1 );
		}
		return $keys;
	}
}
WC_Braintree_Subscription_Data_Store_CPT::init ();