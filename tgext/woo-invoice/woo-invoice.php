<?php
/*
Plugin Name: PickPlugins Invoice for WooCommerce
Plugin URI: http://pickplugins.com
Description: Send invoices to your WooCommerce customers
Version: 1.0.2
WC requires at least: 3.0.0
WC tested up to: 3.3
Text Domain: woo-invoice
Author: pickplugins
Author URI: http://pickplugins.com
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

if ( ! defined('ABSPATH')) exit;  // if direct access 

class woocommerceinvoice{
	
	public function __construct(){
	
        $this->define_constants();
        
        $this->loading_script();
		$this->loading_classes();
        $this->loading_functions();

		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ));
	}
	
	public function load_textdomain() {
		
		//load_plugin_textdomain( 'woo-invoice', false, plugin_basename( dirname( __FILE__ ) ) . '/languages/' );


        $locale = apply_filters( 'plugin_locale', get_locale(), 'woo-invoice' );
        load_textdomain('woo-invoice', WP_LANG_DIR .'/woo-invoice/woo-invoice-'. $locale .'.mo' );

        load_plugin_textdomain( 'woo-invoice', false, plugin_basename( dirname( __FILE__ ) ) . '/languages/' );
	}
    
	public function loading_functions() {
		
		require_once( WOOIN_PLUGIN_DIR . 'includes/functions.php');
        require_once( WOOIN_PLUGIN_DIR . 'includes/functions-settings.php');
	}
	
	public function loading_classes() {
		        			        		
		require_once( WOOIN_PLUGIN_DIR . 'includes/classes/class-order-columns.php');						
		require_once( WOOIN_PLUGIN_DIR . 'includes/classes/class-functions.php');			
		require_once( WOOIN_PLUGIN_DIR . 'includes/classes/class-shortcodes.php');	
		require_once( WOOIN_PLUGIN_DIR . 'includes/classes/class-pick-settings.php');	
	}

    public function admin_scripts(){
		
		wp_enqueue_script('jquery');
		wp_enqueue_script('wooin_js', plugins_url( '/assets/admin/js/scripts.js' , __FILE__ ) , array( 'jquery' ), time("H:s") );
        wp_localize_script('wooin_js', 'wooin_ajax', array( 'wooin_ajaxurl' => admin_url( 'admin-ajax.php')));
        
        wp_enqueue_style('wooin_style', WOOIN_PLUGIN_URL.'assets/admin/css/style.css', array(), time( "H:s") );	


		// Global
       // wp_enqueue_script('vue',  plugins_url( '/assets/global/vue.js' , __FILE__ ));
        // wp_enqueue_script('jspdf.min',  plugins_url( '/assets/global/jspdf.min.js' , __FILE__ ));
        
        wp_enqueue_style('wooin-global',WOOIN_PLUGIN_URL.'assets/global/style.css');
        wp_enqueue_style('icofont',     WOOIN_PLUGIN_URL.'assets/global/icofont.css');
        wp_enqueue_style('hint.min',    WOOIN_PLUGIN_URL.'assets/global/hint.min.css');
    }
    
	public function front_scripts(){
		
		wp_enqueue_script('jquery');
		wp_enqueue_script('wooin_front_js', plugins_url( '/assets/front/js/scripts.js' , __FILE__ ) , array( 'jquery' ), time("H:s") );
		wp_localize_script('wooin_front_js', 'wooin_ajax', array( 'wooin_ajaxurl' => admin_url( 'admin-ajax.php')));
        
        wp_enqueue_style('wooin_style', WOOIN_PLUGIN_URL.'assets/front/css/style.css', array(), time( "H:s") );	


        // Global
        //wp_enqueue_script('vue',  plugins_url( '/assets/global/vue.js' , __FILE__ ));
        // wp_enqueue_script('jspdf.min',  plugins_url( '/assets/global/jspdf.min.js' , __FILE__ ));
        
        wp_enqueue_style('wooin-global',WOOIN_PLUGIN_URL.'assets/global/style.css');
        wp_enqueue_style('icofont',     WOOIN_PLUGIN_URL.'assets/global/icofont.css');
        wp_enqueue_style('hint.min',    WOOIN_PLUGIN_URL.'assets/global/hint.min.css');
	}

    public function loading_script() {
	
		add_action( 'wp_enqueue_scripts', array( $this, 'front_scripts' ), 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
    }
    
    private function define( $name, $value ) {
		if( $name && $value )
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}
	
    public function define_constants() {

		$this->define('WOOIN_PLUGIN_URL', plugins_url('/', __FILE__)  );
		$this->define('WOOIN_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

	}
	
} new woocommerceinvoice();