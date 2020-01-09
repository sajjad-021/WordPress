<?php
/**
 * Yoast SEO: Local plugin file.
 *
 * @package WPSEO_Local\Main
 * @since   1.0
 */

if ( ! class_exists( 'WPSEO_Local_Core' ) ) {

	/**
	 * WPSEO_Local_Core class. Handles all basic needs for the plugin, like custom post_type/taxonomy.
	 */
	class WPSEO_Local_Core {

		/**
		 * Stores the options for this plugin.
		 *
		 * @var array
		 */
		public $options = array();

		/**
		 * Contains the days, used for opening hours.
		 *
		 * @var array
		 */
		public $days = array();

		/**
		 * Holds the default option values for Yoast Local SEO.
		 *
		 * @var array
		 */
		public static $defaults;

		/**
		 * Constructor for the WPSEO_Local_Core class.
		 *
		 * @since 1.0
		 */
		public function __construct() {

			$this->options = get_option( 'wpseo_local' );
			$this->days    = array(
				'monday'    => __( 'Monday', 'yoast-local-seo' ),
				'tuesday'   => __( 'Tuesday', 'yoast-local-seo' ),
				'wednesday' => __( 'Wednesday', 'yoast-local-seo' ),
				'thursday'  => __( 'Thursday', 'yoast-local-seo' ),
				'friday'    => __( 'Friday', 'yoast-local-seo' ),
				'saturday'  => __( 'Saturday', 'yoast-local-seo' ),
				'sunday'    => __( 'Sunday', 'yoast-local-seo' ),
			);

			if ( wpseo_has_multiple_locations() ) {
				$this->create_custom_post_type();
				$this->create_taxonomies();
				$this->exclude_taxonomy();
				add_filter(
					'wpseo_primary_term_taxonomies',
					array( $this, 'filter_wpseo_primary_term_taxonomies' ),
					10,
					3
				);
			}

			if ( is_admin() ) {

				add_action( 'update_option_wpseo_local', array( $this, 'save_permalinks_on_option_save' ), 10 );

				// Setting action for removing the transient on update options.
				if ( method_exists( 'WPSEO_Sitemaps_Cache', 'register_cache_clear_option' ) ) {
					WPSEO_Sitemaps_Cache::register_clear_on_option_update( 'wpseo_local', 'kml' );
				}
			}
			else {
				// XML Sitemap Index addition.
				add_action( 'template_redirect', array( $this, 'redirect_old_sitemap' ) );
				add_filter( 'wpseo_sitemap_index', array( $this, 'add_to_index' ) );
			}

			add_action( 'init', array( $this, 'init_sitemaps' ), 11 );
			add_filter( 'wpseo_sitemap_http_headers', array( $this, 'handle_sitemap_headers' ) );

			// Add support for Jetpack's Omnisearch.
			$this->support_jetpack_omnisearch();
			add_action( 'save_post', array( $this, 'invalidate_sitemap' ) );

			// Run update if needed.
			add_action( 'init', array( $this, 'do_upgrade' ), 14 );

			// Set the default plugin options.
			add_action( 'init', array( $this, 'set_defaults' ), 14 );
			add_action( 'init', array( $this, 'check_defaults' ), 14 );

			// Add Local SEO to the adminbar.
			add_action( 'admin_bar_menu', array( $this, 'admin_bar_menu' ), 95 );

			add_action( 'admin_init', array( $this, 'enforce_company_settings' ), 14 );
		}

		/**
		 * Add Local SEO to the admin bar menu under SEO Settings
		 *
		 * @since 3.4
		 */
		public function admin_bar_menu() {
			global $wp_admin_bar;

			$args = array(
				'parent' => 'wpseo-settings',
				'id'     => 'wpseo-local',
				'title'  => 'Local SEO',
				'href'   => admin_url( 'admin.php?page=wpseo_local' ),
			);
			$wp_admin_bar->add_menu( $args );
		}

		/**
		 * Enforces the publishingEntity to be set to Company
		 *
		 * @since 11.0
		 */
		public function enforce_company_settings() {
			/*
			 * Set the value of this setting to `company`
			 * to make sure that it will remain on this setting even
			 * if Local SEO is deactivated.
			 */
			if ( ! WPSEO_Capability_Utils::current_user_can( 'wpseo_manage_options' ) ) {
				return;
			}

			if ( WPSEO_Options::get( 'company_or_person' ) !== 'company' ) {
				WPSEO_Options::set( 'company_or_person', 'company' );
			}

			// Add the filter after reading the "real" value in the databaes.
			add_filter( 'option_wpseo_titles', array( $this, 'filter_yoast_seo_company_settings' ) );
			add_filter( 'wpseo_knowledge_graph_setting_msg', array( $this, 'force_knowledge_graph_to_company_msg' ) );
		}

		/**
		 * This method will perform some checks before performing plugin upgrade (when needed).
		 */
		public function do_upgrade() {
			$options = get_option( 'wpseo_local' );

			if ( ! isset( $options['version'] ) ) {
				$options['version'] = '0';
			}

			if ( version_compare( $options['version'], WPSEO_LOCAL_VERSION, '<' ) ) {
				// First check if this is a multisite or not.
				if ( ! is_multisite() ) {
					// Performing other upgrades.
					$options = wpseo_local_do_upgrade( $options );
				}
				else {
					// If this is a multisite, get all the blogs and loop through them.
					global $wpdb;
					$network_blogs = $wpdb->get_col( $wpdb->prepare( "SELECT blog_id FROM $wpdb->blogs WHERE site_id = %d", $wpdb->siteid ) );
					if ( is_array( $network_blogs ) && $network_blogs !== array() ) {
						foreach ( $network_blogs as $blog_id ) {
							switch_to_blog( $blog_id );
							$options = wpseo_local_do_upgrade( $options );
							restore_current_blog();
						}
					}
				}

				// Update current version in database.
				$options['version'] = WPSEO_LOCAL_VERSION;
				update_option( 'wpseo_local', $options );
			}
		}

		/**
		 * Adds the rewrite for the Geo sitemap and KML file
		 *
		 * @since 1.0
		 */
		public function init_sitemaps() {

			if ( isset( $GLOBALS['wpseo_sitemaps'] ) ) {
				$GLOBALS['wpseo_sitemaps']->register_sitemap( 'geo', array( $this, 'build_local_sitemap' ) );
				$GLOBALS['wpseo_sitemaps']->register_sitemap( 'locations', array( $this, 'build_kml' ) );

				if ( preg_match( '/(geo-sitemap.xml|locations.kml)(.*?)$/', $_SERVER['REQUEST_URI'], $match ) ) {
					if ( in_array( $match[1], array( 'geo-sitemap.xml', 'locations.kml' ), true ) ) {
						$sitemap = 'geo';
						if ( $match[1] === 'locations.kml' ) {
							$sitemap = 'locations';
						}

						$GLOBALS['wpseo_sitemaps']->build_sitemap( $sitemap );
					}
					else {
						return;
					}

					// 404 for invalid or emtpy sitemaps.
					if ( $GLOBALS['wpseo_sitemaps']->bad_sitemap ) {
						$GLOBALS['wp_query']->is_404 = true;

						return;
					}

					$GLOBALS['wpseo_sitemaps']->output();
					$GLOBALS['wpseo_sitemaps']->sitemap_close();
				}
			}
		}

		/**
		 * Removes the noindex header for the KML file.
		 *
		 * @param $headers array HTTP headers to be sent.
		 *
		 * @return mixed
		 */
		public function handle_sitemap_headers( $headers ) {
			if ( preg_match( '/(locations.kml)(.*?)$/', $_SERVER['REQUEST_URI'], $match ) ) {
				unset( $headers['X-Robots-Tag: noindex, follow'] );
			}

			return $headers;
		}

		/**
		 * Method to invalidate the sitemap
		 *
		 * @param integer $post_id Post ID.
		 */
		public function invalidate_sitemap( $post_id ) {
			// If this is just a revision, don't invalidate the sitemap cache yet.
			if ( wp_is_post_revision( $post_id ) ) {
				return;
			}

			if ( get_post_type( $post_id ) === 'wpseo_locations' && method_exists( 'WPSEO_Sitemaps_Cache', 'invalidate' ) ) {
				WPSEO_Sitemaps_Cache::invalidate( 'kml' );
			}
		}

		/**
		 * Adds support for Jetpack's Omnisearch
		 */
		public function support_jetpack_omnisearch() {
			if ( class_exists( 'Jetpack_Omnisearch_Posts' ) ) {
				new Jetpack_Omnisearch_Posts( 'wpseo_locations' );
			}
		}

		/**
		 * Redirects old geo_sitemap.xml to geo-sitemap.xml to be more in line with other XML sitemaps of Yoast SEO plugin.
		 *
		 * @since 1.2.2.1
		 */
		public function redirect_old_sitemap() {
			if ( preg_match( '/(geo_sitemap.xml)(.*?)$/', $_SERVER['REQUEST_URI'], $match ) ) {

				if ( $match[1] === 'geo_sitemap.xml' && method_exists( 'WPSEO_Sitemaps_Router', 'get_base_url' ) ) {
					wp_safe_redirect( trailingslashit( WPSEO_Sitemaps_Router::get_base_url( '' ) ) . 'geo-sitemap.xml', 301 );
					exit;
				}
			}
		}

		/**
		 * @param boolean $exclude  Defaults to false.
		 * @param string  $taxonomy Name of the taxonomy to exclude.
		 *
		 * @return bool
		 */
		public function exclude_taxonomy_for_sitemap( $exclude, $taxonomy ) {
			if ( $taxonomy === 'wpseo_locations_category' ) {
				$terms = get_terms(
					array(
						'taxonomy'   => $taxonomy,
						'hide_empty' => true,
						'number'     => 1,
						'fields'     => 'ids',
					)
				);

				$exclude = empty( $terms );
			}

			return $exclude;
		}

		/**
		 * Adds the Geo Sitemap to the Index Sitemap.
		 *
		 * @param string $str String with the filtered additions to the index sitemap in it.
		 *
		 * @return string $str String with the local XML sitemap additions to the index sitemap in it.
		 * @since 1.0
		 */
		public function add_to_index( $str ) {
			$base_url = '';
			if ( method_exists( 'WPSEO_Sitemaps_Router', 'get_base_url' ) ) {
				$base_url = WPSEO_Sitemaps_Router::get_base_url( 'geo-sitemap.xml' );
			}

			$date = get_option( 'wpseo_local_xml_update' );
			if ( ! $date || $date == '' ) {
				$date = gmdate( 'c' );
			}

			$str .= '<sitemap>' . "\n";
			$str .= '<loc>' . $base_url . '</loc>' . "\n";
			$str .= '<lastmod>' . $date . '</lastmod>' . "\n";
			$str .= '</sitemap>' . "\n";

			return $str;
		}

		/**
		 * Pings Google with the (presumeably updated) Geo Sitemap.
		 *
		 * @since 1.0
		 */
		private static function ping() {

			// Ping Google. Just do it.
			if ( method_exists( 'WPSEO_Sitemaps_Router', 'get_base_url' ) ) {
				wp_remote_get( 'http://www.google.com/webmasters/tools/ping?sitemap=' . WPSEO_Sitemaps_Router::get_base_url( 'geo-sitemap.xml' ) );
			}
		}

		/**
		 * Updates the last update time transient for the local sitemap and pings Google with the sitemap.
		 *
		 * @since 1.0
		 */
		public static function update_sitemap() {
			// Empty sitemap cache.
			$caching = apply_filters( 'wpseo_enable_xml_sitemap_transient_caching', true );
			if ( $caching ) {
				delete_transient( 'wpseo_sitemap_cache_kml' );
			}

			update_option( 'wpseo_local_xml_update', gmdate( 'c' ) );

			// Ping sitemap.
			self::ping();
		}

		/**
		 * Set defaults settings for Local SEO.
		 *
		 * @since 3.4
		 */
		public static function set_defaults() {
			$options  = get_option( 'wpseo_local' );
			$defaults = array(
				'use_multiple_locations'              => 'off',
				'location_name'                       => '',
				'locations_taxo_slug'                 => '',
				'business_type'                       => '',
				'business_image'                      => '',
				'location_address'                    => '',
				'location_address_2'                  => '',
				'location_city'                       => '',
				'location_state'                      => '',
				'location_zipcode'                    => '',
				'location_country'                    => '',
				'location_phone'                      => '',
				'location_phone_2nd'                  => '',
				'location_fax'                        => '',
				'location_email'                      => '',
				'location_url'                        => ( class_exists( 'WPSEO_Sitemaps_Router' ) ? WPSEO_Sitemaps_Router::get_base_url( '' ) : '' ),
				'location_vat_id'                     => '',
				'location_tax_id'                     => '',
				'location_coc_id'                     => '',
				'location_price_range'                => '',
				'location_currencies_accepted'        => '',
				'location_payment_accepted'           => '',
				'location_area_served'                => '',
				'location_coords_lat'                 => '',
				'location_coords_long'                => '',
				'locations_slug'                      => '',
				'locations_label_singular'            => '',
				'locations_label_plural'              => '',
				'locations_taxo_slug'                 => '',
				'sl_num_results'                      => 10,
				'closed_label'                        => '',
				'hide_opening_hours'                  => 'off',
				'open_247'                            => 'off',
				'open_247_label'                      => '',
				'open_24h_label'                      => '',
				'opening_hours_24h'                   => 'off',
				'opening_hours_monday_from'           => '09:00',
				'opening_hours_monday_to'             => '17:00',
				'opening_hours_monday_second_from'    => '09:00',
				'opening_hours_monday_second_to'      => '17:00',
				'opening_hours_tuesday_from'          => '09:00',
				'opening_hours_tuesday_to'            => '17:00',
				'opening_hours_tuesday_second_from'   => '09:00',
				'opening_hours_tuesday_second_to'     => '17:00',
				'opening_hours_wednesday_from'        => '09:00',
				'opening_hours_wednesday_to'          => '17:00',
				'opening_hours_wednesday_second_from' => '09:00',
				'opening_hours_wednesday_second_to'   => '17:00',
				'opening_hours_thursday_from'         => '09:00',
				'opening_hours_thursday_to'           => '17:00',
				'opening_hours_thursday_second_from'  => '09:00',
				'opening_hours_thursday_second_to'    => '17:00',
				'opening_hours_friday_from'           => '09:00',
				'opening_hours_friday_to'             => '17:00',
				'opening_hours_friday_second_from'    => '09:00',
				'opening_hours_friday_second_to'      => '17:00',
				'opening_hours_saturday_from'         => '09:00',
				'opening_hours_saturday_to'           => '17:00',
				'opening_hours_saturday_second_from'  => '09:00',
				'opening_hours_saturday_second_to'    => '17:00',
				'opening_hours_sunday_from'           => '09:00',
				'opening_hours_sunday_to'             => '17:00',
				'opening_hours_sunday_second_from'    => '09:00',
				'opening_hours_sunday_second_to'      => '17:00',
				'unit_system'                         => 'METRIC',
				'map_view_style'                      => 'HYBRID',
				'address_format'                      => 'address-state-postal',
				'default_country'                     => '',
				'show_route_label'                    => '',
				'custom_marker'                       => '',
				'api_key_browser'                     => '',
				'api_key'                             => '',
				'googlemaps_api_key'                  => '',
				'enhanced_search'                     => 'off',
			);

			$defaults = apply_filters( 'wpseo_local_defaults', $defaults );

			self::$defaults = $defaults;
		}

		/**
		 * Check the default options and set them as option if needed.
		 *
		 * @since 3.9
		 */
		public function check_defaults() {
			$options = get_option( 'wpseo_local' );

			foreach ( self::$defaults as $option => $value ) {
				if ( empty( $options[ $option ] ) ) {
					$options[ $option ] = $value;
				}
			}

			update_option( 'wpseo_local', $options );
		}

		/**
		 * Filters wpseo_titles ot always set the company or person option to company.
		 *
		 * @param array $option The option values.
		 *
		 * @return array $option The option values.
		 */
		public function filter_yoast_seo_company_settings( $option ) {
			$option['company_or_person'] = 'company';

			return $option;
		}

		/**
		 * Outputs a message to users explaining why they can't change their Person or Company setting.
		 *
		 * @return string Message.
		 */
		public function force_knowledge_graph_to_company_msg() {
			/* translators: %s translates to the Yoast SEO: Local brand name */
			return sprintf( __( 'In order to make full use of %s functionality, we force the setting for \'Person or Organization\' here to \'Organization\'.', 'yoast-local-seo' ), 'Yoast SEO: Local' );
		}

		/**
		 * This function generates the Geo sitemap's contents.
		 *
		 * @since 1.0
		 */
		public function build_local_sitemap() {


			// Remark: no transient caching needed here, since the one home_url() request is faster than getting the transient cache.
			$kml_url = '';
			if ( method_exists( 'WPSEO_Sitemaps_Router', 'get_base_url' ) ) {
				$kml_url = WPSEO_Sitemaps_Router::get_base_url( 'locations.kml' );
			}

			// Build entry for Geo Sitemap.
			$output = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:geo="http://www.google.com/geo/schemas/sitemap/1.0">
				<url>
					<loc>' . $kml_url . '</loc>
					<lastmod>' . gmdate( 'c' ) . '</lastmod>
					<priority>1</priority>
				</url>
			</urlset>';

			if ( isset( $GLOBALS['wpseo_sitemaps'] ) ) {
				$GLOBALS['wpseo_sitemaps']->set_sitemap( $output );
				$GLOBALS['wpseo_sitemaps']->renderer->set_stylesheet( '<?xml-stylesheet type="text/xsl" href="' . dirname( plugin_dir_url( __FILE__ ) ) . '/css/geo-sitemap.xsl"?>' );
			}
		}

		/**
		 * This function generates the KML file contents.
		 *
		 * @since 1.0
		 */
		public function build_kml() {

			$output  = '';
			$caching = apply_filters( 'wpseo_enable_xml_sitemap_transient_caching', true );

			if ( $caching ) {
				$output = get_transient( 'wpseo_sitemap_cache_kml' );
			}

			if ( ! $output || '' === $output ) {
				$location_data = $this->get_location_data();

				if ( isset( $location_data['businesses'] ) && is_array( $location_data['businesses'] ) && count( $location_data['businesses'] ) > 0 ) {
					$output = "<kml xmlns=\"http://www.opengis.net/kml/2.2\" xmlns:atom=\"http://www.w3.org/2005/Atom\">\n";
					$output .= "\t<Document>\n";
					$output .= "\t\t<name>" . ( ! empty( $location_data['kml_name'] ) ? $location_data['kml_name'] : ' Locations for ' . $location_data['business_name'] ) . "</name>\n";

					if ( ! empty( $location_data->author ) ) {
						$output .= "\t\t<atom:author>\n";
						$output .= "\t\t\t<atom:name>" . $location_data['author'] . "</atom:name>\n";
						$output .= "\t\t</atom:author>\n";
					}
					if ( ! empty( $location_data_fields['business_website'] ) ) {
						$output .= "\t\t<atom:link href=\"" . $location_data['website'] . "\" />\n";
					}

					$output .= "\t\t<open>1</open>\n";
					$output .= "\t\t<Folder>\n";

					foreach ( $location_data['businesses'] as $key => $business ) {
						if ( ! empty( $business ) ) {
							$business_name        = esc_html( $business['business_name'] );
							$business_description = ! empty( $business['business_description'] ) ? esc_attr( strip_shortcodes( $business['business_description'] ) ) : '';
							$business_url         = esc_url( $business['business_url'] );
							if ( wpseo_has_multiple_locations() && ! empty( $business['post_id'] ) ) {
								$business_url = get_permalink( $business['post_id'] );
							}
							if ( ! isset( $business['full_address'] ) || empty( $business['full_address'] ) ) {
								$address_format = 'address-state-postal';
								if ( ! empty( $this->options['address_format'] ) ) {
									$address_format = $this->options['address_format'];
								}

								$address_details          = array(
									'business_address'   => ( isset( $business['business_address'] ) ) ? $business['business_address'] : '',
									'business_address_2' => ( isset( $business['business_address_2'] ) ) ? $business['business_address_2'] : '',
									'oneline'            => false,
									'business_zipcode'   => ( isset( $business['business_zipcode'] ) ) ? $business['business_zipcode'] : '',
									'business_city'      => ( isset( $business['business_city'] ) ) ? $business['business_city'] : '',
									'business_state'     => ( isset( $business['business_state'] ) ) ? $business['business_state'] : '',
									'show_state'         => true,
									'escape_output'      => false,
									'use_tags'           => false,
								);
								$format                   = new WPSEO_Local_Address_Format();
								$business['full_address'] = $format->get_address_format( $address_format, $address_details );

								if ( ! empty( $business['business_country'] ) ) {
									$business['full_address'] .= ', ' . WPSEO_Local_Frontend::get_country( $business['business_country'] );
								}
							}
							$business_fulladdress = $business['full_address'];

							$output .= "\t\t\t<Placemark>\n";
							$output .= "\t\t\t\t<name><![CDATA[" . html_entity_decode( $business_name ) . "]]></name>\n";
							$output .= "\t\t\t\t<address><![CDATA[" . $business_fulladdress . "]]></address>\n";
							$output .= "\t\t\t\t<phoneNumber><![CDATA[" . $business['business_phone'] . "]]></phoneNumber>\n";
							$output .= "\t\t\t\t<description><![CDATA[" . html_entity_decode( $business_description ) . "]]></description>\n";
							$output .= "\t\t\t\t<atom:link href=\"" . esc_url( $business_url ) . "\"/>\n";
							$output .= "\t\t\t\t<LookAt>\n";
							$output .= "\t\t\t\t\t<latitude>" . $business['coords']['lat'] . "</latitude>\n";
							$output .= "\t\t\t\t\t<longitude>" . $business['coords']['long'] . "</longitude>\n";
							$output .= "\t\t\t\t\t<altitude>1500</altitude>\n";
							$output .= "\t\t\t\t\t<range></range>\n";
							$output .= "\t\t\t\t\t<tilt>0</tilt>\n";
							$output .= "\t\t\t\t\t<heading></heading>\n";
							$output .= "\t\t\t\t\t<altitudeMode>relativeToGround</altitudeMode>\n";
							$output .= "\t\t\t\t</LookAt>\n";
							$output .= "\t\t\t\t<Point>\n";
							$output .= "\t\t\t\t\t<coordinates>" . $business['coords']['long'] . ',' . $business['coords']['lat'] . ",0</coordinates>\n";
							$output .= "\t\t\t\t</Point>\n";
							$output .= "\t\t\t</Placemark>\n";
						}
					}

					$output .= "\t\t</Folder>\n";
					$output .= "\t</Document>\n";
					$output .= "</kml>\n";

					if ( $caching ) {
						set_transient( 'wpseo_sitemap_cache_kml', $output, DAY_IN_SECONDS );
					}
				}
			}

			if ( isset( $GLOBALS['wpseo_sitemaps'] ) ) {
				$GLOBALS['wpseo_sitemaps']->set_sitemap( $output );
				$GLOBALS['wpseo_sitemaps']->renderer->set_stylesheet( '<?xml-stylesheet type="text/xsl" href="' . dirname( plugin_dir_url( __FILE__ ) ) . '/css/kml-file.xsl"?>' );
			}
		}

		/**
		 * Empties the sitemap cache when saving the options
		 */
		public function save_permalinks_on_option_save() {
			// Empty sitemap cache.
			$caching = apply_filters( 'wpseo_enable_xml_sitemap_transient_caching', true );
			if ( $caching ) {
				delete_transient( 'wpseo_sitemap_cache_kml' );
			}
		}

		/**
		 * Builds an array based upon the data from the wpseo_locations post type. This data is needed as input for the Geo sitemap & KML API.
		 *
		 * @param null|int $post_id Post ID of location.
		 *
		 * @return array
		 * @since 1.0
		 */
		public function get_location_data( $post_id = null ) {
			$locations = array();

			// Define base URL.
			$base_url = '';
			if ( method_exists( 'WPSEO_Sitemaps_Router', 'get_base_url' ) ) {
				$base_url = WPSEO_Sitemaps_Router::get_base_url( '' );
			}

			$repo                    = new WPSEO_Local_Locations_Repository();
			$locations['businesses'] = $repo->get( array( 'id' => $post_id ) );

			$base = $GLOBALS['wp_rewrite']->using_index_permalinks() ? 'index.php/' : '';

			$locations['business_name'] = get_option( 'blogname' );
			$locations['kml_name']      = 'Locations for ' . $locations['business_name'] . '.';
			$locations['kml_url']       = home_url( $base . '/locations.kml' );
			$locations['kml_website']   = $base_url;
			$locations['author']        = get_option( 'blogname' );

			return $locations;
		}

		/**
		 * Check if the uploaded custom marker does not exceed 100x100px
		 *
		 * @param int|string $image The ID of the uploaded custom marker.
		 */
		public function check_custom_marker_size( $image ) {
			if ( empty( $image ) ) {
				return;
			}

			if ( is_numeric( $image ) ) {
				$image_src = wp_get_attachment_image_src( $image );
			}

			if ( ! isset( $image_src ) ) {
				$image_id = $this->get_image_id_from_url( $image );
				if ( ! empty( $image_id ) ) {
					$image_src = wp_get_attachment_image_src( $image_id );
				}
			}

			if ( ! isset( $image_src ) || ! is_array( $image_src ) ) {
				return;
			}

			if ( $image_src[1] > 100 || $image_src[2] > 100 ) {
				echo '<p class="warning-alert">';
				echo '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" role="img" aria-hidden="true" focusable="false"><path d="M569.517 440.013C587.975 472.007 564.806 512 527.94 512H48.054c-36.937 0-59.999-40.055-41.577-71.987L246.423 23.985c18.467-32.009 64.72-31.951 83.154 0l239.94 416.028zM288 354c-25.405 0-46 20.595-46 46s20.595 46 46 46 46-20.595 46-46-20.595-46-46-46zm-43.673-165.346l7.418 136c.347 6.364 5.609 11.346 11.982 11.346h48.546c6.373 0 11.635-4.982 11.982-11.346l7.418-136c.375-6.874-5.098-12.654-11.982-12.654h-63.383c-6.884 0-12.356 5.78-11.981 12.654z"/></svg>';
				echo esc_html__( 'The uploaded custom marker exceeds the recommended size of 100x100 pixels. Please be aware this might influence the info popup.', 'yoast-local-seo' );
				echo '</p>';
			}
		}

		/**
		 * Get the image ID from the database, based on the URL.
		 *
		 * @param $image_url
		 *
		 * @return mixed
		 */
		private function get_image_id_from_url( $image_url ) {
			global $wpdb;
			$attachment = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE guid=%s;", $image_url ) );

			if ( empty( $attachment ) ) {
				return '';
			}

			return $attachment[0];
		}

		/**
		 * Creates the wpseo_locations Custom Post Type
		 */
		public function create_custom_post_type() {
			/* Locations as Custom Post Type */
			$label_singular = ! empty( $this->options['locations_label_singular'] ) ? $this->options['locations_label_singular'] : __( 'Location', 'yoast-local-seo' );
			$label_plural   = ! empty( $this->options['locations_label_plural'] ) ? $this->options['locations_label_plural'] : __( 'Locations', 'yoast-local-seo' );
			$labels         = array(
				'name'               => $label_plural,
				'singular_name'      => $label_singular,
				/* translators: %s extends to the singular label for the location post type */
				'add_new'            => sprintf( __( 'New %s', 'yoast-local-seo' ), $label_singular ),
				/* translators: %s extends to the singular label for the location post type */
				'new_item'           => sprintf( __( 'New %s', 'yoast-local-seo' ), $label_singular ),
				/* translators: %s extends to the singular label for the location post type */
				'add_new_item'       => sprintf( __( 'Add New %s', 'yoast-local-seo' ), $label_singular ),
				/* translators: %s extends to the singular label for the location post type */
				'edit_item'          => sprintf( __( 'Edit %s', 'yoast-local-seo' ), $label_singular ),
				/* translators: %s extends to the singular label for the location post type */
				'view_item'          => sprintf( __( 'View %s', 'yoast-local-seo' ), $label_singular ),
				/* translators: %s extends to the plural label for the location post type */
				'search_items'       => sprintf( __( 'Search %s', 'yoast-local-seo' ), $label_plural ),
				/* translators: %s extends to the plural label for the location post type */
				'not_found'          => sprintf( __( 'No %s found', 'yoast-local-seo' ), $label_plural ),
				/* translators: %s extends to the plural label for the location post type */
				'not_found_in_trash' => sprintf( __( 'No %s found in trash', 'yoast-local-seo' ), $label_plural ),
			);

			$slug = ! empty( $this->options['locations_slug'] ) ? $this->options['locations_slug'] : 'locations';

			$args_cpt = array(
				'labels'          => $labels,
				'public'          => true,
				'show_ui'         => true,
				'capability_type' => 'post',
				'hierarchical'    => false,
				'rewrite'         => array(
					'slug'       => esc_attr( $slug ),
					'with_front' => apply_filters( 'yoast_seo_local_cpt_with_front', true ),
				),
				'has_archive'     => esc_attr( $slug ),
				'menu_icon'       => 'dashicons-location',
				'query_var'       => true,
				'show_in_rest'    => true,
				'rest_base'       => 'wpseo_locations',
				'supports'        => array(
					'title',
					'editor',
					'excerpt',
					'author',
					'thumbnail',
					'revisions',
					'custom-fields',
					'page-attributes',
					'publicize',
					'wpcom-markdown',
				),
			);
			$args_cpt = apply_filters( 'wpseo_local_cpt_args', $args_cpt );

			register_post_type( 'wpseo_locations', $args_cpt );
		}

		/**
		 * Create custom taxonomy for wpseo_locations Custom Post Type
		 */
		public function create_taxonomies() {
			$location_post_type       = get_post_type_object( 'wpseo_locations' );
			$post_type_singular_label = $location_post_type->labels->singular_name;

			$labels = array(
				/* translators: %s extends to the singular label for the location category */
				'name'              => sprintf( __( '%s categories', 'yoast-local-seo' ), $post_type_singular_label ),
				/* translators: %s extends to the singular label for the location category */
				'singular_name'     => sprintf( __( '%s category', 'yoast-local-seo' ), $post_type_singular_label ),
				/* translators: %s extends to the singular label for the location category */
				'search_items'      => sprintf( __( 'Search %s categories', 'yoast-local-seo' ), $post_type_singular_label ),
				/* translators: %s extends to the singular label for the location category */
				'all_items'         => sprintf( __( 'All %s categories', 'yoast-local-seo' ), $post_type_singular_label ),
				/* translators: %s extends to the singular label for the location category */
				'parent_item'       => sprintf( __( 'Parent %s category', 'yoast-local-seo' ), $post_type_singular_label ),
				/* translators: %s extends to the singular label for the location category */
				'parent_item_colon' => sprintf( __( 'Parent %s category:', 'yoast-local-seo' ), $post_type_singular_label ),
				/* translators: %s extends to the singular label for the location category */
				'edit_item'         => sprintf( __( 'Edit %s category', 'yoast-local-seo' ), $post_type_singular_label ),
				/* translators: %s extends to the singular label for the location category */
				'update_item'       => sprintf( __( 'Update %s category', 'yoast-local-seo' ), $post_type_singular_label ),
				/* translators: %s extends to the singular label for the location category */
				'add_new_item'      => sprintf( __( 'Add New %s category', 'yoast-local-seo' ), $post_type_singular_label ),
				/* translators: %s extends to the singular label for the location category */
				'new_item_name'     => sprintf( __( 'New %s category name', 'yoast-local-seo' ), $post_type_singular_label ),
				/* translators: %s extends to the singular label for the location category */
				'menu_name'         => apply_filters( 'wpseo_locations_category_label', sprintf( __( '%s categories', 'yoast-local-seo' ), $post_type_singular_label ) ),
			);

			$slug = ! empty( $this->options['locations_taxo_slug'] ) ? $this->options['locations_taxo_slug'] : 'locations-category';

			$args = array(
				'hierarchical'          => true,
				'labels'                => $labels,
				'show_ui'               => true,
				'show_admin_column'     => true,
				'update_count_callback' => '_update_post_term_count',
				'query_var'             => true,
				'rewrite'               => array( 'slug' => esc_attr( $slug ) ),
				'show_in_rest'          => true,
			);
			$args = apply_filters( 'wpseo_local_custom_taxonomy_args', $args );

			// NOTE: when using the wpseo_locations_category_slug filter, be sure to save the permalinks in order for it to work.
			register_taxonomy( 'wpseo_locations_category', 'wpseo_locations', $args );
		}

		/**
		 * Call filter to exclude taxonomies from sitemap
		 */
		public function exclude_taxonomy() {
			add_filter( 'wpseo_sitemap_exclude_taxonomy', array( $this, 'exclude_taxonomy_for_sitemap' ), 10, 2 );
		}

		/**
		 * Filter the WPSEO primary term taxonomies to make sure the location categories are added to the array.
		 *
		 * Enable primary term for location categories, by adding this to the taxonomies array.
		 *
		 * @param array  $taxonomies     An array of taxonomy objects that are primary_term enabled.
		 * @param string $post_type      The post type for which to filter the taxonomies.
		 * @param array  $all_taxonomies All taxonomies for this post type, even ones that don't have primary term.
		 *
		 * @return array
		 */
		public function filter_wpseo_primary_term_taxonomies( $taxonomies, $post_type, $all_taxonomies ) {
			if ( isset( $all_taxonomies['wpseo_locations_category'] ) ) {
				$taxonomies['wpseo_locations_category'] = $all_taxonomies['wpseo_locations_category'];
			}

			return $taxonomies;
		}

		/**
		 * Inserts attachment in WordPress. Used by import panel.
		 *
		 * @param int    $post_id   The post ID where the attachment belongs to.
		 * @param string $image_url File url of the file which has to be uploaded.
		 * @param bool   $set_thumb If there's an image in the import file, then set is as a Featured Image.
		 *
		 * @return int|WP_Error attachment ID. Returns WP_Error when upload goes wrong.
		 */
		public function insert_attachment( $post_id, $image_url, $set_thumb = false ) {

			$file_array  = array();
			$description = get_the_title( $post_id );
			$tmp         = download_url( $image_url );

			// Set variables for storage.
			// Fix file filename for query strings.
			preg_match( '/[^\?]+\.(jpg|jpe|jpeg|gif|png)/i', $image_url, $matches );
			$file_array['name']     = basename( $matches[0] );
			$file_array['tmp_name'] = $tmp;

			// If error storing temporarily, unlink.
			if ( is_wp_error( $tmp ) ) {
				@unlink( $file_array['tmp_name'] );
				$file_array['tmp_name'] = '';
			}

			// Do the validation and storage stuff.
			$attachment_id = media_handle_sideload( $file_array, $post_id, $description );

			// If error storing permanently, unlink.
			if ( is_wp_error( $attachment_id ) ) {
				@unlink( $file_array['tmp_name'] );

				return $attachment_id;
			}

			if ( $set_thumb ) {
				update_post_meta( $post_id, '_thumbnail_id', $attachment_id );
			}

			return $attachment_id;
		}

		/**
		 * Returns the valid local business types currently shown on Schema.org
		 *
		 * @link http://schema.org/docs/full.html In the bottom of this page is a list of Local Business types.
		 * @return array
		 */
		public function get_local_business_types() {
			$business_types_repo = new WPSEO_Local_Business_Types_Repository();

			return $business_types_repo->get_business_types();
		}
	}
}
