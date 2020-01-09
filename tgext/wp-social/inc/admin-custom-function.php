<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
* Variable Name : $getLogoutUrl;
* Variable Details : User logout and redirect current url
* 
* @params : String() $_GET['XScurrentPageLog']. Get data from URL
*
* @since : 1.0
*/
if(isset($_GET['loggedout']) AND isset($_GET['XScurrentPageLog'])){
	$getLogoutUrl = $_GET['XScurrentPageLog'];
	if ( wp_redirect( $getLogoutUrl ) ) {
		exit;
	}
}


/**
* Function Name : xs_services_provider();
* Function Details : Set Social Providers.
* 
* @params : void
* @return : array() 
*
* @since : 1.0
*/
if(!function_exists('xs_services_provider')){
	function xs_services_provider(){
		return ['facebook' => 'Facebook', 'google' => 'Google', 'linkedin' => 'LinkedIn', 'twitter' => 'Twitter', 'dribbble' => 'Dribbble', 'instagram' => 'Instagram', 'github' => 'GitHub', 'wordpress' => 'WordPress', 'vkontakte' => 'Vkontakte', 'reddit' => 'Reddit'];
	}
}

/**
* Function Name : xs_current_url_custom();
* Function Details : Set current url with HTTPS | HTTP.
* 
* @params : void
* @return : String() Current URL when Current URL != base URL
*
* @since : 1.0
*/
if(!function_exists('xs_current_url_custom')){
	function xs_current_url_custom(){	
		$current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	   if(get_site_url().'/' === $current_url){
		   $current_url = '';
	   }
	   return trim($current_url);
	}
}

/**
* Function Name : xs_create_dynamic_shortcode();
* Function Details : Create shortcode dynamic . if you provide or not
* 
* @params : String $atts. if you provide Exam: provider="facebook,twitter,github"
* @params : String $btn-text. if you provide Exam: btn-text="Login with Facebook"
* @params : String $class Set Class. class="test-class" 
* @return : String Output
*
* @since : 1.0
*/
if(!function_exists('xs_create_dynamic_shortcode')){
	function xs_create_dynamic_shortcode( $atts , $content = null) {
		$atts = shortcode_atts(
								array(
										'provider' => 'all',
										'btn-text' => $content,
										'class' => '',
									), $atts, 'xs_social_login' 
							);

		if(strlen(trim($atts['provider'])) > 0){
			$typeSocial = explode(',', $atts['provider']);
		}else{
			$typeSocial = array('all');
		}
		
		$className = $atts['class'];
		$btnText = $atts['btn-text'];
		return xs_social_login_shortcode_widget($typeSocial, $btnText, 'show', $className);
	}
}

/**
* Function Name : xs_social_login_shortcode_widget();
* Function Details : Create shortcode button from template page .
* 
* @params : String $atts. if you provide Exam: provider="facebook,twitter,github"
* @params : String $btn_content. if you provide Exam: btn-text="Login with Facebook"
* @params : String $typeCurrent. Current URL add or remove from redirect URL
* @params : String $className Set Class. class="test-class" 
* @return : String Output of button
*
* @since : 1.0
*/
if(!function_exists('xs_social_login_shortcode_widget')){
	function xs_social_login_shortcode_widget( $attr, $btn_content = null , $typeCurrent = 'show', $className = '') {
		$correntUrl 	  = '';
		$provider_data = get_option('xs_provider_data');
		$style_data = get_option('xs_style_setting_data');
		// object start here
		ob_start();
		require( WSLU_LOGIN_PLUGIN . '/template/login-btn-html.php');
		$buttonData = ob_get_contents();
		ob_end_clean();
		// object end here
		return $buttonData;
	}

}

/**
* Function Name : xs_my_login_stylesheet();
* Function Details : Added style and script page in wp-login.php page
* 
* @params : void
* @return : link page 
*
* @since : 1.0
*/
if(!function_exists('xs_my_login_stylesheet')){
	function xs_my_login_stylesheet() {
		wp_enqueue_script( 'xs_login_custom_login_js', WSLU_LOGIN_PLUGIN_URL. 'assets/js/login-page/font-login-page.js' );
	}
}

if(!function_exists('xs_my_global_stylesheet')){
	function xs_my_global_stylesheet() {
		wp_enqueue_style( 'xs_login_custom_login_css', WSLU_LOGIN_PLUGIN_URL. 'assets/css/login-page/font-login-page.css');
		wp_enqueue_style( 'xs_login_font_login_css', WSLU_LOGIN_PLUGIN_URL. 'assets/css/font-icon.css');
	}
}

