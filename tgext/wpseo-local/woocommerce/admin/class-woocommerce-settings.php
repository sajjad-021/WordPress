<?php
/**
 * Yoast SEO: Local for WooCommerce plugin file.
 *
 * @package YoastSEO_Local_WooCommerce
 * @since   4.0
 */

if ( ! defined( 'WPSEO_LOCAL_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

if ( ! class_exists( 'WPSEO_Local_Admin_Woocommerce_Settings' ) ) {

	/**
	 * WPSEO_Local_Admin_API_Settings class.
	 *
	 * Build the WPSEO Local admin form.
	 *
	 * @since   4.1
	 */
	class WPSEO_Local_Admin_Woocommerce_Settings {

		/**
		 * Holds the slug for this settings tab.
		 *
		 * @var string
		 */
		private $slug = 'woocommerce';

		/**
		 * WPSEO_Local_Admin_API_Settings constructor.
		 */
		public function __construct() {
			add_filter( 'wpseo_local_admin_tabs', array( $this, 'create_tab' ), 99 );

			add_action( 'wpseo_local_admin_' . $this->slug . '_content', array( $this, 'tab_content' ), 10 );
		}

		/**
		 * Adds the WooCommerce Settings tab in the WPSEO local admin panel.
		 *
		 * @param array $tabs Array holding the tabs.
		 *
		 * @return mixed
		 */
		public function create_tab( $tabs ) {
			/* translators: 1: expands to 'WooCommerce'. */
			$tabs[ $this->slug ] = sprintf( __( '%1$s settings', 'yoast-local-seo' ), 'WooCommerce' );

			return $tabs;
		}

		/**
		 * Create tab content for API Settings.
		 */
		public function tab_content() {
			/* translators: 1: expands to 'Local SEO for WooCommerce'. */
			echo '<h2>' . sprintf( esc_html__( '%1$s settings', 'yoast-local-seo' ), 'Local SEO for WooCommerce' ) . '</h2>';
			echo '<div>';
			printf(
				/* translators: 1: expands to '<a>'; 2: expands to '</a>' */
				esc_html__( '%1$sClick here%2$s for the specific WooCommerce settings', 'yoast-local-seo' ),
				'<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=shipping&section=yoast_wcseo_local_pickup' ) ) . '">',
				'</a>'
			);
			echo '</div>';
		}
	}
}
