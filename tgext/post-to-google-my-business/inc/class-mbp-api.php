<?php 
if(!class_exists('MBP_api')){
	class MBP_api{
		//const API_URL = 'https://util.tycoonmedia.net/api/v1/';
		const CACHE_DURATION = 86400; //60*60*24
		const ERROR_DURATION = 86400; //60*60*24
		
		private static $instance;
		
		private $api_key;
		private $site_key;
		private $token;
		private $api_url;
		
		private function __construct(){
			$this->api_key 	= get_option('mbp_api_key');
			$this->site_key = get_option('mbp_site_key');
			$this->token 	= get_option('mbp_api_token');
			$this->api_url 	= apply_filters('mbp_api_url', 'https://util.tycoonmedia.net/api/v1/');
		}
		
		
		public static function getInstance(){
			if(!isset(self::$instance)){
				self::$instance = new self();
			}
			return self::$instance;
		}
		
		
		protected function do_get($endpoint, $args = array()){
			$args['apiKey'] = $this->site_key;
			$args['token'] = $this->token;
			$args['timeout'] = 20;
			$url = add_query_arg($args, $this->api_url.$endpoint);
			$response = wp_remote_get($url);
			
			if(is_wp_error($response)){
				$error_message = $response->get_error_message();
				set_transient('mbp_api_error', $error_message, self::ERROR_DURATION);
				return $error_message;
			}else{			
				$data = json_decode(wp_remote_retrieve_body($response));
				if(!isset($data->error)){
					return $data;
				}else{
					if(is_object($data->error)){
						$error = nl2br($this->format_google_error($data));
					}else{
						$error = (string)$data->error;
					}
					set_transient('mbp_api_error', $error, self::ERROR_DURATION);
					return $error;
				}				
			}
		}
		
		
		protected function do_post($endpoint, $args = array(), $json = false, $method = 'POST'){
			$query_args['apiKey'] = $this->site_key;
			$query_args['token'] = $this->token;
			$url = add_query_arg($query_args, $this->api_url.$endpoint);
			
			$response = wp_remote_post($url, 
				array(
					'timeout'	=> 20,
					'method' 	=> $method,
					'headers'	=> $json ? array('Content-Type' => 'application/json') : null,
					'body' 		=> $json ? json_encode($args) : $args
				)
			);

			if(is_wp_error($response)){
				$error_message = $response->get_error_message();
				set_transient('mbp_api_error', $error_message, self::ERROR_DURATION);	
				return $error_message;
			}else{
				$data = json_decode($response['body']);
				if(!isset($data->error)){					
					return $data;
				}else{
					if(is_object($data->error)){
						$error_message = nl2br($this->format_google_error($data));

					}else{
						$error_message = (string)$data->error;
					}
					set_transient('mbp_api_error', $error_message, self::ERROR_DURATION);
					return $error_message;
				}
			}				
		}

		public function format_google_error($data){
			$error = (string)$data->error->message."\n\n";
			if(isset($data->error->details)){
					foreach($data->error->details as $detail){
							if(is_array($detail->errorDetails)){
									foreach($detail->errorDetails as $errordetail){
											$error .= (string)$errordetail->message."\n\n";
									}
							}
					}	
			}			
			return 'Google API Error(s): '.$error;
		}
		
		public function get_accounts($refresh = false){
			$cached = get_transient('mbp_get_accounts_cache');
			
			if($cached && !$refresh){
				return $cached;
			}				
				
			$accounts = $this->do_get('google/get_accounts/');
			set_transient('mbp_get_accounts_cache', $accounts, self::CACHE_DURATION);
			return $accounts;
		}
		
		
		public function get_locations($account_name, $refresh = false){
			if(!$account_name){ return false; }
			$transient_name = 'mbp_get_locations_cache_'.str_replace("/", "_", $account_name);
			$cached = get_transient($transient_name);
			
			if($cached && !$refresh){
				return apply_filters('mbp_get_locations', (object)$cached);
			}
				
			$locations = array();
			$nextPageToken = false;
			do{			
				$args = array(
					'account_name' => $account_name,
					'nextPageToken' => $nextPageToken
				);
				$query = $this->do_get('google/get_locations_v4/', $args);
				$nextPageToken = isset($query->nextPageToken) ? $query->nextPageToken : false;
				$locations = array_merge($locations, $query->locations);
			}while($nextPageToken);	
			
			$locations = array('locations' => $locations);
			
			set_transient($transient_name, $locations, self::CACHE_DURATION);
			
			return apply_filters('mbp_get_locations', (object)$locations);
		}
		
		public function get_location($location_name, $refresh = false){
			$transient_name = 'mbp_get_location_cache_'.str_replace("/", "_", $location_name);
			$cached = get_transient($transient_name);
			
			if($cached && !$refresh){
				return $cached;
			}							
				
			$location = $this->do_get('google/get_location/', array('location_name' => $location_name));
			set_transient($transient_name, $location, self::CACHE_DURATION);
			return $location;
		}
		
		public function create_post($company_id, $args){
			$args['company_id'] = $company_id;
			return $this->do_post('google/create_post/', $args, true);
		}
		
		
		public function delete_post($post_id){		
			return $this->do_post('google/delete_post/', array('post_id' => $post_id));
		}
		
		
		public function update_post($post_id, $args, $mask){
			$args['post_id'] = $post_id;
			$args['mask'] = $mask;
			return $this->do_post('google/update_post/', $args, true);
		}
		
		
		public function refresh_token(){
			$result = $this->do_get('refreshtoken/');
			$this->token = $result->token;
			return $result->token;
		}
		
		
		public function revoke_access(){
			return $this->do_get('google/revoke_access/');
		}
		
		
		public function get_post_stats($company_id, $post_names = array(), $refresh = false){
			$cached = get_transient('mbp_post_stats');
			
			$startTime = new DateTime();
			$startTime->sub(new DateInterval('P7D'));
			$endTime = new Datetime();
			
			if($cached && !$refresh){
				return $cached;
			}	
			
			$args = array(
				'company_id'		=> $company_id, 
				'localPostNames'	=> $post_names,
				'basicRequest'		=> array(
					'metricRequests'	=> array(
						'metric'			=> 'ALL'
					),
					'timeRange'			=> array(
						'startTime'			=> $startTime->format(DateTime::ATOM),
						'endTime'			=> $endTime->format(DateTime::ATOM)
					)
				)
			);
			return $this->do_post('google/get_post_stats/', $args, true);
		}
	}
}
?>