<?php
namespace WpSocialXs\Elementor;
if ( ! defined( 'ABSPATH' ) ) die( 'Forbidden' );
/**
 * Class Name : Elements - For configration elementtor widget for Fundrasing.
 * Class Type : Normal class
 *
 * initiate all necessary classes, hooks, configs
 *
 * @since 1.0.0
 * @access Public
 */

Class Elements{
    private $active_widgets = ['share'];

	private static $instance;

    public function _init($load = true){
		if($load){
            if ( ! did_action( 'elementor/loaded' ) ) {
                return false;
            }
            add_action('elementor/init', [$this, '_elementor_widget_resister']);

            //call widget 
           add_action( 'elementor/widgets/widgets_registered', [$this, 'register_widget']);
        }

    }
     /**
     * Method Name: _elementor_widget_resister
     * Details : Register categories of Elementor Controls
     * 
     * @since 1.0.0
     * @access public
     */
    public function _elementor_widget_resister( $widgets_manager ){
        \Elementor\Plugin::$instance->elements_manager->add_category(
			'xs-wpsocial-login',
			[
				'title' =>esc_html__( 'Social Login', 'wslu-social-login' ),
				'icon' => 'fa fa-plug',
			],
			1
		);
    }

     /**
     * Method Name: register_widget
     * Details : Register widget of Elementor Controls
     * 
     * @since 1.0.0
     * @access public
     */

    public function register_widget($widgets_manager){
        foreach($this->active_widgets as $widget){
			require(WSLU_LOGIN_PLUGIN.'/inc/elementor/widgets/'.$widget.'.php');
			$class_name = '\Elementor'. DIRECTORY_SEPARATOR . ucfirst($widget);
            $widgets_manager->register_widget_type(new $class_name());
        }
    }
	
	public static function instance(){
        if (!self::$instance){
            self::$instance = new self();
        }
        return self::$instance;
    }

}

