<?php
namespace XsSocialCounter;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
* Class Name : XS_Social_Counter;
* Class Details : this class for showing login button in login and register page for wp, woocommerce, buddyPress and others
* 
* @params : void
* @return : void
*
* @since : 1.0
*/
use \XsSocialCounter\Settings as Settings;

Class Counter{
	
	public function __construct( $load = true){
		if($load){
			$this->social_counter_action();
			add_action('init', [$this, 'counter_access_key_setup']);
			add_shortcode('xs_social_counter', [$this, 'social_counter_shortcode'] );
		}
	}
	
	public function social_counter_action(){
		$option_key 	= 'xs_counter_providers_data';
		$xsc_options	 = get_option( $option_key ) ? get_option( $option_key ) : [];
		$counter_provider = isset($xsc_options['social']) ? $xsc_options['social'] : [];
		
		$option_key_display 	= 'xs_counter_global_setting_data';
		$counter_options	 	= get_option( $option_key_display ) ? get_option( $option_key_display ) : [];
		if( isset( $counter_options['global']['cache'] ) && is_int( $counter_options['global']['cache'] )){
			$cache = (int) $counter_options['global']['cache'];
			$cache = ($cache == 0) ? 12 : $cache;
		}else{	
			$cache = 12 ;
		}
		
		// counters timeout set
		$get_transient_time   = get_transient( 'timeout_xs_counters_data' );
		if( $get_transient_time > time() ){
			return '';
		}
		
		
		$return = []; $xsc_transient = [];
		
		if(is_array($counter_provider) && sizeof($counter_provider) > 0){
			foreach( $counter_provider AS $k=>$v):
				if( isset($v['enable']) ){
					$function = 'xsc_'.$k.'_count';
					$return['data'][$k] = $function();
					$xsc_transient[$k] = $return['data'][$k];
				}
			endforeach;
		}
		set_transient( 'xs_counters_data', $xsc_transient , $cache*60*60 );
		update_option( 'xs_counter_options' , $return );
	}
	
	public function social_counter_shortcode( $atts , $content = null){
		$atts = shortcode_atts(
								array(
										'provider' => 'all',
										'class' => '',
										'style' => '',
										'columns' => '',
										'box_only' => '',
									), $atts, 'xs_social_counter' 
							);

		if(isset($atts['provider']) && $atts['provider'] != 'all'){
			$provider = explode(',', $atts['provider']);
		}else{
			$provider = 'all';
		}
		
		$config = [];
		$config['class'] = $atts['class'];
		$config['style'] = $atts['style'];
		$config['columns'] = $atts['columns'];
		
		return $this->get_counter_data($provider, $config);
	}
	
	public function get_counter_data($provider = 'all', $config = []){
		$core_provider = $this->xs_counter_providers();
		
		$className = isset($config['class']) ? $config['class'] : '';
		
		$provider = ($provider == 'all') ? array_keys( $core_provider ) : $provider;
		
		$style = get_option( 'xs_style_setting_data_counter' , '');
		$styleConfig = isset($style['login_button_style']) ? $style['login_button_style'] :  'wslu-counter-box-shaped wslu-counter-fill-colored';
		
		$widget_style = isset($config['style']) ? $config['style'] : '';
		$widget_style = ( strlen($widget_style) > 0  ) ? $widget_style : $styleConfig;
		
		$columns = isset($config['columns']) ? $config['columns'] : '';
		
		$counter_data	 = get_option( 'xs_counter_options' ) ? get_option( 'xs_counter_options' ) : [];
		$counter_data = isset($counter_data['data']) ? $counter_data['data'] : [];
		
		$xsc_options	 = get_option( 'xs_counter_providers_data' ) ? get_option( 'xs_counter_providers_data' ) : [];
		$counter_provider = isset($xsc_options['social']) ? $xsc_options['social'] : [];
		
		$global_data = get_option( 'xs_counter_global_setting_data' ) ? get_option( 'xs_counter_global_setting_data' ) : [];
		$global_data = isset($global_data['global']) ? $global_data['global'] : [];
		
		ob_start();
		require( WSLU_LOGIN_PLUGIN . '/template/counter/counter-html.php');
		$counter = ob_get_contents();
		ob_end_clean();
		// object end here
		return $counter;
	}
	
	public function counter_access_key_setup(){
		
		if(isset($_POST['xs_provider_submit_form_access_counter'])){
			
			$getpage = isset($_GET['page']) ? Settings::sanitize($_GET['page']) : '';
			$getType = isset($_GET['xs_access']) ? Settings::sanitize($_GET['xs_access']) : '';
			
			if($getpage != 'wslu_counter_setting'){
				return '';
			}
			
			$accesskey = isset($_POST['accesskey']) ? Settings::sanitize($_POST['accesskey']) : '';
			$app_id = isset($accesskey[$getType]['app_id']) ? $accesskey[$getType]['app_id'] : '';
			$app_secret = isset($accesskey[$getType]['app_secret']) ? $accesskey[$getType]['app_secret'] : '';
			
			if($getType == 'twitter'){
				// preparing credentials
				$credentials = $app_id . ':' . $app_secret;
				$toSend 	 = base64_encode($credentials);

				// http post arguments
				$args = array(
					'method'      => 'POST',
					'httpversion' => '1.1',
					'blocking' 		=> true,
					'headers' 		=> array(
						'Authorization' => 'Basic ' . $toSend,
						'Content-Type' 	=> 'application/x-www-form-urlencoded;charset=UTF-8'
					),
					'body' 				=> array( 'grant_type' => 'client_credentials' )
				);

				add_filter('https_ssl_verify', '__return_false');
				$response = wp_remote_post('https://api.twitter.com/oauth2/token', $args);

				$keys = json_decode(wp_remote_retrieve_body($response));
				if(!isset($keys->access_token)){
					return '';
				}
				if( !empty($keys->access_token) ) {
					update_option('xs_counter_'.$getType.'_token', $keys->access_token);
					update_option('xs_counter_'.$getType.'_app_id', $app_id);
					update_option('xs_counter_'.$getType.'_app_secret', $app_secret);
					
					echo "<script type='text/javascript'>window.location='".admin_url()."admin.php?page=wslu_counter_setting&tab=wslu_providers&xs_access=".$getType."';</script>";
					exit;
				}
			}else if( $getType == 'instagram'){
				$cur_page =  admin_url().'admin.php?page=wslu_counter_setting&tab=wslu_providers&xs_access='.$getType.'' ;
				
				$params = array(
					'client_id'     => $app_id,
					'response_type' => 'code',
					'scope'         => 'basic',
					'redirect_uri'  => $cur_page,
				);
				
				$url = "https://api.instagram.com/oauth/authorize/?" . http_build_query($params);
				
				set_transient( 'xs_counter_'.$getType.'_client_id', $app_id, 60*60 );
				set_transient( 'xs_counter_'.$getType.'_client_secret', $app_secret, 60*60 );
				header( "Location: $url" );
				
			}else if( $getType == 'linkedin'){
				$cur_page =  admin_url().'admin.php?page=wslu_counter_setting&tab=wslu_providers&xs_access='.$getType.'';
				$params = [
					'response_type' => 'code',
					'client_id'     => $app_id,
					//'scope'         => 'rw_company_admin r_basicprofile',
					'scope'         => 'r_liteprofile r_emailaddress w_member_social r_ad_campaigns rw_organization',
					'state'         => uniqid( '', true ), // unique long string
					'redirect_uri'  => $cur_page,
				];

				$url = 'https://www.linkedin.com/oauth/v2/authorization?' . http_build_query($params);
				
				set_transient( 'xs_counter_'.$getType.'_api_key', $app_id, 	60*60 );
				set_transient( 'xs_counter_'.$getType.'_secret_key', $app_secret, 60*60 );
				
				header( "Location: $url" );
			}else if($getType == 'facebook'){
				$url = 'https://www.facebook.com/login.php?skip_api_login=1&api_key=1203050406491591&signed_next=1&next=https://www.facebook.com/v2.12/dialog/oauth?redirect_uri=https%3A%2F%2Fwww.ajuda.me%2Fwp-login.php%3FloginSocial%3Dfacebook
				&display=popup&state=d4a4c6d6df98117acfa25d4343483c69&scope=public_profile%2Cemail&response_type=code&client_id=1203050406491591&ret=login&logger_id=49a9a593-e908-a451-16d4-eb38a4ae7882&cancel_url=https://www.ajuda.me/wp-login.php?loginSocial=facebook&error=access_denied&error_code=200&error_description=Permissions+error&error_reason=user_denied&state=d4a4c6d6df98117acfa25d4343483c69#_=_&display=popup&locale=pt_PT&logger_id=49a9a593-e908-a451-16d4-eb38a4ae7882';
				
				$cur_page =  admin_url().'admin.php?page=wslu_counter_setting&tab=wslu_providers&xs_access='.$getType.'';
				
				$params = [
					'skip_api_login' => 1,
					'api_key' => 1203050406491591,
					'signed_next' => 1,
					'next' => 'https://www.facebook.com/v2.12/dialog/oauth?redirect_uri='.$cur_page,
					'display' => 'popup',
					'response_type' => 'code',
					'client_id'     => $app_id,
					'scope'         => 'public_profile email',
					'ret'         => 'login',
					'logger_id'         => '49a9a593-e908-a451-16d4-eb38a4ae7882',
					'cancel_url'         => 'https://www.ajuda.me/wp-login.php?loginSocial=facebook&error=access_denied&error_code=200&error_description=Permissions+error&error_reason=user_denied&state=d4a4c6d6df98117acfa25d4343483c69#_=_&display=popup&locale=pt_PT&logger_id=49a9a593-e908-a451-16d4-eb38a4ae7882',
					'state'         => uniqid( '', true ), // unique long string
					'redirect_uri'  => $cur_page,
				];
				$url = 'https://www.facebook.com/login.php?' . http_build_query($params);
				header( "Location: $url" );
				
			}else if( $getType == 'dribbble'){
				$cur_page =  admin_url().'admin.php?page=wslu_counter_setting&tab=wslu_providers&xs_access='.$getType.'' ;
				$params = array(
					'client_id'     => $app_id,
					'response_type' => 'code',
					'scope'         => 'public',
					'redirect_uri'  => $cur_page,
					'state'  => substr(md5(microtime()),rand(0,26),10),
				);
				
				$url = "https://dribbble.com/oauth/authorize?" . http_build_query($params);
				
				set_transient( 'xs_counter_'.$getType.'_client_id', $app_id, 60*60 );
				set_transient( 'xs_counter_'.$getType.'_client_secret', $app_secret, 60*60 );
				
				header( "Location: $url" );
				
			}
		}
	}
	
	
	public function xs_counter_providers(){
		return [
				'facebook'   => [ 'label' => 'Facebook', 'data' => ['text' => __( 'Fans', 'wslu-social-login' ), 'url' => 'http://www.facebook.com/%s']  ],
				'twitter'    => [ 'label' => 'Twitter', 'data' => ['text' => __( 'Followers', 'wslu-social-login' ), 'url' => 'http://twitter.com/%s']  ],
				//'linkedin'   => [ 'label' => 'LinkedIn', 'data' => ['text' => __( 'Followers', 'wslu-social-login' ), 'url' => 'https://www.linkedin.com/%s/%s']  ],
				'pinterest'  => [ 'label' => 'Pinterest', 'data' => ['text' => __( 'Followers', 'wslu-social-login' ), 'url' => 'http://www.pinterest.com/%s']  ],
				'dribbble'   => [ 'label' => 'Dribbble', 'data' => ['text' => __( 'Followers', 'wslu-social-login' ), 'url' => 'http://dribbble.com/%s']  ],
				'instagram'  => [ 'label' => 'Instagram', 'data' => ['text' => __( 'Followers', 'wslu-social-login' ), 'url' => 'http://instagram.com/%s']  ],
				'youtube'    => [ 'label' => 'Youtube', 'data' => ['text' => __( 'Subscribers', 'wslu-social-login' ), 'url' => 'http://youtube.com/%s/%s']  ],
				//'vimeo'      => [ 'label' => 'Vimeo', 'data' => ['text' => __( 'Subscribers',	'wslu-social-login' ), 'url' => 'https://vimeo.com/channels/%s']  ],
				'mailchimp'  => [ 'label' => 'Mailchimp', 'data' => ['text' => __( 'Subscribers',	'wslu-social-login' )]  ],
				//'vkontakte'  => [ 'label' => 'Vkontakte', 'data' => ['text' => __( 'Members', 'wslu-social-login' ), 'url' => 'http://vk.com/%s']  ],
				'comments'   => [ 'label' => 'Comments', 'data' => ['text' => __( 'Comments', 'wslu-social-login' )]  ],
				'posts'      => [ 'label' => 'Posts', 'data' => ['text' => __( 'Posts', 'wslu-social-login' )]  ],
				
			];
	}
	
	public function xs_counter_providers_data(){
		return [
			'facebook' => [ 'id' => [ 'type' => 'normal', 'label' => 'Page ID/Name', 'input' => 'text'],],
			'twitter' => [ 'id' => [ 'type' => 'normal', 'label' => 'UserName', 'input' => 'text'],  'api' => ['type' => 'access', 'label' => 'Access Token Key(optional)', 'input' => 'text', 'filed' => ['app_id' => 'Consumer key', 'app_secret' => 'Consumer secret'] ] ],
			'instagram' => [ 'id' => [ 'type' => 'normal', 'label' => 'UserName', 'input' => 'text'],  'api' => ['type' => 'access', 'label' => 'Access Token Key(optional)', 'input' => 'text', 'filed' => ['app_id' => 'Client ID', 'app_secret' => 'Client Secret'] ] ],
			'linkedin' => [ 'type' => [ 'type' => 'normal', 'label' => 'Account Type', 'input' => 'select', 'data' => [ 'Company' => 'Company', 'Profile' => 'Profile']], 'id' => [ 'type' => 'normal', 'label' => 'Your ID', 'input' => 'text'], 'api' => ['type' => 'access', 'label' => 'Access Token Key(optional)', 'input' => 'text', 'filed' => ['app_id' => 'API Key', 'app_secret' => 'Secret Key'] ] ],
			'pinterest' => [ 'username' => [ 'type' => 'normal', 'label' => 'UserName', 'input' => 'text'],],
			'youtube' => [ 'type' => [ 'type' => 'normal', 'label' => 'Account Type', 'input' => 'select', 'data' => [ 'Channel' => 'Channel', 'User' => 'User']], 'id' => [ 'type' => 'normal', 'label' => 'Username or Channel ID', 'input' => 'text'], 'key' => [ 'type' => 'normal', 'label' => 'Youtube API Key(optional)', 'input' => 'text'] ],
			'dribbble' => [ 'id' => [ 'type' => 'normal', 'label' => 'UserName', 'input' => 'text'],  'api' => ['type' => 'access', 'label' => 'Access Token Key(optional)', 'input' => 'text', 'filed' => ['app_id' => 'Client ID', 'app_secret' => 'Client Secret'] ] ],
			'mailchimp' => [ 'id' => [ 'type' => 'normal', 'label' => 'List ID (Optional)', 'input' => 'text'],  'api' => [ 'type' => 'normal', 'label' => 'API Key', 'input' => 'text'] ],
		];
	}
	
	public function xs_counter_defalut_providers(){
		if( !get_option( 'xs_counter_active' ) ){
			$default_data = [
				'social' => $this->xs_counter_providers(),
				'cache' => 5
			];

			update_option( 'xs_counter_providers_data',  $default_data);
			update_option( 'xs_counter_active', WSLU_VERSION );
		}
	}
}

New \XsSocialCounter\Counter();

