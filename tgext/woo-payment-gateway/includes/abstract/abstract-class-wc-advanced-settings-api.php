<?php
if (! class_exists ( 'WC_Braintree_Settings_API' )) {
	return;
}
/**
 *
 * @version 3.0.0
 * @package Braintree/Abstracts
 *         
 */
abstract class WC_Braintree_Advanced_Settings_API extends WC_Braintree_Settings_API {

	public function __construct() {
		$this->init_form_fields ();
		$this->init_settings ();
		add_action ( 'woocommerce_update_options_checkout_braintree_advanced_' . $this->id, array( 
				$this, 'process_admin_options' 
		) );
		add_filter ( 'wc_braintree_advanced_settings_tabs', array( 
				$this, 'advanced_settings_tabs' 
		) );
		add_action ( 'woocommerce_settings_checkout_braintree_advanced_' . $this->id, array( 
				$this, 'enqueue_admin_scripts' 
		) );
		add_action ( 'woocommerce_settings_checkout_braintree_advanced_' . $this->id, array( 
				$this, 'output' 
		) );
		add_action ( 'wc_braintree_localize_' . $this->id . '_settings', array( 
				$this, 'localize_settings' 
		) );
		add_action ( 'wc_braintree_settings_before_options_braintree_advanced_' . $this->id, array( 
				$this, 'section_options' 
		) );
	}

	public function advanced_settings_tabs($tabs) {
		$tabs[ $this->id ] = $this->tab_title;
		return $tabs;
	}

	public function section_options() {
		global $current_section, $wc_braintree_subsection;
		include braintree ()->plugin_path () . 'includes/admin/views/advanced-settings-nav.php';
	}
}