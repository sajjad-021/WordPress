<?php
if(!class_exists('MBP_Post_Type_Google_Subposts'))
{
	class MBP_Post_Type_Google_Subposts
	{
		const POST_TYPE	= 'mbp-google-subposts';


    	public function __construct(){
    	
    		add_action('init', array(&$this, 'init'));
    		//add_action('admin_init', array(&$this, 'admin_init'));
    	}


    	public function init(){
    		$this->create_post_type();
    	}
		
		
		public function create_post_type(){			
			register_post_type(self::POST_TYPE,
				apply_filters('mbp_subpost_type_settings', array(
					'public' => false,
					'exclude_from_search' => true,
					'publicly_queryable' => false,
					'show_in_nav_menus' => false,
					'can_export' => true
				))
			);			
		}
	}
} 
?>