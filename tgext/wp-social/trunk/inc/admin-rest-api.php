<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
* Function Name : xs_return_call_back_login_function();
* Function Details : WP Rest API.
* 
* @params : void
* @return : array() 
*
* @since : 1.0
*/
if(!function_exists('xs_return_call_back_login_function')){
	function xs_return_call_back_login_function(WP_REST_Request $request ){
		$param = $request['data'];
		if(is_null($param)){
			$typeSocial = '';
		}
		
		$typeSocial = $param;
		$socialType = '';
		$callBackUrl = '';
		
		require_once( WSLU_LOGIN_PLUGIN.'/inc/admin-create-user.php' );
		die();
	}
}

/**
* wp rest api add action 
*/
add_action( 'rest_api_init', function () {
  register_rest_route( 'wslu-social-login', '/type/(?P<data>\w+)/',
	array(
		'methods' => 'GET',
		'callback' => 'xs_return_call_back_login_function',
	  ) 
  );
} );

