<?php
namespace Elementor;
if ( ! defined( 'ABSPATH' ) ) die( 'Forbidden' );


use \XsSocialCounter\Settings as Settings;
use \XsSocialShare\Share as SharePro;

Class Share extends Widget_Base {

    public $base;

    public function get_name() {
        return 'xs-wpsocial-share-button';
    }

    public function get_title() {
        return esc_html__( 'Share Button', 'wslu-social-login' );
    }

    public function get_icon() {
        return 'eicon-button';
    }

    public function get_categories() {
        return ['xs-wpsocial-login'];
    }

	public function __style(){
		return Settings::$share_style;
	}
	
	public function __share_provider(){
		$obj = New SharePro(false);
		
		$link = $obj->social_share_link();
		$provider = [];
		foreach($link as $k=>$v):
			$provider[$k] = isset($v['label']) ? $v['label'] : '';
		endforeach;
		
		return $provider;
	}
     
    protected function _register_controls() {

        // content of listing
		$this->start_controls_section(
			'__social_login_providers',
			array(
				'label' => esc_html__( 'Providers', 'wslu-social-login' ),
			)
        );
		$this->add_control(
			'provider_styles',
			[
				'label' => __( 'Select Providers', 'wslu-social-login' ),
				'type' => Controls_Manager::SELECT2,
				'multiple' => true,
				'options' => $this->__share_provider(),
				'default' =>  '',
			]
        );
		 $this->add_control(
			'select_styles',
			[
				'label' => __( 'Select Donate', 'wslu-social-login' ),
				'type' => Controls_Manager::SELECT,
				'multiple' => false,
				'options' => $this->__style(),
				'default' =>  '',
			]
        );
		
		$this->add_control(
			'custom_class',
			[
				'label' => __( 'Custom Class', 'wslu-social-login' ),
				'type' => Controls_Manager::TEXT,
				'default' =>  '',
			]
        );
		
        $this->end_controls_section();
		
		 $this->start_controls_section(
			'__social_login_providers_styles',
			array(
                'label' => esc_html__( 'Providers', 'wslu-social-login' ),
                'tab' => Controls_Manager::TAB_STYLE,
			)
        );
		$this->add_group_control(
            Group_Control_Typography::get_type(), [
                'name'       => '__social_login_providers_typograghy',
                'selector'   => '{{WRAPPER}} .xs_social_share_widget .xs_share_url > li > a .xs-social-icon span',
            ]
        );
        $this->end_controls_section();
	


    }
     // render files
     protected function render( ) {
        $settings = $this->get_settings_for_display();
        extract($settings);
	    $provider = 'all';
		if( !empty($provider_styles)){
			$provider = array_values($provider_styles);
		}
		
	    $attr['class'] = $custom_class;
		if(!empty($select_styles) ){
			$attr['style'] = $select_styles;
		}
		if( function_exists('__wp_social_share_pro_check') ){
			if( __wp_social_share_pro_check() ){
				echo __wp_social_share( $provider, $attr );
			}
		}
		
        

     }
}

