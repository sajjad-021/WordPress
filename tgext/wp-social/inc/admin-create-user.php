<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
* session stat for current redirect URL after login from social.
*
* @since : 1.0
*/
session_start();
	
if(isset($_GET['XScurrentPage']) AND strlen($_GET['XScurrentPage']) > 2){
	$_SESSION['xs_social_login_ref_url']  = $_GET['XScurrentPage'];
}

/**
* Variable Name: $currentURL
* Variable Details: get Current URL from session data after login by social 
*
* @since : 1.0
*/
$currentURL = isset($_SESSION['xs_social_login_ref_url']) ? $_SESSION['xs_social_login_ref_url'] : get_site_url();

/**
* Wp Function: is_user_logged_in();
* Function Details: check user login. If user is login after redirect current URL by $currentURL
*
* @since : 1.0
*/

if(is_user_logged_in()){
	if ( wp_redirect( $currentURL ) ) {
		exit;
	}
}

/**
* Variable Name : $xs_config
* Variable Type : Array
*
* @since : 1.0
*/
$xs_config = [];
if(strlen($typeSocial) > 0){
	/**
	* Variable Name : $provider_data
	* Variable Type : Array
	* @return : array() $provider_data .  Get array from socail provider data "xs_provider_data"
	*
	* @since : 1.0
	*/
	$provider_data = get_option('xs_provider_data');
	/**
	* Variable Name : $callBackUrl
	* Variable Type : String
	* Variable Details : Create dynamic callback URL for all social services.
	*
	* @since : 1.0
	*/
	$callBackUrl = get_site_url().'/wp-json/wslu-social-login/type/'.$typeSocial;
	/**
	* Variable Name : $serviceType
	* Variable Type : Array
	* @return : array() xs_services_provider().  Get array from custom function page "admin-custom-function.php" - "xs_services_provider()"
	*
	* @since : 1.0
	*/
	$serviceType = xs_services_provider();
	
	/**
	* check array key from $serviceType by social type . For Example: facebook
	*
	* @since : 1.0
	*/
	if(array_key_exists($typeSocial, $serviceType)){
		$socialType = $serviceType[$typeSocial];
	}
	
	/**
	* API configration for Facebook, Twitter, Linkedin, Dribble, Pinterest, Wordpress, Instagram, GitHub, Vkontakte and Reddit
	*
	* @since : 1.0
	*/
	
	/**
	* Set callback URL in array "$xs_config" for configration API
	*
	* @since : 1.0
	*/
	$xs_config['callback'] = $callBackUrl;
	
	/**
	* Create array for API Providers for all service using foreach by variable "$serviceType"
	*
	* @since : 1.0
	*/
	foreach($serviceType AS $serviceKey=>$serviceValue):
		$idData = 'id';
		if($serviceKey == 'twitter'){
			$idData = 'key';
		}
		$xs_config['providers'][$serviceValue] = [
													'enabled' => true,
													'keys' => [
																	$idData => isset($provider_data[$serviceKey]['id']) ? $provider_data[$serviceKey]['id'] : '',
																	'secret' => isset($provider_data[$serviceKey]['secret']) ? $provider_data[$serviceKey]['secret'] : '' 
																]
												];
	endforeach;
}

/**
* Config API 
*
* @since : 1.0
*/
$code = isset($_GET['code']) ? $_GET['code'] : '';
if(strlen($socialType) > 0){
	
	try{
	    $hybridauth = new Hybridauth\Hybridauth($xs_config);

	    $adapter = $hybridauth->authenticate($socialType); 

	    $isConnected = $adapter->isConnected();
	 	if($isConnected):
			$getProfile = $adapter->getUserProfile();
			
			if(is_object($getProfile) AND sizeof($getProfile) > 0){
				session_unset($_SESSION['xs_social_login_ref_url']);
				
				$display_name = '';
				$user_name 	= '';
				$user_email = '';
				$insertData = [];
				if( isset($getProfile->firstName) ){
					$insertData['user_nicename'] = $getProfile->firstName;
					$user_name 	.= str_replace(' ', '-', strtolower(trim($getProfile->firstName)));
				}
				if(isset($getProfile->identifier)){
					$user_name 	.= $getProfile->identifier.'-';
				}
				$user_name 	.= $typeSocial;
				
				if( isset($getProfile->displayName) ){
					$insertData['display_name'] = $getProfile->displayName;
				}

				if( isset($getProfile->email) ){
					$user_email =  $getProfile->email;
					$insertData['user_email'] = $user_email;
				}

				$insertData['user_login'] = $user_name;
				$insertData['user_pass'] = wp_hash_password('123456');

				$user_id = username_exists( $user_name );
				
				/**
				* Variable Name : $setting_data
				* Variable Type : Array
				* @return : array() $setting_data .  Get array from socail global setting data "xs_global_setting_data"
				*
				* @since : 1.0
				*/
				$setting_data = get_option('xs_global_setting_data');
				
				/**
				* Variable Name : $redirectUrlEnable
				* Variable Type : int()
				* @return : int $redirectUrlEnable Enable 0,1
				*
				* @since : 1.0
				*/
				$redirectUrlEnable = isset($setting_data['custom_login_url']['enable']) ? $setting_data['custom_login_url']['enable'] : 0;

				/**
				* Variable Name : $redirectUrl
				* Variable Type : string()
				* @return : string $redirectUrl custom URL
				*
				* @since : 1.0
				*/
				$redirectUrl = isset($setting_data['custom_login_url']['data']) ? $setting_data['custom_login_url']['data'] : $currentURL;
				
				
				/**
				* check user already exists
				*
				* @since : 1.0
				*/
				if ( $user_id ) {
					$user_nameD = xs_login_get_user_data($user_name, 'user_login');
					$user_id = xs_login_get_user_data($user_name, 'ID');
					wp_set_password('123456', $user_id);
					$password = '123456';
					
					if(xs_user_login($user_nameD, $password)){
						if($redirectUrlEnable == 1){
							if ( wp_redirect( $redirectUrl ) ) {
								exit;
							}
						}else{
							if ( wp_redirect( $currentURL ) ) {
								exit;
							}
						}
					}else{
						die('System Error for Login!');
					}
				/**
				* when user already exixts then direct login by this user
				*
				* @since : 1.0
				*/	
				}else{
					$checkUser = xs_login_create_user($insertData);
					if($checkUser == 0){
						// exits user data new
						if(strlen($user_email) == 0){
							$user_nameD 	= xs_login_get_user_data($user_name, 'user_login');
							$user_id 	= xs_login_get_user_data($user_name, 'ID');
						}else{
							$user_nameD 	= xs_login_get_user_data_email($user_email, 'user_login');
							$user_id 	= xs_login_get_user_data_email($user_email, 'ID');
						}
						wp_set_password('123456', $user_id);
						$password = '123456';
						if(xs_user_login($user_nameD, $password)){
							if($redirectUrlEnable == 1){
								if ( wp_redirect( $redirectUrl ) ) {
									exit;
								}
							}else{
								if ( wp_redirect( $currentURL ) ) {
									exit;
								}
							}
						}
					}else{
						// insert user data new.
						wp_set_password('123456', $checkUser);
						$password = '123456';
						if(xs_user_login($user_name, $password)){
							if($redirectUrlEnable == 1){
								if ( wp_redirect( $redirectUrl ) ) {
									exit;
								}
							}else{
								if ( wp_redirect( $currentURL ) ) {
									exit;
								}
							}
						}
					}
					
					
				}	
			}else{
				die('System Error for Callback!');
			}	
		
		endif;
		
	    $adapter->disconnect();
	}
	catch(\Exception $e){
	    echo 'Oops, we ran into an issue! ' . $e->getMessage();
	}
	

}

/**
* Function Name : xs_login_create_user();
* Function Details : create new user from socail login and check enable wp new create new users.
* 
* @params : array() $userdata. For user information
* @return : int() if success then user id else 0
*
* @since : 1.0
*/

function xs_login_create_user( $userdata){
	/*$getPermissionRegisterWP = get_option('users_can_register', 0);
	 if($getPermissionRegisterWP == 0){
		return 0;
	 }*/
	$user_id = wp_insert_user( $userdata ) ;
	if ( ! is_wp_error( $user_id ) ) {
		return $user_id;
	}else{
		return 0;
	}
}	

add_action('init', 'xs_login_create_user');

/**
* Function Name : xs_login_get_user_data();
* Function Details : Get user information when user already exists into database
* 
* @params : String() $loginName. User login name
* @return : String() User information by set filed from database table.
*
* @since : 1.0
*/
function xs_login_get_user_data($loginName, $getFiled = 'user_login'){
	$users = get_user_by('login', $loginName);
	if(empty($users)){
		return '';
	}
	return $users->data->$getFiled;
}

add_action('init', 'xs_login_get_user_data');

/**
* Function Name : xs_login_get_user_data_email();
* Function Details : Get user information when email already exists into database
* 
* @params : String() $email. User login name
* @return : String() User information by set filed from database table.
*
* @since : 1.0
*/
function xs_login_get_user_data_email($email, $getFiled = 'user_login'){
	$users = get_user_by('email', $email);
	if(empty($users)){
		return '';
	}
	return $users->data->$getFiled;
}

add_action('init', 'xs_login_get_user_data');
/**
* Function Name : xs_user_login();
* Function Details : User login function by wp_signon();
* 
* @params : String() $user_name. User login name
* @params : String() $password. User password
* @return : True | False
*
* @since : 1.0
*/
function xs_user_login($user_name,  $password){
	 if(strlen($user_name) == 0){
		 die('User name is empty!');
	 }
	 if(strlen($password) == 0){
		 die('User password is empty!');
	 }
	 $credentials = array();
	 $credentials['user_login'] = $user_name;
	 $credentials['user_password'] = $password;
	return wp_signon($credentials);
}

add_action('init', 'xs_user_login');

