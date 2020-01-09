<?php
namespace XsSocialShare;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
* Class Name : Share;
* Class Details : this class for showing login button in login and register page for wp, woocommerce, buddyPress and others
* 
* @params : void
* @return : void
*
* @since : 1.0
*/

Class Share{
	
	private $app_key = ['c5752d2f90b7c95dd6fcf1ffc82a8fbb68d8c9e8', '1934f519a63e142e0d3c893e59cc37fe0172e98a'];
	private $api_url = 'https://api.sharedcount.com/v1.0/?url=%s&apikey=%s';
	
	public function __construct( $load = true){
		if( $load ){
			//add_filter( 'plugin_row_meta', array( $this, 'xs_plugin_row_meta' ), 10, 2 );
			
			add_shortcode('xs_social_share', [$this, 'social_share_shortcode'] );
			
			add_action('the_content', [ $this, 'share_the_body_content' ] );
			//add_action('wp_body_open', [ $this, 'share_the_body_content_body' ] );
			
			add_action('wp_footer', [ $this, 'share_the_body_content_body' ] );
			
		}
	}
	
	/*
	 body and content
	*/
	public function share_the_body_content( $content = ''){
		$style = get_option( 'xs_style_setting_data_share' , '');
		$content_position = isset($style['login_content']) ? $style['login_content'] :  'left_content';
		
		if(in_array($content_position, ['after_content', 'before_content']) ){
			$getContent = $this->get_share_data('all', ['class' => $content_position]);
			if($content_position == 'after_content'){
				return $content.$getContent;
			}else if($content_position == 'before_content'){
				return $getContent.$content;
			}
			
		}
		return $content;
	}
	
	public function share_the_body_content_body( ){
		$style = get_option( 'xs_style_setting_data_share' , '');
		$content_position = isset($style['login_button_content']) ? $style['login_button_content'] :  'left_content';
		
		if(in_array($content_position, [ 'left_content', 'right_content', 'top_content', 'bottom_content' ]) ){
			echo $this->get_share_data('all', ['class' => $content_position]);
		}
	}
	
	public function social_share_action( $post_url = '', $id_post = 0){
		$option_key 	= 'xs_share_providers_data';
		$xsc_options	 = get_option( $option_key ) ? get_option( $option_key ) : [];
		$share_provider = isset($xsc_options['social']) ? $xsc_options['social'] : '';
		
		if(empty($post_url) || $id_post == 0){
			return '';
		}
		$cache = 12 ;
		
		$api_key_set   = 'c5752d2f90b7c95dd6fcf1ffc82a8fbb68d8c9e8';
		
		$get_transient   = get_transient( 'xs_share_data__'.$id_post );
		$get_transient_time   = get_transient( 'timeout_xs_share_data__'.$id_post );
		
		$prev_data	 = get_post_meta( $id_post, 'xs_share_data__', true ) ? get_post_meta( $id_post, 'xs_share_data__', true ) : [];
		
		
		if($get_transient_time > time() ){
			return '';
		}
		
		$url = sprintf($this->api_url, $post_url, $api_key_set);
		
		$get_request = wp_remote_get( $url , [] );
		$request = wp_remote_retrieve_body( $get_request );
		$api_call = @json_decode( $request , true );
		
		$return = []; $xsc_transient = [];
		if( is_array($share_provider) && sizeof($share_provider) > 0):
			foreach( $share_provider AS $k=>$v):
				if( isset($v['enable']) ){
					$before_data = isset($prev_data[$k]) ? $prev_data[$k] : 0;
					if( !empty($get_transient[$k]) ){
						$result = $get_transient[$k];
					}else{
						if( $k == 'facebook' ){
							$result = isset($api_call['Facebook']['share_count']) ? $api_call['Facebook']['share_count'] : $before_data;
						}else if( $k == 'pinterest' ){
							$result = isset($api_call['Pinterest']) ? $api_call['Pinterest'] : $before_data;
						}else if( $k == 'linkedin' ){
							$result = isset($api_call['LinkedIn']) ? $api_call['LinkedIn'] : $before_data;
						}else if( $k == 'stumbleUpon' ){
							$result = isset($api_call['StumbleUpon']) ? $api_call['StumbleUpon'] : $before_data;
						}else{
							$result = 0;
						}
						
					}
					$return[$k] = $result;
					$xsc_transient[$k] = $result;
				}
			endforeach;
		endif;
		update_post_meta( $id_post, 'xs_share_data__', $return );
		set_transient( 'xs_share_data__'.$id_post, $xsc_transient , $cache*60*60 );
		
	}
	public function social_share_shortcode( $atts , $content = null){
		$atts = shortcode_atts(
								array(
										'provider' => 'all',
										'class' => '',
										'style' => '',
										'columns' => '',
										'box_only' => '',
									), $atts, 'xs_social_share' 
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
		
		return $this->get_share_data($provider, $config);
	}
	
	public function get_share_data($provider = 'all', $config = []){
		$core_provider = $this->social_share_link();
		
		$className = isset($config['class']) ? $config['class'] : '';
		
		$provider = ($provider == 'all') ? array_keys( $core_provider ) : $provider;
		
		$style = get_option( 'xs_style_setting_data_share' , '');
		$styleConfig = isset($style['login_button_style']) ? $style['login_button_style'] :  'wslu-share-box-shaped  wslu-fill-colored wslu-share-horizontal';
		
		$widget_style = isset($config['style']) ? $config['style'] : '';
		$widget_style = ( strlen($widget_style) > 0  ) ? $widget_style : $styleConfig;
		
		$columns = isset($config['columns']) ? $config['columns'] : '';
		
		$xsc_options	 = get_option( 'xs_share_providers_data' ) ? get_option( 'xs_share_providers_data' ) : [];
		$counter_provider = isset($xsc_options['social']) ? $xsc_options['social'] : [];
		
		
		$global_data = get_option( 'xs_share_global_setting_data' ) ? get_option( 'xs_share_global_setting_data' ) : [];
		$global_data = isset($global_data['global']) ? $global_data['global'] : [];
		
		
		global $currentUrl, $title,$author, $details, $source, $media, $app_id;
		global $post;
		
		$currentUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		if(is_object($post) && isset($post->ID)){
			$currentUrl = get_permalink();
			
			// action save post
			$this->social_share_action(get_permalink($post->ID), $post->ID);
			
		}
		$postId = isset($post->ID) ? $post->ID : 0;
		
		$post_meta = get_post_meta( $postId, 'xs_share_data__' );
		//echo '<pre>';print_r($post_meta);echo '</pre>';
		
		$current_id = get_current_user_id();
		//$user = get_userdata( $current_id );
		$author = 'xpeedstudio';
		/*if(is_object($user)){
			$author = isset($user->data->user_nicename) ? $user->data->user_nicename : '';
		}*/
		$details = '';
		if(isset($post->post_excerpt) && strlen($post->post_excerpt) > 2){
			$details = $post->post_excerpt;
		}
		$title = get_the_title();
		
		$source = get_bloginfo();
		$thumbnail_src = wp_get_attachment_image_src( get_post_thumbnail_id( $postId ), 'full' );
		
		$custom_logo_id = get_theme_mod( 'custom_logo' );
		$image = wp_get_attachment_image_src( $custom_logo_id , 'full' );
		$customLogo = isset($image[0]) ? $image[0] : '';
		
		$media = isset($thumbnail_src[0]) ? $thumbnail_src[0] : $customLogo;
		$app_id = '463603197734720';
		
		ob_start();
		require( WSLU_LOGIN_PLUGIN . '/template/share/share-html.php');
		$counter = ob_get_contents();
		ob_end_clean();
		// object end here
		return $counter;
	}
	
	
	public function social_share_link(){
		$link = [];
		//$link['link'] = [ ];
		$link['facebook'] = ['label' => 'Facebook',  'url' => 'http://www.facebook.com/sharer.php', 'params' => [ 'u' => '[%url%]', 't' => '[%title%]', 'v' => 3] ];
		$link['twitter'] = [ 'label' => 'Twitter', 'url' => 'https://twitter.com/intent/tweet', 'params' => [ 'text' => '[%title%] [%url%]', 'original_referer' => '[%url%]', 'related' => '[%author%]'] ];
		$link['linkedin'] = [ 'label' => 'LinkedIn', 'url' => 'https://www.linkedin.com/shareArticle', 'params' => [ 'url' => '[%url%]', 'title' => '[%title%]', 'summary' => '[%details%]', 'source' => '[%source%]', 'mini' => true] ];
		$link['pinterest'] = [ 'label' => 'Pinterest', 'url' => 'https://pinterest.com/pin/create/button/', 'params' => [ 'url' => '[%url%]', 'media' => '[%media%]', 'description' => '[%details%]'] ];
		$link['facebook-messenger'] = [ 'label' => 'Facebook Messenger', 'url' => 'https://www.facebook.com/dialog/send', 'params' => [ 'link' => '[%url%]', 'redirect_uri' => '[%url%]', 'display' => 'popup', 'app_id' => '[%app_id%]' ] ];
		$link['kik'] = [ 'label' => 'Kik', 'url' => 'https://www.kik.com/send/article/', 'params' => [ 'url' => '[%url%]', 'text' => '[%details%]', 'title' => '[%title%]' ] ];
		$link['skype'] = [ 'label' => 'Skype', 'url' => 'https://web.skype.com/share', 'params' => [ 'url' => '[%url%]'] ];
		$link['trello'] = [ 'label' => 'Trello', 'url' => 'https://trello.com/add-card', 'params' => [ 'url' => '[%url%]', 'name' => '[%title%]', 'desc' => '[%details%]', 'mode' => 'popup'] ];
		$link['viber'] = [ 'label' => 'Viber', 'url' => 'viber://forward', 'params' => [ 'text' => '[%title%] [%url%]'] ];
		$link['whatsapp'] = [ 'label' => 'WhatsApp', 'url' => 'whatsapp://send', 'params' => [ 'text' => '[%title%] [%url%]'] ];
		$link['telegram'] = [ 'label' => 'Telegram', 'url' => 'https://telegram.me/share/url', 'params' => [ 'url' => '[%url%]', 'text' => '[%title%]'] ];
		$link['email'] = [ 'label' => 'Email', 'url' => 'mailto:', 'params' => [ 'body' => 'Title: [%title%] \n\n URL: [%url%]', 'subject' => '[%title%]']];
		$link['reddit'] = [ 'label' => 'Reddit', 'url' => 'http://reddit.com/submit', 'params' => [ 'url' => '[%url%]', 'title' => '[%title%]']];
		$link['digg'] = [ 'label' => 'Digg', 'url' => 'http://digg.com/submit', 'params' => [ 'url' => '[%url%]', 'title' => '[%title%]', 'phase' => 2]];
		$link['stumbleupon'] = [ 'label' => 'StumbleUpon', 'url' => 'http://www.stumbleupon.com/submit', 'params' => [ 'url' => '[%url%]', 'title' => '[%title%]']];
		return $link;
	}	
	

	public function xs_plugin_row_meta(  $links, $file ){
		  if ( strpos( $file, 'wp-social.php' ) !== false ) {
			$new_links = array(
				'demo' => '<a href="#" target="_blank"><span class="dashicons dashicons-welcome-view-site"></span>Live Demo</a>',
				'doc' => '<a href="#" target="_blank"><span class="dashicons dashicons-media-document"></span>User Guideline</a>',
				'support' => '<a href="https://help.wpmet.com/" target="_blank"><span class="dashicons dashicons-admin-users"></span>Support</a>',
				'pro' => '<a href="#" target="_blank"><span class="dashicons dashicons-cart"></span>Premium version</a>'
			);
			$links = array_merge( $links, $new_links );
		}
		return $links;
	}
	
	
}

New \XsSocialShare\Share();