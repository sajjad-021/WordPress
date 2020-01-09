<?php

if ( !class_exists( 'MBP_Admin_Page_Settings' ) ) {
    class MBP_Admin_Page_Settings
    {
        const  SETTINGS_PAGE = 'post_to_google_my_business_settings' ;
        private  $settings_api ;
        protected  $plugin ;
        public function __construct( MBP_Plugin $plugin, MBP_WeDevs_Settings_API $settings_api )
        {
            $this->settings_api = $settings_api;
            $this->plugin = $plugin;
        }
        
        public function init()
        {
            add_action( 'admin_init', array( &$this, 'admin_init' ) );
            add_action( 'admin_menu', array( &$this, 'add_menu' ) );
            add_action( 'wp_ajax_mbp_get_businesses', array( &$this, 'get_businesses_ajax' ) );
        }
        
        public function admin_init()
        {
            $this->settings_api->set_sections( $this->get_settings_sections() );
            $this->settings_api->set_fields( $this->get_settings_fields() );
            $this->settings_api->admin_init();
            add_action( 'wsa_form_top_mbp_google_settings', array( &$this, 'google_form_top' ) );
            add_action( 'wsa_form_top_mbp_quick_post_settings', array( &$this, 'quick_post_top' ) );
            add_action( 'admin_enqueue_scripts', array( &$this, 'enqueue_scripts' ) );
            if ( !mbp_fs()->can_use_premium_code() ) {
                add_action( 'wsa_form_bottom_mbp_post_type_settings', array( &$this, 'post_type_bottom' ) );
            }
            //add_action('wsa_form_bottom_mbp_google_settings', array(&$this, 'google_form_bottom'));
        }
        
        public function enqueue_scripts( $hook )
        {
            /* selective loading of JS has issues when the plugin is translated
            			if(!in_array($hook, array(
            						'toplevel_page_post_to_google_my_business', 
            						'post-to-gmb_page_post_to_google_my_business_settings'
            					)
            				)
            			){
            				return;
            			}
            			
            			$screen = get_current_screen();
            			if(!is_object($screen)){
            				return;
            			}
            			*/
            wp_enqueue_script(
                'mbp-settings-page',
                plugins_url( '../js/mbp-admin-page-settings.js', __FILE__ ),
                array( 'jquery' ),
                $this->plugin->version(),
                true
            );
        }
        
        function get_current_setting( $option, $section, $default = '' )
        {
            $options = get_option( $section );
            if ( isset( $options[$option] ) ) {
                return $options[$option];
            }
            return $default;
        }
        
        public function get_settings_page()
        {
            return self::SETTINGS_PAGE;
        }
        
        function get_settings_sections()
        {
            $sections = array( array(
                'id'    => 'mbp_google_settings',
                'title' => __( 'Google settings', 'post-to-google-my-business' ),
            ), array(
                'id'    => 'mbp_quick_post_settings',
                'title' => __( 'Auto-post settings', 'post-to-google-my-business' ),
            ), array(
                'id'    => 'mbp_post_type_settings',
                'title' => __( 'Post type settings', 'post-to-google-my-business' ),
            ) );
            /*
            //if(mbp_fs()->is_plan_or_trial__premium_only('pro')){
            	$sections[] = array(
            		'id'    => 'mbp_post_type_settings',
            		'title' => __('Post type settings', 'post-to-google-my-business')	
            	);
            //}
            */
            return $sections;
        }
        
        function get_settings_fields()
        {
            $fields = array(
                'mbp_google_settings'     => array( array(
                'name'     => 'google_location',
                'label'    => __( 'Default location', 'post-to-google-my-business' ),
                'desc'     => __( 'Select the post-types where the GMB metabox should be displayed', 'post-to-google-my-business' ),
                'callback' => array( &$this, 'settings_field_google_business' ),
            ) ),
                'mbp_quick_post_settings' => array( array(
                'name'              => 'template',
                'label'             => __( 'Quick post template', 'post-to-google-my-business' ),
                'desc'              => sprintf( __( 'The template for new Google posts when using quick post. Supports <a target="_blank" href="%s">variables</a> and <a target="_blank" href="%s">spintax</a> (premium only)', 'post-to-google-my-business' ), 'https://tycoonmedia.net/blog/using-the-quick-publish-feature/', 'https://tycoonmedia.net/blog/using-spintax/' ),
                'type'              => 'textarea',
                'sanitize_callback' => array( &$this, 'validate_quick_post_template' ),
                'default'           => __( 'New post: %post_title% - %post_content%', 'post-to-google-my-business' ),
            ), array(
                'name'    => 'cta',
                'label'   => __( 'Default call to action', 'post-to-google-my-business' ),
                'desc'    => __( 'The default button text', 'post-to-google-my-business' ),
                'type'    => 'select',
                'default' => 'LEARN_MORE',
                'options' => array(
                'NONE'       => __( 'No button', 'post-to-google-my-business' ),
                'BOOK'       => __( 'Book', 'post-to-google-my-business' ),
                'ORDER'      => __( 'Order', 'post-to-google-my-business' ),
                'SHOP'       => __( 'Shop', 'post-to-google-my-business' ),
                'LEARN_MORE' => __( 'Learn more', 'post-to-google-my-business' ),
                'SIGN_UP'    => __( 'Sign up', 'post-to-google-my-business' ),
                'GET_OFFER'  => __( 'Get offer', 'post-to-google-my-business' ),
                'CALL'       => __( 'Call Now', 'post-to-google-my-business' ),
            ),
            ), array(
                'name'  => 'invert',
                'label' => __( 'Post to GMB by default', 'post-to-google-my-business' ),
                'desc'  => __( 'Invert the function of the "Auto-post" checkbox. Your posts will be automatically published to GMB, unless you tell it not to.', 'post-to-google-my-business' ),
                'type'  => 'checkbox',
            ) ),
            );
            return $fields;
        }
        
        public function settings_field_google_user( $args )
        {
            $value = esc_attr( $this->settings_api->get_option( $args['id'], $args['section'], $args['std'] ) );
            $size = ( isset( $args['size'] ) && !is_null( $args['size'] ) ? $args['size'] : 'regular' );
            echo  sprintf(
                '<select class="%1$s" name="%2$s[%3$s]" id="%2$s[%3$s]">',
                $size,
                $args['section'],
                $args['id']
            ) ;
            
            if ( $this->plugin->is_configured() ) {
                $api = MBP_api::getInstance();
                $accounts = $api->get_accounts();
                
                if ( $accounts && count( $accounts->accounts ) >= 1 ) {
                    echo  sprintf( '<option disabled selected value>%s</option>', esc_html__( 'Select a user or location group', 'post-to-google-my-business' ) ) ;
                    foreach ( $accounts->accounts as $account ) {
                        
                        if ( $account->name == $value ) {
                            $selected = true;
                        } else {
                            $selected = false;
                        }
                        
                        echo  sprintf(
                            '<option value="%s"%s>%s</option>',
                            $account->name,
                            ( $selected ? ' selected="selected"' : '' ),
                            $account->accountName
                        ) ;
                    }
                } else {
                    echo  sprintf( '<option disabled selected value>%s</option>', esc_html__( 'No user accounts found', 'post-to-google-my-business' ) ) ;
                }
            
            } else {
                echo  sprintf( '<option disabled selected value>%s</option>', esc_html__( 'Connect your Google account first.', 'post-to-google-my-business' ) ) ;
            }
            
            echo  '</select>' ;
        }
        
        public function settings_field_google_business( $args )
        {
            $value = $this->settings_api->get_option( $args['id'], $args['section'], $args['std'] );
            $name = sprintf( '%1$s[%2$s]', $args['section'], $args['id'] );
            //$user = $this->get_current_setting('google_user', 'mbp_google_settings');
            ?>
				<div class="mbp-info mbp-location-blocked-info">
					<strong><?php 
            _e( 'Location grayed out?', 'post-to-google-my-business' );
            ?></strong>
					<?php 
            _e( 'It means the location is blocked from using the LocalPostAPI, and can\'t be posted to using the plugin.', 'post-to-google-my-business' );
            ?>
					<a href="https://wordpress.org/plugins/post-to-google-my-business/#why%20is%2Fare%20my%20location(s)%20grayed%20out%3F"><?php 
            _e( 'Learn more...', 'post-to-google-my-business' );
            ?></a>				
				</div>

				<?php 
            echo  $this->plugin->business_selector( $name, $value ) ;
            echo  $this->plugin->business_selector_options( null ) ;
            if ( $this->plugin->is_configured() ) {
                echo  '<br /><a class="button" href="#" id="refresh-api-cache">' . esc_html__( 'Refresh locations', 'post-to-google-my-business' ) . '</a>' ;
            }
            ?>
					<br /><br />
				<?php 
            echo  $this->plugin->message_of_the_day() ;
        }
        
        public function add_menu()
        {
            /*
            add_options_page(
            	esc_html__('My Business Post settings', 'post-to-google-my-business'), 
            	esc_html__('My Business Post', 'post-to-google-my-business'), 
            	'manage_options', 
            	'post_to_google_my_business', 
            	array(&$this, 'admin_page') 
            );
            */
            add_menu_page(
                __( 'Post to Google My Business settings', 'post-to-google-my-business' ),
                __( 'Post to GMB', 'post-to-google-my-business' ),
                'publish_posts',
                'post_to_google_my_business',
                array( &$this, 'admin_page' ),
                MBP_Plugin::dashicon()
            );
            add_submenu_page(
                'post_to_google_my_business',
                __( 'Post to Google My Business settings', 'post-to-google-my-business' ),
                __( 'Settings', 'post-to-google-my-business' ),
                'manage_options',
                $this::SETTINGS_PAGE,
                array( &$this, 'admin_page' )
            );
        }
        
        public function is_configured()
        {
            
            if ( $this->plugin->is_configured() ) {
                return sprintf( '<br /><span class="dashicons dashicons-yes"></span> %s<br /><br />', __( 'Connected', 'post-to-google-my-business' ) );
            } else {
                return sprintf( '<br /><span class="dashicons dashicons-no"></span> %s<br /><br />', __( 'Not connected', 'post-to-google-my-business' ) );
            }
        
        }
        
        public function admin_page()
        {
            if ( !current_user_can( 'manage_options' ) ) {
                wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
            }
            include plugin_dir_path( __FILE__ ) . '../templates/settings.php';
        }
        
        public function google_form_top()
        {
            echo  $this->is_configured() ;
            echo  $this->auth_urls() ;
            echo  '<br /><br />' ;
        }
        
        public function post_type_bottom()
        {
            echo  sprintf( __( 'Support for other post types is a <a href="%s">Pro feature</a>.', 'post-to-google-my-business' ), mbp_fs()->get_upgrade_url() ) ;
        }
        
        public function quick_post_top()
        {
            //echo __('Quick post allows you to create posts on Google My Business based on the template below.', 'post-to-google-my-business');
        }
        
        public function auth_urls()
        {
            $configured = $this->plugin->is_configured();
            echo  sprintf(
                '<a href="%s" class="button%s">%s</a>',
                esc_url( admin_url( 'admin-post.php?action=mbp_generate_url' ) ),
                ( $configured ? '' : '-primary' ),
                ( $configured ? esc_html__( 'Reconnect to Google My Business', 'post-to-google-my-business' ) : esc_html__( 'Connect to Google My Business', 'post-to-google-my-business' ) )
            ) ;
            
            if ( $configured ) {
                echo  sprintf( '<br /><br /><a href="%s">%s</a>', esc_url( admin_url( 'admin-post.php?action=mbp_disconnect' ) ), esc_html__( 'Disconnect this website from Google My Business', 'post-to-google-my-business' ) ) ;
                echo  '<br /><br />' ;
                echo  sprintf( '<a href="%s">%s</a>', esc_url( admin_url( 'admin-post.php?action=mbp_revoke' ) ), esc_html__( 'Revoke Google account authorization', 'post-to-google-my-business' ) ) ;
            }
        
        }
        
        public function get_businesses_ajax()
        {
            //$user = sanitize_text_field($_POST['user_id']);
            $refresh = ( $_POST['refresh'] == "true" ? true : false );
            //$selected = sanitize_text_field($_POST['selected']);
            echo  $this->plugin->business_selector(
                'mbp_google_settings[google_location]',
                null,
                null,
                true
            ) ;
            wp_die();
        }
        
        public function validate_quick_post_template( $value )
        {
            
            if ( empty($value) ) {
                add_settings_error(
                    'template',
                    'mbp_quick_post_error',
                    'The quick post template can not be empty',
                    'error'
                );
                return 'New post: %post_title% - %post_content%';
            }
            
            return $value;
        }
    
    }
}