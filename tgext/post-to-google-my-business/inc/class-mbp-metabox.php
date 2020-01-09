<?php

/*
"mbp_post_image": "",
    "undefined": "",
    "mbp_event_title": "",
    "mbp_event_start_date": "",
    "mbp_event_end_date": "",
    "mbp_offer_title": "",
    "mbp_offer_start_date": "",
    "mbp_offer_end_date": "",
    "mbp_offer_coupon": "",
    "mbp_offer_redeemlink": "",
    "mbp_offer_terms": "",
    "mbp_product_name": "",
    "mbp_product_price": "",
    "mbp_product_minprice": "",
    "mbp_product_maxprice": "",
    "mbp_product_pricerange": "1",
    "mbp_button": "1",
    "mbp_button_url": "",
    "mbp_schedule": "1",
    "mbp_scheduled_date": "",
    "mbp_repost": "1",
    "mbp_reposts": "0"
*/
if ( !class_exists( 'MBP_Metabox' ) ) {
    class MBP_Metabox
    {
        protected  $settings ;
        protected  $plugin ;
        private  $_meta = array() ;
        private  $_posttypes = array() ;
        public function __construct( MBP_Plugin $plugin, MBP_Admin_Page_Settings $settings )
        {
            $this->settings = $settings;
            $this->plugin = $plugin;
            add_action( 'init', array( &$this, 'init' ) );
            add_action( 'admin_init', array( &$this, 'admin_init' ) );
        }
        
        public function init()
        {
            add_action(
                'save_post',
                array( &$this, 'do_quickpost' ),
                10,
                3
            );
            //add_action('transition_post_status', array(&$this, 'post_status_transition'), 10, 3);
            add_action( 'wp_ajax_mbp_new_post', array( &$this, 'ajax_create_post' ) );
            add_action( 'wp_ajax_mbp_load_post', array( &$this, 'ajax_load_post' ) );
            add_action( 'wp_ajax_mbp_delete_post', array( &$this, 'ajax_delete_post' ) );
            add_action( 'wp_ajax_mbp_edit_post', array( &$this, 'ajax_edit_post' ) );
            add_action( 'before_delete_post', array( &$this, 'delete_subposts' ) );
            $this->_meta = apply_filters( 'mbp_meta_fields', array(
                'mbp_topic_type',
                'mbp_existing_post',
                'mbp_attachment_type',
                'mbp_post_attachment',
                'mbp_post_text',
                'mbp_event_title',
                'mbp_event_start_date',
                'mbp_event_end_date',
                'mbp_offer_title',
                'mbp_offer_coupon',
                'mbp_offer_redeemlink',
                'mbp_offer_terms',
                'mbp_button',
                'mbp_button_url',
                'mbp_schedule',
                'mbp_scheduled_date',
                'mbp_repost',
                'mbp_reposts'
            ) );
            $this->_posttypes = apply_filters( 'mbp_post_types', array( 'post' ) );
        }
        
        public function get_gmb_subposts_for_post( $parent_post_id )
        {
            return get_children( [
                'post_parent' => $parent_post_id,
                'post_type'   => MBP_Post_Type_Google_Subposts::POST_TYPE,
            ] );
        }
        
        public function delete_subposts( $post_id )
        {
            foreach ( $this->get_gmb_subposts_for_post( $post_id ) as $subpost ) {
                wp_delete_post( $subpost->ID, true );
            }
        }
        
        public function admin_init()
        {
            add_action( 'add_meta_boxes', array( &$this, 'add_meta_boxes' ) );
            add_action( 'admin_enqueue_scripts', array( &$this, 'enqueue_metabox_scripts' ) );
            //add_action('post_submitbox_misc_actions', array(&$this, 'quick_publish_checkbox'));
        }
        
        //To upgrade from older version of the plugin
        /**
         * @param $post_id
         */
        public function upgrade_meta( $post_id )
        {
            if ( !get_post_meta( $post_id, 'mbp_create_post', true ) ) {
                return;
            }
            $posts = get_post_meta( $post_id, 'mbp_posts', true );
            if ( $posts && !is_array( $posts ) ) {
                $posts = array( $posts );
            }
            //Convert single posts (v.1.0) to array
            foreach ( $posts as $post ) {
                $image = get_post_meta( $post_id, 'mbp_post_image', true );
                $location = $post['location'];
                $api = MBP_api::getInstance();
                $location_info = $api->get_location( $location );
                $args = array(
                    'post_type'   => MBP_Post_Type_Google_Subposts::POST_TYPE,
                    'post_parent' => $post_id,
                    'meta_input'  => array(
                    'mbp_location'     => array(
                    'id'   => $location,
                    'name' => $location_info->locationName,
                ),
                    'mbp_created_time' => time(),
                    'mbp_form_fields'  => array(
                    'mbp_post_text'       => get_post_meta( $post_id, 'mbp_post_text', true ),
                    'mbp_topic_type'      => 'STANDARD',
                    'mbp_attachment_type' => ( $image ? 'PHOTO' : false ),
                    'mbp_post_attachment' => ( $image ? $image : '' ),
                    'mbp_scheduled_date'  => false,
                ),
                    'mbp_name'         => $post['name'],
                    'mbp_searchUrl'    => $post['url'],
                ),
                );
                
                if ( get_post_meta( $post_id, 'mbp_event', true ) ) {
                    $start_date = DateTime::createFromFormat( 'Y-m-d H:i', get_post_meta( $post_id, 'mbp_event_start_date', true ) );
                    $end_date = DateTime::createFromFormat( 'Y-m-d H:i', get_post_meta( $post_id, 'mbp_event_end_date', true ) );
                    $args['meta_input']['mbp_form_fields']['mbp_event_title'] = get_post_meta( $post_id, 'mbp_event_title', true );
                    $args['meta_input']['mbp_form_fields']['mbp_event_start_date'] = $start_date->format( 'Y-m-d H:i e' );
                    $args['meta_input']['mbp_form_fields']['mbp_event_end_date'] = $end_date->format( 'Y-m-d H:i e' );
                    $args['meta_input']['mbp_form_fields']['mbp_topic_type'] = 'EVENT';
                }
                
                
                if ( get_post_meta( $post_id, 'mbp_button', true ) ) {
                    $args['meta_input']['mbp_form_fields']['mbp_button'] = true;
                    $args['meta_input']['mbp_form_fields']['mbp_button_type'] = get_post_meta( $post_id, 'mbp_button_type', true );
                    $args['meta_input']['mbp_form_fields']['mbp_button_url'] = get_post_meta( $post_id, 'mbp_button_url', true );
                }
                
                wp_insert_post( $args, true );
            }
            $delete_metas = array(
                'mbp_create_post',
                'mbp_posts',
                'mbp_post_image',
                'mbp_post_text',
                'mbp_event',
                'mbp_event_start_date',
                'mbp_event_end_date',
                'mbp_button',
                'mbp_button_type',
                'mbp_button_url'
            );
            foreach ( $delete_metas as $meta ) {
                delete_post_meta( $post_id, $meta );
            }
        }
        
        public function enqueue_metabox_scripts( $hook )
        {
            if ( !in_array( $hook, array( 'post.php', 'post-new.php' ) ) ) {
                return;
            }
            $screen = get_current_screen();
            if ( !is_object( $screen ) || !in_array( $screen->post_type, $this->_posttypes ) ) {
                return;
            }
            wp_enqueue_media();
            wp_enqueue_script(
                'mbp-metabox',
                plugins_url( '../js/mbp-metabox.js', __FILE__ ),
                array( 'jquery' ),
                $this->plugin->version(),
                true
            );
            $localize_vars = array(
                'post_id'              => get_the_ID(),
                'post_nonce'           => wp_create_nonce( 'mbp_post_nonce' ),
                'publish_confirmation' => __( "You're working on a Google My Business post, but it has not yet been published/scheduled. Press OK to publish/schedule it now, or Cancel to save it as a draft.", 'post-to-google-my-business' ),
                'please_wait'          => __( "Please Wait...", 'post-to-google-my-business' ),
                'publish_button'       => __( 'Publish', 'post-to-google-my-business' ),
                'update_button'        => __( 'Update', 'post-to-google-my-business' ),
                'draft_button'         => __( 'Save draft', 'post-to-google-my-business' ),
                'schedule_post'        => __( 'Schedule post', 'post-to-google-my-business' ),
            );
            wp_localize_script( 'mbp-metabox', 'mbp_localize_script', $localize_vars );
        }
        
        public function add_meta_boxes()
        {
            foreach ( $this->_posttypes as $posttype ) {
                add_meta_box(
                    sprintf( 'my_business_post_%s_section', $posttype ),
                    __( 'Post to Google My Business', 'post-to-google-my-business' ),
                    array( &$this, 'add_inner_meta_boxes' ),
                    $posttype
                );
            }
        }
        
        public function add_inner_meta_boxes( $post )
        {
            
            if ( $this->settings->get_current_setting( 'google_location', 'mbp_google_settings' ) ) {
                $this->upgrade_meta( $post->ID );
                ob_start();
                require_once dirname( __FILE__ ) . '/../templates/metabox.php';
                $output = ob_get_contents();
                ob_end_clean();
                echo  apply_filters( 'mbp_metabox_template', $output, $post ) ;
            } else {
                echo  sprintf( '<a href="%s">', esc_url( admin_url( 'admin.php?page=' . $this->settings->get_settings_page() ) ) ) ;
                _e( 'Please configure Post to Google My Business first', 'post-to-google-my-business' );
                echo  '</a> ' ;
                _e( '(Connect, pick a default location and Save Changes)' );
            }
        
        }
        
        public function quick_publish_checkbox()
        {
            if ( !in_array( get_post_type(), $this->_posttypes ) ) {
                return;
            }
            if ( get_post_meta( get_the_ID(), 'mbp_autopost_created', true ) ) {
                return false;
            }
            $invert = $this->settings->get_current_setting( 'invert', 'mbp_quick_post_settings', false );
            wp_nonce_field( 'mbp_quick_publish', 'mbp_quick_publish_nonce' );
            ?>
				<div class="misc-pub-section misc-pub-section-last mbp-autopost-checkbox-container">
					<input type="hidden" value="1" name="mbp_wp_post" />
					<label><input type="checkbox" id="mbp_create_post" value="1" name="mbp_create_post" <?php 
            checked( $this->is_autopost_checkbox_checked( get_the_ID() ) );
            ?>/>
                        <?php 
            
            if ( !$invert || $invert == 'off' ) {
                _e( 'Auto-post this to GMB', 'post-to-google-my-business' );
            } else {
                _e( 'Do NOT auto-post this to GMB', 'post-to-google-my-business' );
            }
            
            ?>
                    </label>
				</div>
				<?php 
        }
        
        public function is_wp_post_submission()
        {
            return isset( $_POST['mbp_wp_post'] );
        }
        
        public function is_autopost_checkbox_checked( $post_id )
        {
            $submitted = $this->is_wp_post_submission();
            $checked = ( isset( $_POST['mbp_create_post'] ) && $_POST['mbp_create_post'] ? true : false );
            
            if ( $submitted && $checked ) {
                update_post_meta( $post_id, 'mbp_autopost_checked', true );
                return true;
            } elseif ( $submitted && !$checked ) {
                update_post_meta( $post_id, 'mbp_autopost_checked', false );
                return false;
            }
            
            if ( !$submitted && get_post_meta( $post_id, 'mbp_autopost_checked', true ) ) {
                return true;
            }
            return false;
        }
        
        public function do_quickpost( $post_id, $post, $update )
        {
            $quickpost = $this->is_autopost_checkbox_checked( $post_id );
            //if(wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)){ return false; } 	// This doesnt work reliably
            if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
                return false;
            }
            if ( $post->post_status != 'publish' ) {
                return false;
            }
            //Prevents double post
            if ( !in_array( $post->post_type, $this->_posttypes ) ) {
                return false;
            }
            remove_action( 'save_post', array( &$this, 'do_quickpost' ), 10 );
            //to prevent infinite loop
            $invert = $this->settings->get_current_setting( 'invert', 'mbp_quick_post_settings', false );
            //Check whether the function of the checkbox should be inverted
            $invert = ( !$invert || $invert == 'off' ? false : true );
            if ( $invert ) {
                $quickpost = !$quickpost;
            }
            //Invert the value of the checkbox if it should
            if ( !$quickpost ) {
                return false;
            }
            //Finally decide if we should create a post or not
            if ( get_post_meta( $post_id, 'mbp_autopost_created', true ) ) {
                return false;
            }
            //Dont post if already done so
            $api = MBP_api::getInstance();
            $template = $this->settings->get_current_setting( 'template', 'mbp_quick_post_settings', 'New post: %post_title% - %post_content%' );
            if ( empty($template) ) {
                $template = 'New post: %post_title% - %post_content%';
            }
            $summary = $this->parse_placeholder_variables( $template, $post_id );
            $summary = html_entity_decode( strip_tags( $summary ) );
            $summary = preg_replace( "~(?:\\[/?)[^\\]]+/?\\]~s", '', $summary );
            //Strip shortcodes
            $summary = substr( $summary, 0, 1500 );
            $cta = $this->settings->get_current_setting( 'cta', 'mbp_quick_post_settings', 'LEARN_MORE' );
            $location = $this->settings->get_current_setting( 'google_location', 'mbp_google_settings' );
            $location_info = $api->get_location( $location );
            $gmb_post_args = array(
                'languageCode' => get_bloginfo( 'language' ),
                'summary'      => $summary,
                'topicType'    => 'STANDARD',
            );
            
            if ( $cta != 'NONE' ) {
                $gmb_post_args['callToAction'] = array(
                    'actionType' => $cta,
                );
                if ( $cta != 'CALL' ) {
                    $gmb_post_args['callToAction']['url'] = get_permalink( $post_id );
                }
            }
            
            $thumbnail = get_the_post_thumbnail_url( $post_id, 'large' );
            
            if ( $thumbnail ) {
                $media = array(
                    'media' => array(
                    'mediaFormat' => 'PHOTO',
                    'sourceUrl'   => $thumbnail,
                ),
                );
                $gmb_post_args = array_merge( $gmb_post_args, $media );
            }
            
            $gmb_post_args = apply_filters( 'mbp_autopost_post_args', $gmb_post_args, $location );
            $gmb_post = $api->create_post( $location, $gmb_post_args );
            
            if ( is_object( $gmb_post ) ) {
                $gmb_name = (string) $gmb_post->name;
                $gmb_url = (string) $gmb_post->searchUrl;
                $last_error = false;
                do_action( 'mbp_autopost_created', $gmb_name, $gmb_url );
                update_post_meta( $post_id, 'mbp_autopost_created', true );
            } else {
                $gmb_name = false;
                $gmb_url = false;
                $last_error = $gmb_post;
            }
            
            $args = array(
                'post_type'   => MBP_Post_Type_Google_Subposts::POST_TYPE,
                'post_parent' => $post_id,
                'meta_input'  => array(
                'mbp_location'     => array(
                'id'   => $location,
                'name' => $location_info->locationName,
            ),
                'mbp_created_time' => time(),
                'mbp_form_fields'  => array(
                'mbp_post_text'       => $gmb_post_args['summary'],
                'mbp_topic_type'      => $gmb_post_args['topicType'],
                'mbp_button'          => true,
                'mbp_button_type'     => $gmb_post_args['callToAction']['actionType'],
                'mbp_button_url'      => $gmb_post_args['callToAction']['url'],
                'mbp_attachment_type' => ( $thumbnail ? $gmb_post_args['media']['mediaFormat'] : false ),
                'mbp_post_attachment' => ( $thumbnail ? $gmb_post_args['media']['sourceUrl'] : '' ),
                'mbp_scheduled_date'  => false,
            ),
                'mbp_gmb_args'     => $gmb_post_args,
                'mbp_posts'        => array( array(
                'location'      => $location,
                'location_name' => $location_info->locationName,
                'name'          => $gmb_name,
                'url'           => $gmb_url,
            ) ),
                'mbp_last_error'   => $last_error,
            ),
                'post_status' => 'public',
            );
            wp_insert_post( $args, true );
            add_action(
                'save_post',
                array( &$this, 'do_quickpost' ),
                10,
                3
            );
            return true;
        }
        
        public function gmb_topic_types()
        {
            $types = array(
                'STANDARD' => array(
                'name'     => __( 'What\'s New', 'post-to-google-my-business' ),
                'dashicon' => 'dashicons-megaphone',
            ),
                'EVENT'    => array(
                'name'     => __( 'Event', 'post-to-google-my-business' ),
                'dashicon' => 'dashicons-calendar',
            ),
                'OFFER'    => array(
                'name'     => __( 'Offer', 'post-to-google-my-business' ),
                'dashicon' => 'dashicons-tag',
            ),
                'PRODUCT'  => array(
                'name'     => __( 'Product', 'post-to-google-my-business' ),
                'dashicon' => 'dashicons-cart',
            ),
            );
            return $types;
        }
        
        public function ajax_create_post()
        {
            //Fix this messy function asap
            check_ajax_referer( 'mbp_post_nonce', 'mbp_post_nonce' );
            $parent_post_id = (int) $_POST['mbp_post_id'];
            if ( !current_user_can( 'publish_posts', $parent_post_id ) ) {
                wp_send_json_error( array(
                    'error' => __( 'You do not have permission to publish posts', 'post-to-google-my-business' ),
                ) );
            }
            $errors = array();
            $editing = $child_post_id = ( isset( $_POST['mbp_editing'] ) && is_numeric( $_POST['mbp_editing'] ) ? intval( $_POST['mbp_editing'] ) : false );
            $draft = ( isset( $_POST['mbp_draft'] ) && json_decode( $_POST['mbp_draft'] ) ? true : false );
            $form_fields = array();
            foreach ( $_POST['mbp_form_fields'] as $field ) {
                
                if ( strpos( $field['name'], '[]' ) !== false ) {
                    //arrays
                    $field['name'] = substr( $field['name'], 0, -2 );
                    $form_fields[$field['name']][] = sanitize_text_field( $field['value'] );
                    continue;
                }
                
                
                if ( $field['name'] == 'mbp_post_text' ) {
                    $form_fields[$field['name']] = sanitize_textarea_field( $field['value'] );
                    continue;
                }
                
                $form_fields[$field['name']] = sanitize_text_field( $field['value'] );
            }
            $scheduled = false;
            $repost = false;
            //Create Google args
            $gmb_post_args = array(
                'languageCode' => get_bloginfo( 'language' ),
                'summary'      => stripslashes( $form_fields['mbp_post_text'] ),
                'topicType'    => $form_fields['mbp_topic_type'],
            );
            //Add image/video
            $sourceUrl = $form_fields['mbp_post_attachment'];
            $mediaFormat = $form_fields['mbp_attachment_type'];
            
            if ( $sourceUrl && !empty($sourceUrl) ) {
                $media = array(
                    'media' => array(
                    'mediaFormat' => $mediaFormat,
                    'sourceUrl'   => $sourceUrl,
                ),
                );
                $gmb_post_args = array_merge( $gmb_post_args, $media );
            }
            
            //Add button
            
            if ( isset( $form_fields['mbp_button'] ) ) {
                $button = array(
                    'callToAction' => array(
                    'actionType' => $form_fields['mbp_button_type'],
                ),
                );
                if ( $form_fields['mbp_button_type'] != 'CALL' ) {
                    $button['callToAction']['url'] = ( !empty($form_fields['mbp_button_url']) ? $form_fields['mbp_button_url'] : get_permalink( $parent_post_id ) );
                }
                $gmb_post_args = array_merge( $gmb_post_args, $button );
            }
            
            // End create Google args
            $locations = array();
            
            if ( !isset( $form_fields['mbp_selected_location'] ) ) {
                $locations[] = $this->settings->get_current_setting( 'google_location', 'mbp_google_settings' );
            } elseif ( isset( $form_fields['mbp_selected_location'] ) && !is_array( $form_fields['mbp_selected_location'] ) ) {
                $locations[] = $form_fields['mbp_selected_location'];
            } elseif ( isset( $form_fields['mbp_selected_location'] ) && is_array( $form_fields['mbp_selected_location'] ) ) {
                $locations = $form_fields['mbp_selected_location'];
            }
            
            $locations = apply_filters( 'mbp_create_post_locations', $locations );
            
            if ( $editing ) {
                $maskitems = array(
                    'mbp_attachment_type' => 'media.mediaFormat',
                    'mbp_post_image'      => 'media.sourceUrl',
                    'mbp_post_text'       => 'summary',
                    'mbp_button_type'     => 'callToAction.actionType',
                    'mbp_button_url'      => 'callToAction.url',
                    'mbp_button'          => 'callToAction',
                );
                $maskitems = apply_filters( 'mbp_mask_items', $maskitems );
                $old_values = get_post_meta( $child_post_id, 'mbp_form_fields', true );
                update_post_meta( $child_post_id, 'mbp_last_error', false );
                $mask_array = array();
                foreach ( $maskitems as $key => $value ) {
                    if ( !isset( $form_fields[$key] ) || !isset( $old_value[$key] ) || $form_fields[$key] != $old_values[$key] ) {
                        $mask_array[] = $value;
                    }
                }
                $mask = implode( ",", $mask_array );
            }
            
            $creation_time = time();
            $api = MBP_api::getInstance();
            $args = array(
                'ID'          => $child_post_id,
                'post_type'   => MBP_Post_Type_Google_Subposts::POST_TYPE,
                'post_parent' => $parent_post_id,
                'meta_input'  => array(
                'mbp_created_time' => $creation_time,
                'mbp_form_fields'  => $form_fields,
                'mbp_gmb_args'     => $gmb_post_args,
            ),
                'post_status' => ( $draft ? 'draft' : 'publish' ),
            );
            $child_post_id = wp_insert_post( $args, true );
            
            if ( !$scheduled && !$draft ) {
                //
                $gmb_post_args['summary'] = strip_tags( $gmb_post_args['summary'] );
                $gmb_post_args['summary'] = substr( $gmb_post_args['summary'], 0, 1500 );
                $gmb_posts = $this->get_gmb_posts( $child_post_id );
                foreach ( $locations as $location ) {
                    $new_gmb_post = array();
                    $location_info = $api->get_location( $location );
                    $new_gmb_post['location'] = $location;
                    $new_gmb_post['location_name'] = (string) $location_info->locationName;
                    $key = $this->is_gmb_post_created_for_location( $gmb_posts, $location );
                    
                    if ( $editing && $key !== false && $this->is_gmb_post_published( $gmb_posts[$key] ) ) {
                        $post_name = $gmb_posts[$key]['name'];
                        $mbp_post = $api->update_post( (string) $post_name, $gmb_post_args, $mask );
                    } else {
                        $mbp_post = $api->create_post( $location, $gmb_post_args );
                    }
                    
                    
                    if ( is_object( $mbp_post ) ) {
                        $new_gmb_post = array_merge( $new_gmb_post, array(
                            'name' => (string) $mbp_post->name,
                            'url'  => (string) $mbp_post->searchUrl,
                        ) );
                    } else {
                        $errors[] = sprintf( __( 'Failed to post to location %s: %s', 'post-to-google-my-business' ), $new_gmb_post['location_name'], $mbp_post );
                        $new_gmb_post = array_merge( $new_gmb_post, array(
                            'error' => $mbp_post,
                        ) );
                    }
                    
                    
                    if ( $key !== false ) {
                        $gmb_posts[$key] = $new_gmb_post;
                    } else {
                        $gmb_posts[] = $new_gmb_post;
                    }
                
                }
                update_post_meta( $child_post_id, 'mbp_posts', $gmb_posts );
            }
            
            $types = $this->gmb_topic_types();
            //error_log(print_r($location_info, true));
            $json_args = array(
                'id'  => $child_post_id,
                'row' => $this->create_table_row(
                $child_post_id,
                $types[$form_fields['mbp_topic_type']]['dashicon'],
                $types[$form_fields['mbp_topic_type']]['name'],
                ( isset( $gmb_url ) ? $gmb_url : false ),
                null,
                $creation_time,
                ( isset( $form_fields['mbp_scheduled_date'] ) ? $form_fields['mbp_scheduled_date'] : false ),
                $repost
            ),
            );
            
            if ( !empty($errors) ) {
                $json_args['error'] = $error = implode( '<br />', $errors );
                update_post_meta( $child_post_id, 'mbp_last_error', $error );
                wp_send_json_error( $json_args );
            } else {
                wp_send_json_success( $json_args );
            }
        
        }
        
        public function get_gmb_posts( $child_post_id )
        {
            $posts = get_post_meta( $child_post_id, 'mbp_posts', true );
            if ( is_array( $posts ) ) {
                return $posts;
            }
            return array();
        }
        
        public function is_gmb_post_created_for_location( $gmb_posts, $location )
        {
            foreach ( $gmb_posts as $key => $post ) {
                if ( $post['location'] == $location ) {
                    return $key;
                }
            }
            return false;
        }
        
        public function is_gmb_post_published( $post )
        {
            if ( isset( $post['url'] ) ) {
                return true;
            }
            return false;
        }
        
        public function gmb_location_post_error( $post )
        {
            if ( isset( $post['error'] ) ) {
                return $post['error'];
            }
            return false;
        }
        
        public function create_gmb_posts( $locations, $args )
        {
            $api = MBP_api::getInstance();
            $gmb_posts = array();
            foreach ( $locations as $location ) {
                $location_info = $api->get_location( $location );
                $new_gmb_post = array(
                    'location'      => $location,
                    'location_name' => (string) $location_info->locationName,
                );
                $gmb_post = $this->create_gmb_post( $location, $args );
                
                if ( is_object( $gmb_post ) ) {
                    $new_gmb_post = array_merge( $new_gmb_post, array(
                        'name' => (string) $gmb_post->name,
                        'url'  => (string) $gmb_post->searchUrl,
                    ) );
                } else {
                    $new_gmb_post = array_merge( $new_gmb_post, array(
                        'error' => $gmb_post,
                    ) );
                }
                
                $gmb_posts[] = $new_gmb_post;
            }
            return $gmb_posts;
        }
        
        public function create_gmb_post( $location, $args )
        {
            $api = MBP_api::getInstance();
            $gmb_post = $api->create_post( $location, $args );
            return $gmb_post;
        }
        
        public function ajax_load_post()
        {
            check_ajax_referer( 'mbp_post_nonce', 'mbp_post_nonce' );
            $post_id = (int) $_POST['mbp_post_id'];
            if ( !current_user_can( 'edit_posts', $post_id ) ) {
                wp_send_json( array(
                    'error' => __( 'You do not have permission to edit posts', 'post-to-google-my-business' ),
                ) );
            }
            $form_fields = get_post_meta( $post_id, 'mbp_form_fields', true );
            $has_error = get_post_meta( $post_id, 'mbp_last_error', true );
            
            if ( $form_fields && is_array( $form_fields ) ) {
                wp_send_json( array(
                    'success'   => true,
                    'post'      => array(
                    'form_fields' => $form_fields,
                    'post_status' => get_post_status( $post_id ),
                ),
                    'has_error' => $has_error,
                ) );
            } else {
                wp_send_json( array(
                    'error' => __( 'Post could not be loaded', 'post-to-google-my-business' ),
                ) );
            }
        
        }
        
        public function ajax_delete_post()
        {
            check_ajax_referer( 'mbp_post_nonce', 'mbp_post_nonce' );
            $post_id = (int) $_POST['mbp_post_id'];
            if ( !current_user_can( 'delete_posts', $post_id ) ) {
                wp_send_json( array(
                    'error' => __( 'You do not have permission to delete posts', 'post-to-google-my-business' ),
                ) );
            }
            $errors = array();
            $api = MBP_api::getInstance();
            foreach ( $this->get_gmb_posts( $post_id ) as $gmb_post ) {
                if ( !$this->is_gmb_post_published( $gmb_post ) ) {
                    continue;
                }
                $delete = $api->delete_post( $gmb_post['name'] );
                if ( !is_object( $delete ) ) {
                    $errors[] = sprintf( __( 'Failed to delete post from location %s: %s', 'post-to-google-my-business' ), $gmb_post['location_name'], (string) $delete );
                }
            }
            wp_delete_post( $post_id );
            
            if ( empty($errors) ) {
                wp_send_json_success();
            } else {
                wp_send_json_error( array(
                    'error' => implode( '<br />', $errors ),
                ) );
            }
        
        }
        
        public function get_existing_posts( $parent_id )
        {
            $args = array(
                'post_parent' => $parent_id,
                'post_type'   => MBP_Post_Type_Google_Subposts::POST_TYPE,
            );
            $posts = get_children( $args );
            $types = $this->gmb_topic_types();
            
            if ( is_array( $posts ) ) {
                foreach ( $posts as $post_id => $post ) {
                    $form_fields = get_post_meta( $post_id, 'mbp_form_fields', true );
                    //$location = get_post_meta($post_id, 'mbp_location', true);
                    $mbp_posts = get_post_meta( $post_id, 'mbp_posts', true );
                    echo  $this->create_table_row(
                        $post_id,
                        $types[$form_fields['mbp_topic_type']]['dashicon'],
                        $types[$form_fields['mbp_topic_type']]['name'],
                        get_post_meta( $post_id, 'mbp_searchUrl', true ),
                        //$location,
                        $mbp_posts,
                        get_post_meta( $post_id, 'mbp_created_time', true ),
                        ( isset( $form_fields['mbp_scheduled_date'] ) ? $form_fields['mbp_scheduled_date'] : '' ),
                        get_post_meta( $post_id, 'mbp_repost', true ),
                        get_post_meta( $post_id, 'mbp_last_error', true )
                    ) ;
                }
                echo  '<tr class="no-items' . (( count( $posts ) >= 1 ? ' hidden' : '' )) . '"><td class="colspanchange" colspan="4">' . esc_html__( 'No GMB posts found.', 'post-to-google-my-business' ) . '</td></tr>' ;
            }
        
        }
        
        public function parse_placeholder_variables( $text, $post_id )
        {
            $post = get_post( $post_id );
            $variables = array();
            foreach ( $post as $key => $value ) {
                $variables['%' . $key . '%'] = $value;
            }
            $variables['%post_permalink%'] = get_permalink( $post_id );
            //User info
            $user_variables = array(
                'aim',
                'description',
                'display_name',
                'first_name',
                'jabber',
                'last_name',
                'nickname',
                'user_email',
                'user_nicename',
                'user_url',
                'yim'
            );
            foreach ( $user_variables as $variable ) {
                $variables['%author_' . $variable . '%'] = get_the_author_meta( $variable, $post->post_author );
            }
            $site_variables = array(
                'name',
                'description',
                'url',
                'pingback_url',
                'atom_url',
                'rdf_url',
                'rss_url',
                'rss2_url',
                'comments_atom_url',
                'comments_rss2_url'
            );
            foreach ( $site_variables as $variable ) {
                $variables['%site_' . $variable . '%'] = get_bloginfo( $variable );
            }
            $variables = apply_filters( 'mbp_placeholder_variables', $variables );
            return str_replace( array_keys( $variables ), $variables, $text );
        }
        
        public function create_table_row(
            $post_id,
            $dashicon,
            $topicType,
            $searchUrl,
            $location = array(),
            $created,
            $scheduledDate = false,
            $repost = false,
            $has_error = false
        )
        {
            $status = get_post_status( $post_id );
            $table_row = '
				<tr data-postid="' . $post_id . '"  class="mbp-post ' . (( $has_error ? ' mbp-has-error"' : '' )) . '">
					<td>
						' . (( $has_error ? '<span class="dashicons dashicons-warning"></span> ' : '' )) . '
						' . (( $repost ? '<span class="dashicons dashicons-controls-repeat"></span> ' : '' )) . '
						<a href="#" class="row-title mbp-action" data-action="edit"><span class="dashicons ' . $dashicon . '"></span> ' . $topicType . '</a>' . (( $status == 'draft' ? ' [DRAFT]' : '' )) . '<br />
						<div class="row-actions">
							<span class="edit"><a href="#" data-action="edit" class="mbp-action">' . __( 'Edit', 'post-to-google-my-business' ) . '</a> | </span>
							<span class="duplicate"><a href="#" data-action="duplicate" class="mbp-action">' . __( 'Duplicate', 'post-to-google-my-business' ) . '</a> | </span>
							<span class="trash"><a href="#" data-action="trash" class="submitdelete mbp-action">' . __( 'Delete', 'post-to-google-my-business' ) . '</a></span>';
            $table_row .= '
						</div>			
					</td>
					<td>
					' . (( $scheduledDate ? '<span class="dashicons dashicons-clock"></span> ' . $scheduledDate : __( 'Immediately', 'post-to-google-my-business' ) )) . '</td>
					<td>' . $this->get_location_list_from_gmb_post( $post_id ) . '</td>
					<td>' . human_time_diff( $created ) . ' ' . __( 'ago' ) . '</td>
				</tr>';
            return apply_filters( 'mbp_create_table_row', $table_row, $post_id );
        }
        
        public function get_location_list_from_gmb_post( $post_id )
        {
            $locations = array();
            foreach ( $this->get_gmb_posts( $post_id ) as $gmb_post ) {
                
                if ( $this->is_gmb_post_published( $gmb_post ) ) {
                    $locations[] = '<a href="' . $gmb_post['url'] . '" target="_blank">' . $gmb_post['location_name'] . ' <span class="dashicons dashicons-external"></span></a>';
                    continue;
                }
                
                
                if ( $error = $this->gmb_location_post_error( $gmb_post ) ) {
                    $locations[] = '<span title="' . strip_tags( $error ) . '">' . $gmb_post['location_name'] . ' <span class="dashicons dashicons-warning"></span></span>';
                    continue;
                }
                
                $locations[] = $gmb_post['location_name'];
            }
            return implode( '<br />', $locations );
        }
    
    }
}