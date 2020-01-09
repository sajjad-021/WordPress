<?php
/**
 * Yoast SEO: Local plugin file.
 *
 * @package WPSEO_Local\Admin
 */

/**
 * This class holds the assets for Yoast Local SEO.
 */
class WPSEO_Local_Admin_Assets extends WPSEO_Admin_Asset_Manager {

	/**
	 * Prefix for naming the assets.
	 *
	 * @var string
	 */
	const PREFIX = 'wp-seo-local-';

	/**
	 * Child constructor for WPSEO_Local_Admin_Assets
	 */
	public function __construct() {
		parent::__construct( new WPSEO_Admin_Asset_SEO_Location( WPSEO_LOCAL_FILE ), self::PREFIX );
	}

	/**
	 * Returns the scripts that need to be registered.
	 *
	 * @return array scripts that need to be registered.
	 */
	protected function scripts_to_be_registered() {
		$flat_version = $this->flatten_version( WPSEO_LOCAL_VERSION );

		$select2_language = 'en';
		$user_locale      = WPSEO_Language_Utils::get_user_locale();
		$language         = WPSEO_Language_Utils::get_language( $user_locale );

		if ( file_exists( WPSEO_PATH . "js/dist/select2/i18n/{$user_locale}.js" ) ) {
			// Chinese and some others use full locale.
			$select2_language = $user_locale;
		}
		elseif ( file_exists( WPSEO_PATH . "js/dist/select2/i18n/{$language}.js" ) ) {
			$select2_language = $language;
		}

		return array(
			array(
				'name'    => 'frontend',
				'src'     => self::PREFIX . 'frontend-' . $flat_version,
				'version' => WPSEO_LOCAL_VERSION,
				'deps'    => array( 'jquery' ),
			),
			array(
				'name'      => 'seo-locations',
				'src'       => self::PREFIX . 'analysis-locations-' . $flat_version,
				'version'   => WPSEO_LOCAL_VERSION,
				'in_footer' => true,
			),
			array(
				'name'      => 'seo-pages',
				'src'       => self::PREFIX . 'analysis-pages-' . $flat_version,
				'version'   => WPSEO_LOCAL_VERSION,
				'in_footer' => true,
			),
			array(
				'name'      => 'global-script',
				'src'       => self::PREFIX . 'global-' . $flat_version,
				'version'   => WPSEO_LOCAL_VERSION,
				'deps'      => array( 'jquery' ),
				'in_footer' => true,
			),
			array(
				'name'      => 'geocoding-repository',
				'src'       => self::PREFIX . 'geocoding-repository-' . $flat_version,
				'version'   => WPSEO_LOCAL_VERSION,
				'deps'      => array(),
				'in_footer' => true,
			),
			array(
				'name'      => 'locations',
				'src'       => self::PREFIX . 'locations-' . $flat_version,
				'version'   => WPSEO_LOCAL_VERSION,
				'deps'      => array( 'wp-polyfill' ),
				'in_footer' => true,
			),
			array(
				'name'      => 'store-locator',
				'src'       => self::PREFIX . 'store-locator-' . $flat_version,
				'version'   => WPSEO_LOCAL_VERSION,
				'deps'      => array( 'wp-polyfill' ),
				'in_footer' => true,
			),
			array(
				'name'      => 'support',
				'src'       => self::PREFIX . 'support-' . $flat_version,
				'version'   => WPSEO_LOCAL_VERSION,
				'deps'      => array( 'jquery' ),
				'in_footer' => true,
			),
			array(
				'name'    => 'select2',
				'src'     => 'select2/select2.full',
				'suffix'  => '.min',
				'deps'    => array(
					'jquery',
				),
				'version' => '4.0.3',
			),
			array(
				'name'    => 'select2-translations',
				'src'     => 'select2/i18n/' . $select2_language,
				'deps'    => array(
					'jquery',
					self::PREFIX . 'select2',
				),
				'version' => '4.0.3',
				'suffix'  => '',
			),
			array(
				'name'    => 'checkout',
				'src'     => self::PREFIX . 'checkout-' . $flat_version,
				'version' => WPSEO_LOCAL_VERSION,
				'deps'    => array( 'jquery' ),
			),
			array(
				'name'    => 'shipping-settings',
				'src'     => self::PREFIX . 'shipping-settings-' . $flat_version,
				'version' => WPSEO_LOCAL_VERSION,
				'deps'    => array( 'jquery' ),
			),
		);
	}

	/**
	 * Returns the styles that need to be registered.
	 *
	 * @return array styles that need to be registered.
	 * @todo Data format is not self-documenting. Needs explanation inline. R.
	 */
	protected function styles_to_be_registered() {
		$flat_version = $this->flatten_version( WPSEO_LOCAL_VERSION );

		return array(
			array(
				'name' => 'admin-css',
				'src'  => 'admin-' . $flat_version,
				'rtl'  => false,
			),
		);
	}
}
