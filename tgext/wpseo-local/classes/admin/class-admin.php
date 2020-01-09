<?php
/**
 * Yoast SEO: Local plugin file.
 *
 * @package WPSEO_LOCAL\Admin
 */

if ( ! defined( 'WPSEO_LOCAL_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

if ( ! class_exists( 'WPSEO_Local_Admin' ) ) {

	/**
	 * Class that holds most of the admin functionality for WP SEO Local.
	 */
	class WPSEO_Local_Admin {

		/**
		 * Group name for the options.
		 *
		 * @var string
		 */
		public $group_name = 'yoast_wpseo_local_options';

		/**
		 * Option name.
		 *
		 * @var string
		 */
		public $option_name = 'wpseo_local';

		/**
		 * Admin Asset Manager object.
		 *
		 * @var WPSEO_Local_Admin_Assets
		 */
		private $asset_manager;

		/**
		 * Holds the API key repository class.
		 *
		 * @var WPSEO_Local_Api_Keys_Repository
		 */
		private $api_repository;

		/**
		 * @var boolean True if the current page is the Yoast SEO: Local settings page.
		 */
		private $is_settings_page;

		/**
		 * @var boolean True if the current page is a Yoast SEO: Local locations page.
		 */
		private $is_locations_page;

		/**
		 * @var boolean True if the current page is a Yoast SEO: Local locations category term page.
		 */
		private $is_locations_category_term_page;

		/**
		 * @var boolean True if the current page is a post edit or create page.
		 */
		private $is_edit_page;

		/**
		 * Class constructor
		 */
		public function __construct() {

			add_action( 'admin_init', array( $this, 'options_init' ) );
			add_action( 'current_screen', array( $this, 'set_current_screen' ) );

			// Adds page to WP SEO menu.
			add_action( 'wpseo_submenu_pages', array( $this, 'register_settings_page' ), 20 );

			// Register local into admin_pages.
			$this->register_wpseo();

			// Add styles and scripts.
			add_action( 'admin_enqueue_scripts', array( $this, 'config_page_scripts' ) );
			add_action( 'admin_print_styles', array( $this, 'config_page_styles' ) );
			add_action( 'admin_footer', array( $this, 'config_page_footer' ) );

			// Flush the rewrite rules after options change.
			add_action( 'update_option_wpseo_local', array( $this, 'update_multiple_locations' ), 10, 2 );
			add_action( 'admin_init', array( $this, 'flush_rewrite_rules' ) );

			// Only initialize the Helpscout Beacon when the License Manager is present.
			if ( class_exists( 'Yoast_Plugin_License_Manager' ) ) {
				add_action( 'admin_init', array( $this, 'init_beacon' ) );
			}

			// Only register the yoast i18n when the page is a Yoast SEO page.
			if ( $this->is_local_seo_page( filter_input( INPUT_GET, 'page' ) ) ) {
				$this->register_i18n_promo_class();
			}

			add_action( 'admin_init', array( $this, 'maps_api_browser_key_notification' ) );

			$this->asset_manager = new WPSEO_Local_Admin_Assets();
			$this->asset_manager->register_assets();
			$this->api_repository = new WPSEO_Local_Api_Keys_Repository();
		}

		/**
		 * Initializes the HelpScout beacon
		 */
		public function init_beacon() {
			$page      = filter_input( INPUT_GET, 'page' );
			$query_var = ( $page ) ? $page : '';
			// Only add the helpscout beacon on Yoast SEO pages.
			if ( $query_var === 'wpseo_local' ) {
				$beacon = yoast_get_helpscout_beacon( $query_var, Yoast_HelpScout_Beacon::BEACON_TYPE_NO_SEARCH );
				$beacon->add_setting( new WPSEO_Local_Beacon_Settings() );
				$beacon->register_hooks();
			}
		}

		/**
		 * Registers the wpseo_local setting for Settings API
		 *
		 * @since 1.0
		 */
		public function options_init() {
			register_setting( 'yoast_wpseo_local_options', 'wpseo_local' );
		}

		/**
		 * Adds local page to admin_page variable of wpseo
		 */
		public function register_wpseo() {
			add_filter( 'wpseo_admin_pages', array( $this, 'register_local_page' ) );
		}

		/**
		 * Registers local page
		 *
		 * @param array $pages Array of admin pages.
		 *
		 * @return array
		 */
		public function register_local_page( $pages ) {
			$pages[] = 'wpseo_local';

			return $pages;
		}

		/**
		 * Registers the settings page in the WP SEO menu.
		 *
		 * @param array $submenu_pages Array of submenu pages for SEO admin menu item.
		 *
		 * @return array
		 * @since 1.0
		 */
		public function register_settings_page( $submenu_pages ) {
			$submenu_pages[] = array(
				'wpseo_dashboard',
				'Yoast SEO: Local SEO',
				'Local SEO',
				apply_filters( 'wpseo_manage_options_capability', 'wpseo_manage_options' ),
				'wpseo_local',
				array( 'WPSEO_Local_Admin_Page', 'build_page' ),
			);

			return $submenu_pages;
		}

		/**
		 * Set true or false values to see what screen we are on.
		 */
		public function set_current_screen() {
			global $pagenow;
			$current_screen = get_current_screen();

			$this->is_settings_page                = ( 'seo_page_wpseo_local' === $current_screen->id );
			$this->is_locations_page               = ( 'post' === $current_screen->base && 'wpseo_locations' === $current_screen->id );
			$this->is_locations_category_term_page = ( 'term' === $current_screen->base && 'edit-wpseo_locations_category' === $current_screen->id );
			$this->is_edit_page                    = in_array( $pagenow, array( 'post.php', 'post-new.php' ) );
		}

		/**
		 * Loads some CSS
		 *
		 * @since 1.0
		 */
		public function config_page_styles() {
			if ( $this->is_locations_page || $this->is_settings_page || $this->is_edit_page || $this->is_locations_category_term_page ) {
				$this->asset_manager->enqueue_style( 'admin-css' );
			}
		}

		/**
		 * Enqueues the (tiny) global JS needed for the plugin.
		 */
		public function config_page_scripts() {
			if ( $this->is_settings_page ) {
				$this->asset_manager->enqueue_script( 'support' );
			}

			if ( $this->is_settings_page || $this->is_locations_page || $this->is_locations_category_term_page ) {
				$this->asset_manager->enqueue_script( 'global-script' );
				wp_enqueue_media();
			}

			if ( $this->is_settings_page || $this->is_locations_page ) {
				$api_repository = new WPSEO_Local_Api_Keys_Repository();
				$api_key        = $api_repository->get_api_key( 'browser' );

				// Only load the Geocoding scripts if an API key is entered
				if ( ! empty( $api_key ) ) {
					$google_maps_url = '//maps.google.com/maps/api/js';

					$query_args = array();
					if ( ! empty( $api_key ) ) {
						$query_args['key'] = $api_key;
					}

					if ( ! empty( $language ) ) {
						$query_args['language'] = esc_attr( strtolower( $language ) );
					}

					if ( ! empty( $query_args ) ) {
						$google_maps_url = add_query_arg( $query_args, $google_maps_url );
					}

					wp_enqueue_script( 'maps-geocoder', $google_maps_url, array(), null, true );

					$this->asset_manager->register_wp_assets();
					$this->asset_manager->enqueue_script( 'wp-polyfill' );

					wp_localize_script( WPSEO_Local_Admin_Assets::PREFIX . 'locations', 'wpseoLocalLocations', array( 'apiKey' => $api_key ) );
					$this->asset_manager->enqueue_script( 'locations' );
				}
			}
		}

		/**
		 * Print the required JavaScript in the footer
		 */
		public function config_page_footer() {
			if ( $this->is_settings_page || $this->is_locations_page ) {
				// @codingStandardsIgnoreStart
				?>
				<script>
					jQuery( document ).ready( function( $ ) {
						$( "#business_type, #wpseo_business_type" ).select2( {
							placeholder: <?php echo wp_json_encode( __( 'Choose a business type', 'yoast-local-seo' ) ); ?>,
							allowClear: true,
						} );
						$( "#location_timezone, #wpseo_business_timezone" ).select2( {
							placeholder: <?php echo wp_json_encode( __( 'Choose a time zone', 'yoast-local-seo' ) ); ?>,
							allowClear: true,
						} );
						$( "#location_price_range, #wpseo_business_price_range" ).select2( {
							placeholder: <?php echo wp_json_encode( __( 'Select your price indication', 'yoast-local-seo' ) ); ?>,
							allowClear: false,
							minimumResultsForSearch: Infinity,
						} );
						$( "#location_country, #wpseo_business_country, #default_country" ).select2( {
							placeholder: <?php echo wp_json_encode( __( 'Choose a country', 'yoast-local-seo' ) ); ?>,
							allowClear: true,
						} );
						$( "#address_format" ).select2( {
							allowClear: false,
							minimumResultsForSearch: Infinity,
						} );
						$("#wpseo_copy_from_location").select2({
							placeholder: <?php echo wp_json_encode( __( 'Select a location', 'yoast-local-seo' ) ); ?>,
							allowClear: true
						});
					});
				</script>
				<?php
				// @codingStandardsIgnoreEnd
			}
		}

		/**
		 * Generates the import panel for importing locations via CSV
		 */
		public function import_panel() {

			echo '<div id="local-seo-import" class="yoastbox">';
			echo '<h2>' . esc_html__( 'CSV import of locations for Local Search', 'yoast-local-seo' ) . '</h2>';

			echo '</div>';
		}

		/**
		 * Flushes the rewrite rules if multiple locations is turned on or off or the slug is changed.
		 *
		 * @param mixed $old_option_value Value of the current option.
		 * @param mixed $new_option_value Value of the new, currently saved option.
		 *
		 * @since 1.3.1
		 */
		public function update_multiple_locations( $old_option_value, $new_option_value ) {
			$old_value_exists = array_key_exists( 'use_multiple_locations', $old_option_value );
			$new_value_exists = array_key_exists( 'use_multiple_locations', $new_option_value );

			$old_option_value['locations_slug'] = isset( $old_option_value['locations_slug'] ) ? esc_attr( $old_option_value['locations_slug'] ) : '';
			$new_option_value['locations_slug'] = isset( $new_option_value['locations_slug'] ) ? esc_attr( $new_option_value['locations_slug'] ) : '';

			$old_option_value['locations_taxo_slug'] = isset( $old_option_value['locations_taxo_slug'] ) ? esc_attr( $old_option_value['locations_taxo_slug'] ) : '';
			$new_option_value['locations_taxo_slug'] = isset( $new_option_value['locations_taxo_slug'] ) ? esc_attr( $new_option_value['locations_taxo_slug'] ) : '';

			if ( ( false === $old_value_exists && true === $new_value_exists ) || ( $old_option_value['locations_slug'] != $new_option_value['locations_slug'] ) || ( $old_option_value['locations_taxo_slug'] != $new_option_value['locations_taxo_slug'] ) ) {
				set_transient( 'wpseo_local_permalinks_settings_changed', true, 60 );
			}
		}

		/**
		 * Flushes the rewrite rules if multiple locations is turned on or off or the slug is changed.
		 *
		 * @since 1.3.1
		 */
		public function flush_rewrite_rules() {
			if ( get_transient( 'wpseo_local_permalinks_settings_changed' ) == true ) {
				flush_rewrite_rules();

				delete_transient( 'plugin_settings_have_changed' );
			}
		}

		/**
		 * Registers a notification if the Google Maps API browser key has not yet been set.
		 */
		public function maps_api_browser_key_notification() {
			if ( ! class_exists( 'Yoast_Notification_Center' ) ) {
				return;
			}

			$message_text    = sprintf(
			/* translators: %1$s expands to Yoast SEO: Local, %2$s expands to Google Maps,%3$s expands to a link open tag to the settings page, %4$s expands to the closing tag for the link(s) to the settings page and %5$s expands to the opening tag for the link to the knowledge base article */
				__( '%1$s needs a %2$s API key to show %2$s on your website. You haven\'t set a %2$s API key yet. Go to the %3$s%1$s API key tab%4$s to set the key, or %5$svisit the knowledge base%4$s for more information.', 'yoast-local-seo' ),
				'Yoast SEO: Local',
				'Google Maps',
				'<a href="' . admin_url( 'admin.php?page=wpseo_local#top#api_keys' ) . '">',
				'</a>',
				'<a href="https://yoa.st/generate-set-google-maps-browser-key" target="_blank">'
			);
			$message_options = array(
				'type' => Yoast_Notification::WARNING,
				'id'   => 'LocalSEOBrowserKey',
			);

			$api_key             = $this->api_repository->get_api_key();
			$notification_center = Yoast_Notification_Center::get();
			$notification        = new Yoast_Notification( $message_text, $message_options );

			if ( empty( $api_key ) ) {
				$notification_center->add_notification( $notification );
			}
			else {
				$notification_center->remove_notification( $notification );
			}
		}

		/**
		 * Registers a notification if the Google Maps API server key has not yet been set.
		 */
		public function maps_api_server_key_notification() {
			if ( ! class_exists( 'Yoast_Notification_Center' ) ) {
				return;
			}

			$message_text    = sprintf(
			/* translators: %1$s expands to Yoast SEO: Local, %2$s expands to Google Maps,%3$s expands to a link open tag to the settings page, %4$s expands to the closing tag for the link(s) to the settings page and %5$s expands to the opening tag for the link to the knowledge base article */
				__( '%1$s needs a %2$s server key to calculate the geographical location of addresses. You haven\'t set a %2$s server key yet. Go to the %3$s%1$s API keys page%4$s to set the key, or %5$svisit the knowledge base%4$s for more information.', 'yoast-local-seo' ),
				'Yoast SEO: Local',
				'Google Maps',
				'<a href="' . admin_url( 'admin.php?page=wpseo_local#top#api_keys' ) . '">',
				'</a>',
				'<a href="https://yoa.st/gm-geocoding-api-server-key" target="_blank">'
			);
			$message_options = array(
				'type' => Yoast_Notification::WARNING,
				'id'   => 'LocalSEOServerKey',
			);

			$api_key             = $this->api_repository->get_api_key( 'server' );
			$notification_center = Yoast_Notification_Center::get();
			$notification        = new Yoast_Notification( $message_text, $message_options );

			if ( empty( $api_key ) ) {
				$notification_center->add_notification( $notification );
			}
			else {
				$notification_center->remove_notification( $notification );
			}
		}

		/**
		 * Checks if the page is a local seo page.
		 *
		 * @param string $page The page that might be a local seo page.
		 *
		 * @return bool
		 */
		private function is_local_seo_page( $page ) {
			$pages = array( 'wpseo_local' );

			return in_array( $page, $pages, true );
		}

		/**
		 * Register the promotion class for our GlotPress instance
		 *
		 * @link https://github.com/Yoast/i18n-module
		 */
		private function register_i18n_promo_class() {
			$args = array(
				'textdomain'     => 'yoast-local-seo',
				'project_slug'   => 'yoast-seo-local',
				'plugin_name'    => 'Local SEO by Yoast',
				'hook'           => 'wpseo_admin_promo_footer',
				'glotpress_url'  => 'http://translate.yoast.com/gp/',
				'glotpress_name' => 'Yoast Translate',
				'glotpress_logo' => 'https://translate.yoast.com/gp-templates/images/Yoast_Translate.svg',
				'register_url'   => 'https://translate.yoast.com/gp/projects#utm_source=plugin&utm_medium=promo-box&utm_campaign=wpseo-i18n-promo',
			);

			new Yoast_I18n_v3( $args );
		}
	} /* End of class */

} //end if

