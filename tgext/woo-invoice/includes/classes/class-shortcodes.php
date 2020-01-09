<?php
/*
* @Author 		pickplugins
* Copyright: 	2015 pickplugins
*/

if ( ! defined('ABSPATH')) exit;  // if direct access 


class WOOIN_Shortcodes  {
	
	public $shortcodes = null;

	public function __construct(){

		$this->set_shortcodes();
		$this->add_shortcodes();
	}

	private function set_shortcodes(){
 
		$this->shortcodes = apply_filters( 'wooin_filter_shortcodes', array(
            
            'order-invoice' => array(
                'file-slug' => 'order-invoice',
            ),

		) );
	}
	
	private function add_shortcodes(){

		foreach( $this->shortcodes as $shortcode => $args ) : 
		add_shortcode( $shortcode, array( $this, 'shortcode_content_display' ), $shortcode );
		endforeach;
	}
	
	public function shortcode_content_display( $atts, $content = null, $shortcode ) {
               
        ob_start();
        // global $userprofile;

        if( is_wp_error( $shortcode_html = $this->get_shortcode_html( $shortcode, $atts ) ) ) {
                
            // $userprofile->print_error( $shortcode_html );
            return ob_get_clean();
        }
        
		echo $shortcode_html;
		return ob_get_clean();
    }
    
    public function get_shortcode_html( $shortcode, $atts ){

        $file_slug = isset( $this->shortcodes[ $shortcode ]['file-slug'] ) ? $this->shortcodes[ $shortcode ][ 'file-slug' ] : '';

        $template_dir = sprintf( "%stemplates/%s.php", WOOIN_PLUGIN_DIR, $file_slug );
        $template_dir = apply_filters( 'wooin_filter_shortcode_template_dir', $template_dir, $shortcode );
        $template_dir = file_exists( $template_dir ) ? $template_dir : '';

        if( empty( $template_dir ) ) return new WP_Error( 'empty_data', sprintf( __( 'Template file not found for shortcode : [%s]', 'woo-invoice' ), $shortcode ) );

        ob_start();
        include $template_dir;
        return ob_get_clean();
    }
	
} new WOOIN_Shortcodes();

