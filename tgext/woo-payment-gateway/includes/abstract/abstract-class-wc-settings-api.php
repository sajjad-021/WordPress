<?php
if (! class_exists ( 'WC_Settings_API' )) {
	return;
}
/**
 *
 * @version 3.0.0
 * @package Braintree/Abstracts
 *
 */
abstract class WC_Braintree_Settings_API extends WC_Settings_API {

	protected $tab_title = '';

	private $messages = array();

	public function __construct() {
		$this->init_form_fields ();
		$this->init_settings ();
		add_action ( 'woocommerce_settings_checkout_' . $this->id, array( 
				$this, 'output' 
		) );
		add_filter ( 'wc_braintree_admin_settings_tabs', array( 
				$this, 'admin_settings_tabs' 
		) );
		add_action ( 'wc_braintree_localize_' . $this->id . '_settings', array( 
				$this, 'localize_settings' 
		) );
	}

	public function output() {
		global $current_section;
		if (count ( $this->get_errors () ) > 0) {
			$this->display_errors ();
		}
		if (count ( $this->get_messages () ) > 0) {
			$this->display_messages ();
		}
		$this->admin_options ();
	}

	public function admin_settings_tabs($tabs) {
		$tabs[ $this->id ] = $this->get_tab_title ();
		return $tabs;
	}

	public function get_tab_title() {
		return $this->tab_title;
	}

	public function get_prefix() {
		return $this->plugin_id . $this->id . '_';
	}

	public function get_custom_attribute_html($attribs) {
		if (! empty ( $attribs[ 'custom_attributes' ] ) && is_array ( $attribs[ 'custom_attributes' ] )) {
			foreach ( $attribs[ 'custom_attributes' ] as $k => $v ) {
				if (is_array ( $v )) {
					$attribs[ 'custom_attributes' ][ $k ] = htmlspecialchars ( wp_json_encode ( $v ) );
				}
			}
		}
		return parent::get_custom_attribute_html ( $attribs );
	}

	public function generate_multiselect_html($key, $data) {
		$value = ( array ) $this->get_option ( $key, array() );
		$data[ 'options' ] = array_merge ( array_flip ( $value ), $data[ 'options' ] );
		return parent::generate_multiselect_html ( $key, $data );
	}

	public function generate_button_html($key, $data) {
		$field_key = $this->get_field_key ( $key );
		$defaults = array( 'title' => '', 'label' => '', 
				'disabled' => false, 'class' => '', 
				'css' => '', 'type' => 'text', 
				'desc_tip' => false, 'description' => '', 
				'custom_attributes' => array() 
		);
		
		$data = wp_parse_args ( $data, $defaults );
		
		if (! $data[ 'label' ]) {
			$data[ 'label' ] = $data[ 'title' ];
		}
		ob_start ();
		include braintree ()->plugin_path () . 'includes/admin/views/button-html.php';
		return ob_get_clean ();
	}

	public function generate_paragraph_html($key, $data) {
		$field_key = $this->get_field_key ( $key );
		$defaults = array( 'title' => '', 'label' => '', 
				'class' => '', 'css' => '', 'type' => 'text', 
				'desc_tip' => false, 'description' => '', 
				'custom_attributes' => array() 
		);
		$data = wp_parse_args ( $data, $defaults );
		if (! $data[ 'label' ]) {
			$data[ 'label' ] = $data[ 'title' ];
		}
		ob_start ();
		include braintree ()->plugin_path () . 'includes/admin/views/paragraph-html.php';
		return ob_get_clean ();
	}

	public function admin_options() {
		global $current_section;
		include braintree ()->plugin_path () . 'includes/admin/views/html-settings-nav.php';
		printf ( '<input type="hidden" id="wc_braintree_prefix" name="wc_braintree_prefix" value="%1$s"/>', $this->get_prefix () );
		echo '<div class="wc-braintree-settings-container">';
		do_action ( 'wc_braintree_settings_before_options_' . $current_section );
		parent::admin_options ();
		echo '</div>';
	}

	public function add_message($message) {
		$this->messages[] = $message;
	}

	public function get_messages() {
		return $this->messages;
	}

	public function display_messages() {
		if ($this->get_messages ()) {
			echo '<div id="woocommerce_messages" class="updated notice is-dismissible">';
			foreach ( $this->get_messages () as $error ) {
				echo '<p>' . wp_kses_post ( $error ) . '</p>';
			}
			echo '</div>';
		}
	}

	public function localize_settings() {
		wp_localize_script ( 'wc-braintree-admin-settings', 'woocommerce_' . $this->id . '_settings_params', $this->get_localized_params () );
	}

	protected function get_localized_params() {
		return $this->settings;
	}

	public function enqueue_admin_scripts() {}

	/**
	 * Return true if the provided option is active.
	 *
	 * @param string $key        	
	 * @return boolean
	 */
	public function is_active($key) {
		return $this->get_option ( $key ) === 'yes';
	}
}