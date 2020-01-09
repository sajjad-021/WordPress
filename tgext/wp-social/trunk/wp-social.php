<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/*
 * Plugin Name: Wp Social - Login, Share, Counter
 * Plugin URI: https://wpmet.com/
 * Description: Wp Social Login / Social Sharing / Social Counter System for Facebook, Google, Twitter, Linkedin, Dribble, Pinterest, Wordpress, Instagram, GitHub, Vkontakte, Reddit and more providers.
 * Author: Wpmet
 * Version: 1.2.8
 * Author URI: https://wpmet.com/
 * Text Domain: wslu-social-login
 * License: GPL2+
 * Domain Path: /languages/
**/
define( 'WSLU_VERSION', '1.2.8' );
define( 'WSLU_VERSION_PREVIOUS_STABLE_VERSION', '1.2.7' );

define( "WSLU_LOGIN_PLUGIN", plugin_dir_path(__FILE__) );
define( "WSLU_LOGIN_PLUGIN_URL", plugin_dir_url(__FILE__) );


require( WSLU_LOGIN_PLUGIN.'autoload.php' );
/**
* Load Text Domain
*/
if ( ! function_exists( 'wslu_social_init' ) ) :
	function wslu_social_init() {
		load_plugin_textdomain( 'wslu-social-login' , false, dirname( plugin_basename( __FILE__ ) ).'/languages' );
		
		if ( file_exists( WP_PLUGIN_DIR . '/elementor/elementor.php' ) ) {
			WpSocialXs\Elementor\Elements::instance()->_init();
		}
	}
	add_action( 'plugins_loaded', 'wslu_social_init', 199 );
endif;



/**
* Active Plugin
*/
if ( ! function_exists( 'xs_social_plugin_activate' ) ) :
	function xs_social_plugin_activate() {
		$counter = New \XsSocialCounter\Counter(false);
		$counter->xs_counter_defalut_providers();
		
	}
	register_activation_hook( __FILE__, 'xs_social_plugin_activate' );
	
	// custom function added
	if(file_exists(WSLU_LOGIN_PLUGIN.'inc/custom-function.php') ){
		include( WSLU_LOGIN_PLUGIN.'inc/custom-function.php');
	}
endif;

