<?php
/**
 *
 * @since 3.0.0
 * @package Braintree/Admin
 *
 */
if (! class_exists ( 'WC_Settings_API' )) {
	return;
}
class WC_Braintree_API_Settings extends WC_Braintree_Settings_API {

	public function __construct() {
		$this->id = 'braintree_api';
		$this->tab_title = __ ( 'API Settings', 'woo-payment-gateway' );
		add_action ( 'woocommerce_update_options_checkout_' . $this->id, array( 
				$this, 'process_admin_options' 
		) );
		add_filter ( 'woocommerce_save_settings_checkout_braintree_api', array( 
				$this, 'save_if_connection_test' 
		) );
		parent::__construct ();
	}

	public function init_form_fields() {
		$this->form_fields = apply_filters ( 'wc_braintree_api_form_fields', include 'api-settings.php' );
	}

	public function process_admin_options() {
		parent::process_admin_options ();
		$key1 = $this->get_field_key ( 'sandbox_connection_test' );
		$key2 = $this->get_field_key ( 'production_connection_test' );
		$env = ! empty ( $_POST[ $key1 ] ) ? 'sandbox' : ( ! empty ( $_POST[ $key2 ] ) ? 'production' : null );
		if (null !== $env) {
			$this->connection_test ( $env );
		}
		$this->fetch_merchant_accounts ();
	}

	public function save_if_connection_test($save) {
		if (! empty ( $_POST[ $this->get_field_key ( 'sandbox_connection_test' ) ] ) || ! empty ( $_POST[ $this->get_field_key ( 'production_connection_test' ) ] )) {
			return true;
		}
		return $save;
	}

	public function connection_test($env) {
		try {
			$gateway = new \Braintree\Gateway ( wc_braintree_connection_settings ( $env ) );
			try {
				$gateway->clientToken ()->generate ();
				$this->add_message ( sprintf ( __ ( '%1$s connection test was successful.', 'woo-payment-gateway' ), $env == 'sandbox' ? __ ( 'Sandbox', 'woo-payment-gateway' ) : __ ( 'Production', 'woo-payment-gateway' ) ) );
			} catch ( \Braintree\Exception\Configuration $e ) {
				$this->add_error ( __ ( 'A Configuration exception was thrown. This error typcically happens when you have entered your API keys incorrectly or have left a value blank.', 'woo-payment-gateway' ) );
			} catch ( \Braintree\Exception\Authentication $e ) {
				$this->add_error ( __ ( 'An Authentication exception was thrown. This error typcically happens when you have entered your API keys incorrectly', 'woo-payment-gateway' ) );
			} catch ( \Braintree\Exception\Authorization $e ) {
				$this->add_error ( 'An Authorization exception was thrown. This error typically happens when you have entered an incorrect API key. Double check that you entered your API keys in the correct fields. Also, make sure you didn\'t confuse your Merchant ID with a Merchant Account ID value.', 'woo-payment-gateway' );
			}
		} catch ( \Braintree\Exception $e ) {
			$this->add_error ( sprintf ( __ ( 'A general error occured during the connection test. Exception thrown: %1$s', 'woo-payment-gateway' ), get_class ( $e ) ) );
		}
	}

	/**
	 * Retrieve merchant accounts from the Braintree Gateway.
	 */
	public function fetch_merchant_accounts() {
		$envs = array( 'production', 'sandbox' 
		);
		$adv_options = get_option ( 'woocommerce_braintree_merchant_account_settings', array( 
				'production_merchant_accounts', 
				'sandbox_merchant_accounts' 
		) );
		$update = false;
		foreach ( $envs as $env ) {
			if (empty ( $adv_options[ "{$env}_merchant_accounts" ] ) && get_option ( "wc_braintree_fetch_{$env}_merchant_accounts", false ) !== false) {
				$settings = wc_braintree_connection_settings ( $env );
				if (is_array ( $settings ) && ! in_array ( '', $settings )) {
					try {
						$gateway = new \Braintree\Gateway ( $settings );
						$accounts = $gateway->merchantAccount ()->all ();
						foreach ( $accounts as $account ) {
							$adv_options[ "{$env}_merchant_accounts" ][ $account->currencyIsoCode ] = $account->id;
						}
						$update = true;
						delete_option ( "wc_braintree_fetch_{$env}_merchant_accounts" );
					} catch ( \Braintree\Exception $e ) {
						// $this->add_error ( sprintf ( __ ( 'Error fetching merchant accounts. Reason: %1$s', 'woo-payment-gateway' ), $e->getMessage () ) );
					}
				}
			}
		}
		if ($update) {
			update_option ( 'woocommerce_braintree_merchant_account_settings', $adv_options );
		}
	}
}