<?php
/**
 * Yoast SEO: Local plugin file.
 *
 * @package WPSEO_Local\Admin\
 * @since   4.0
 */

if ( ! defined( 'WPSEO_LOCAL_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

if ( ! class_exists( 'WPSEO_Local_Admin_General_Settings' ) ) {

	/**
	 * WPSEO_Local_Admin_General_Settings class.
	 *
	 * Build the WPSEO Local admin form.
	 *
	 * @since   4.0
	 */
	class WPSEO_Local_Admin_General_Settings {

		/**
		 * Holds the slug for this settings tab.
		 *
		 * @var string
		 */
		private $slug = 'general';

		/**
		 * Holds WPSEO Local Core instance.
		 *
		 * @var mixed
		 */
		private $wpseo_local_core;

		/**
		 * Holds WPSEO Local Timezone Repository instance.
		 *
		 * @var mixed
		 */
		private $wpseo_local_timezone_repository;

		/**
		 * Holds the API keys repository.
		 *
		 * @var WPSEO_Local_Api_Keys_Repository
		 */
		private $api_repository;

		/**
		 * Stores the options for this plugin.
		 *
		 * @var mixed
		 */
		private $options;

		/**
		 * WPSEO_Local_Admin_General_Settings constructor.
		 */
		public function __construct() {
			$this->get_core();
			$this->get_timezone_repository();
			$this->get_options();

			add_filter( 'wpseo_local_admin_tabs', array( $this, 'create_tab' ) );
			add_filter( 'wpseo_local_admin_help_center_video', array( $this, 'set_video' ) );
			$this->api_repository = new WPSEO_Local_Api_Keys_Repository();

			add_action( 'wpseo_local_admin_' . $this->slug . '_content', array( $this, 'introductory_copy' ), 10 );
			add_action( 'wpseo_local_admin_' . $this->slug . '_content', array( $this, 'multiple_locations' ), 10 );
			add_action( 'wpseo_local_admin_' . $this->slug . '_content', array( $this, 'single_location_settings' ), 10 );
			add_action( 'wpseo_local_admin_' . $this->slug . '_content', array( $this, 'multiple_locations_settings' ), 10 );
			add_action( 'wpseo_local_admin_' . $this->slug . '_content', array( $this, 'coordinates_settings' ), 10 );
			add_action( 'wpseo_local_admin_' . $this->slug . '_content', array( $this, 'address_format' ), 10 );
			add_action( 'wpseo_local_admin_' . $this->slug . '_content', array( $this, 'local_config' ), 10 );
		}

		/**
		 * Set WPSEO Local Core instance in local property
		 */
		private function get_core() {
			global $wpseo_local_core;
			$this->wpseo_local_core = $wpseo_local_core;
		}

		/**
		 * Set WPSEO Local Core Timezone Repository in local property
		 */
		private function get_timezone_repository() {
			$wpseo_local_timezone_repository       = new WPSEO_Local_Timezone_Repository();
			$this->wpseo_local_timezone_repository = $wpseo_local_timezone_repository;
		}

		/**
		 * Get wpseo_local options.
		 */
		private function get_options() {
			$this->options = get_option( 'wpseo_local' );
		}

		/**
		 * @param array $tabs Array holding the tabs.
		 *
		 * @return mixed
		 */
		public function create_tab( $tabs ) {
			$tabs[ $this->slug ] = __( 'Business info', 'yoast-local-seo' );

			return $tabs;
		}

		/**
		 * @param array $videos Array holding the videos for the help center.
		 *
		 * @return mixed
		 */
		public function set_video( $videos ) {
			$videos[ $this->slug ] = 'https://yoa.st/screencast-local-settings';

			return $videos;
		}

		/**
		 * Add local config action.
		 */
		public function local_config() {
			do_action( 'wpseo_local_config' );
		}

		/**
		 * Introductory copy before starting about the multiple locations settings.
		 */
		public function introductory_copy() {
			WPSEO_Local_Admin_Page::section_before( 'introductory-copy' );
			echo '<p>';
			esc_html_e( 'Set up the location of your business with the form below. This information will be used in the search results, and can be used to add blocks with contact information or a map to a page or post on your website.', 'yoast-local-seo' );
			echo '</p>';
			echo '<p>';
			printf(
			/* translators: 1: link open tag; 2: link close tag. %3$s expands to Yoast SEO */
				esc_html__( 'If you have multiple locations, %3$s will create a new Custom Post Type where you can manage your locations. %1$sRead more about managing multiple locations with CPTs%2$s.', 'yoast-local-seo' ),
				'<a href="https://kb.yoast.com/kb/configuration-guide-for-local-seo/#multiple-locations" target="_blank">',
				'</a>',
				'Yoast SEO'
			);
			echo '</p>';
			WPSEO_Local_Admin_Page::section_after(); // End introductory-copy section.
		}

		/**
		 * Multiple locations checkbox.
		 */
		public function multiple_locations() {
			WPSEO_Local_Admin_Page::section_before( 'select-multiple-locations' );
			//WPSEO_Local_Admin_Wrappers::checkbox( 'use_multiple_locations', '', __( 'Use multiple locations', 'yoast-local-seo' ) );


			Yoast_Form::get_instance()->toggle_switch(
				'use_multiple_locations',
				array(
					'on'  => __( 'Yes', 'yoast-local-seo' ),
					'off' => __( 'No', 'yoast-local-seo' ),
				),
				__( 'Use multiple locations', 'yoast-local-seo' )
			);

			WPSEO_Local_Admin_Page::section_after(); // End select-multiple-locations section.
		}

		/**
		 * Single locations settings section.
		 */
		public function single_location_settings() {
			$business_types_repo      = new WPSEO_Local_Business_Types_Repository();
			$flattened_business_types = $business_types_repo->get_business_types();
			$business_types_help      = new WPSEO_Admin_Help_Panel(
				'business_types_help',
				__( 'Help with: Business types', 'yoast-local-seo' ),
				sprintf(
					__( 'If your business type is not listed, please read %1$sthe FAQ entry%2$s.', 'yoast-local-seo' ),
					'<a href="https://yoa.st/business-listing" target="_blank">', '</a>'
				),
				'has-wrapper'
			);
			$price_indication_help    = new WPSEO_Admin_Help_Panel(
				'price_indication_help',
				__( 'Help with: Price indication', 'yoast-local-seo' ),
				esc_html__( 'Select the price indication of your business, where $ is cheap and $$$$$ is expensive.', 'yoast-local-seo' ),
				'has-wrapper'
			);
			$area_served_help    = new WPSEO_Admin_Help_Panel(
				'area_served_help',
				__( 'Help with: Area served', 'yoast-local-seo' ),
				esc_html__( 'The geographic area where a service or offered item is provided.', 'yoast-local-seo' ),
				'has-wrapper'
			);

			WPSEO_Local_Admin_Page::section_before( 'single-location-settings', 'clear: both; ' . ( wpseo_has_multiple_locations() ? 'display: none;' : '' ) );
			$company_name = WPSEO_Options::get( 'company_name' );
			$company_logo = WPSEO_Options::get( 'company_logo' );

			echo '<p>';
			if ( ! empty( $company_name ) || ! empty( $company_logo ) ) {

				printf(
				/* translators: 1: HTML <a> open tag; 2: <a> close tag; 3: "Yoast SEO". */
					esc_html__( 'You can change your current company name and logo within the general settings of the Search Appearance of %3$s. %1$sGo to the settings%2$s.', 'yoast-local-seo' ),
					'<a href="' . esc_url( admin_url( 'admin.php?page=wpseo_titles#top#general' ) ) . '">',
					'</a>',
					'Yoast SEO'
				);
			}
			else {
				printf(
				/* translators: 1: HTML <a> open tag; 2: <a> close tag; 3: "Yoast SEO". */
					esc_html__( 'You can set up your company name and logo within the general settings of the Search Appearance of %3$s. %1$sChange your company settings%2$s.', 'yoast-local-seo' ),
					'<a href="' . esc_url( admin_url( 'admin.php?page=wpseo_titles#top#general' ) ) . '">',
					'</a>',
					'Yoast SEO'
				);
			}
			echo '</p>';

			echo '<div class="wpseo-local-help-wrapper">';
			WPSEO_Local_Admin_Wrappers::select( 'business_type', apply_filters( 'yoast-local-seo-admin-label-business-type', __( 'Business type', 'yoast-local-seo' ) . $business_types_help->get_button_html() ), $flattened_business_types );

			echo $business_types_help->get_panel_html();
			echo '</div> <!-- .wpseo-local-help-wrapper -->';

			WPSEO_Local_Admin_Wrappers::textinput( 'location_address', apply_filters( 'yoast-local-seo-admin-label-business-address', __( 'Business address', 'yoast-local-seo' ) ), '', array( 'class' => 'wpseo_local_address_input' ) );
			WPSEO_Local_Admin_Wrappers::textinput( 'location_address_2', apply_filters( 'yoast-local-seo-admin-label-business-address-2', __( 'Business address line 2', 'yoast-local-seo' ) ) );
			WPSEO_Local_Admin_Wrappers::textinput( 'location_city', apply_filters( 'yoast-local-seo-admin-label-business-city', __( 'Business city', 'yoast-local-seo' ) ), '', array( 'class' => 'wpseo_local_city_input' ) );
			WPSEO_Local_Admin_Wrappers::textinput( 'location_state', apply_filters( 'yoast-local-seo-admin-label-business-state', __( 'Business state', 'yoast-local-seo' ) ), '', array( 'class' => 'wpseo_local_state_input' ) );
			WPSEO_Local_Admin_Wrappers::textinput( 'location_zipcode', apply_filters( 'yoast-local-seo-admin-label-business-zipcode', __( 'Business zipcode', 'yoast-local-seo' ) ), '', array( 'class' => 'wpseo_local_zipcode_input' ) );
			WPSEO_Local_Admin_Wrappers::select( 'location_country', apply_filters( 'yoast-local-seo-admin-label-business-country', __( 'Business country', 'yoast-local-seo' ) ), WPSEO_Local_Frontend::get_country_array() );
			WPSEO_Local_Admin_Wrappers::textinput( 'location_phone', apply_filters( 'yoast-local-seo-admin-label-business-phone', __( 'Business phone', 'yoast-local-seo' ) ) );
			WPSEO_Local_Admin_Wrappers::textinput( 'location_phone_2nd', apply_filters( 'yoast-local-seo-admin-label-business-phone-2', __( '2nd Business phone', 'yoast-local-seo' ) ) );
			WPSEO_Local_Admin_Wrappers::textinput( 'location_fax', apply_filters( 'yoast-local-seo-admin-label-business-fax', __( 'Business fax', 'yoast-local-seo' ) ) );
			WPSEO_Local_Admin_Wrappers::textinput( 'location_email', apply_filters( 'yoast-local-seo-admin-label-business-email', __( 'Business email', 'yoast-local-seo' ) ) );
			WPSEO_Local_Admin_Wrappers::textinput( 'location_url', apply_filters( 'yoast-local-seo-admin-label-business-url', __( 'URL', 'yoast-local-seo' ) ), '', array( 'placeholder' => WPSEO_Sitemaps_Router::get_base_url( '' ) ) );
			WPSEO_Local_Admin_Wrappers::textinput( 'location_vat_id', apply_filters( 'yoast-local-seo-admin-label-business-vat-id', __( 'VAT ID', 'yoast-local-seo' ) ) );
			WPSEO_Local_Admin_Wrappers::textinput( 'location_tax_id', apply_filters( 'yoast-local-seo-admin-label-business-tax-id', __( 'Tax ID', 'yoast-local-seo' ) ) );
			WPSEO_Local_Admin_Wrappers::textinput( 'location_coc_id', apply_filters( 'yoast-local-seo-admin-label-business-coc-id', __( 'Chamber of Commerce ID', 'yoast-local-seo' ) ) );

			echo '<div class="wpseo-local-help-wrapper">';

			WPSEO_Local_Admin_Wrappers::select( 'location_price_range', apply_filters( 'yoast-local-seo-admin-label-business-price-range', __( 'Price indication', 'yoast-local-seo' ) . $price_indication_help->get_button_html() ), $this->get_pricerange_array() );
			echo $price_indication_help->get_panel_html();
			echo '</div>';


			WPSEO_Local_Admin_Wrappers::textinput( 'location_currencies_accepted', apply_filters( 'yoast-local-seo-admin-label-business-currencies-accepted', __( 'Currencies accepted', 'yoast-local-seo' ) ) );
			WPSEO_Local_Admin_Wrappers::textinput( 'location_payment_accepted', apply_filters( 'yoast-local-seo-admin-label-business-payment-accepted', __( 'Payment methods accepted', 'yoast-local-seo' ) ) );

			echo '<div class="wpseo-local-help-wrapper">';
			WPSEO_Local_Admin_Wrappers::textinput( 'location_area_served', apply_filters( 'yoast-local-seo-admin-label-business-area-served', __( 'Area served', 'yoast-local-seo' ) . $area_served_help->get_button_html() ) );
			echo $area_served_help->get_panel_html();
			echo '</div>';

			WPSEO_Local_Admin_Page::section_after(); // End show-single-locaton section.
		}

		/**
		 * Multiple locations settings section.
		 */
		public function multiple_locations_settings() {
			$base_url = get_site_url();

			WPSEO_Local_Admin_Page::section_before( 'multiple-locations-settings', 'clear: both; ' . ( wpseo_has_multiple_locations() ? '' : 'display: none;' ) );

			echo '<p>' . esc_html__( 'You can find some advanced settings regarding multiple locations under the Advanced tab', 'yoast-local-seo' ) . '</p>';

			Yoast_Form::get_instance()->toggle_switch(
				'multiple_locations_same_organization',
				array(
					'on'  => __( 'Yes', 'yoast-local-seo' ),
					'off' => __( 'No', 'yoast-local-seo' ),
				),
				__( 'Are all locations part of the same Organization?', 'yoast-local-seo' )
			);

			echo '<h2>' . esc_html__( 'Add multiple locations', 'yoast-local-seo' ) . '</h2>';
			echo '<p>';
			printf(
			/* translators: 1: link open tag; 2: link close tag. */
				esc_html__( 'You have selected the multiple locations option, so we added the Locations Post Type for you in the menu on the left. Now you can start adding the locations %1$sright here%2$s.', 'yoast-local-seo' ),
				'<a href="' . esc_url( $base_url ) . '/wp-admin/edit.php?post_type=wpseo_locations" target="_blank">',
				'</a>'
			);
			echo '</p>';
			WPSEO_Local_Admin_Page::section_after();
		}

		/**
		 * Retrieves array of the 5 pricerange steps.
		 *
		 * @return array Array of pricerange.
		 */
		private function get_pricerange_array() {
			$pricerange = array(
				''      => __( 'Select your price indication', 'yoast-local-seo' ),
				'$'     => '$',
				'$$'    => '$$',
				'$$$'   => '$$$',
				'$$$$'  => '$$$$',
				'$$$$$' => '$$$$$',
			);

			return $pricerange;
		}

		/**
		 * Adds the maps settings with single location setup
		 */
		public function coordinates_settings() {
			WPSEO_Local_Admin_Page::section_before( 'location-coordinates-settings', 'clear: both; ' . ( wpseo_has_multiple_locations() ? 'display: none;' : '' ) );
			echo '<h3>' . esc_html__( 'Location coordinates', 'yoast-local-seo' ) . '</h3>';

			echo '<p>' . esc_html__( 'Here you can enter the latitude and longitude coordinates yourself. If you\'ve entered a Google Maps API Key these coordinates will be automatically calculated. This API Key is also needed for the map to show on your site.', 'yoast-local-seo' ) . '</p>';
			if ( empty( $this->options['location_coords_lat'] ) || empty( $this->options['location_coords_long'] ) ) {
				echo '<p>' . esc_html__( 'In order for automatic lat/long calculation to work, you first need to enter an API code under the API tab at the top of this page', 'yoast-local-seo' ) . '</p>';
			}

			WPSEO_Local_Admin_Wrappers::textinput( 'location_coords_lat', apply_filters( 'yoast-local-seo-admin-label-business-lat', __( 'Latitude', 'yoast-local-seo' ) ), '', array( 'class' => 'wpseo_local_lat_input' ) );
			WPSEO_Local_Admin_Wrappers::textinput( 'location_coords_long', apply_filters( 'yoast-local-seo-admin-label-business-long', __( 'Longitude', 'yoast-local-seo' ) ), '', array( 'class' => 'wpseo_local_lng_input' ) );

			$api_key = $this->api_repository->get_api_key( 'browser' );

			if ( ! empty( $api_key ) && ( '' != $this->options['location_coords_lat'] && '' != $this->options['location_coords_long'] ) ) {
				echo '<p>' . esc_html__( 'If the marker is not in the right location for your store, you can drag the pin to the location where you want it.', 'yoast-local-seo' ) . '</p>';

				$args = array(
					'echo'       => true,
					'show_route' => false,
					'map_style'  => 'roadmap',
					'draggable'  => true,
				);
				wpseo_local_show_map( $args );

				echo '<br />';
			}
			WPSEO_Local_Admin_Page::section_after();
		}

		/**
		 * Show the dropdown to select an address format.
		 */
		public function address_format() {
			WPSEO_Local_Admin_Page::section_before( 'wpseo-local-address-format' );
			echo '<h3>' . esc_html__( 'Address format', 'yoast-local-seo' ) . '</h3>';

			$select_options = array(
				'address-state-postal'       => '{address} {city}, {state} {zipcode} &nbsp;&nbsp;&nbsp;&nbsp; (New York, NY 12345 )',
				'address-state-postal-comma' => '{address} {city}, {state}, {zipcode} &nbsp;&nbsp;&nbsp;&nbsp; (New York, NY, 12345 )',
				'address-postal-city-state'  => '{address} {zipcode} {city}, {state} &nbsp;&nbsp;&nbsp;&nbsp; (12345 New York, NY )',
				'address-postal'             => '{address} {city} {zipcode} &nbsp;&nbsp;&nbsp;&nbsp; (New York 12345 )',
				'address-postal-comma'       => '{address} {city}, {zipcode} &nbsp;&nbsp;&nbsp;&nbsp; (New York, 12345 )',
				'address-city'               => '{address} {city} &nbsp;&nbsp;&nbsp;&nbsp; (New York)',
				'postal-address'             => '{zipcode} {state} {city} {address} &nbsp;&nbsp;&nbsp;&nbsp; (12345 NY New York)',
			);
			WPSEO_Local_Admin_Wrappers::select(
				'address_format',
				__( 'Address format', 'yoast-local-seo' ),
				$select_options
			);

			/* translators: %s extends to <a href="mailto:pluginsupport@yoast.com">pluginsupport@yoast.com</a> */
			echo '<p style="border:none;">' . sprintf( __( 'A lot of countries have their own address format. Please choose one that matches yours. If you have something completely different, please let us know via %s.', 'yoast-local-seo' ), '<a href="mailto:pluginsupport@yoast.com">pluginsupport@yoast.com</a>' ) . '</p>';
			WPSEO_Local_Admin_Page::section_after(); // End address format section.
		}
	}
}
