<?php 
/**
 * Newsletter class
 *
 * @author   Magazine3
 * @category Admin
 * @path     reviews/reviews_service
 * @Version 1.9
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

class saswp_reviews_service {
    
    /**
     * List of hooks used in this context
     */
    public function saswp_service_hooks(){
        add_action( 'wp_ajax_saswp_fetch_google_reviews', array($this,'saswp_fetch_google_reviews'));
        add_shortcode( 'saswp-reviews', array($this, 'saswp_reviews_shortcode' ));
    }
    
    /**
     * Function to get reviews schema markup
     * @global type $sd_data
     * @return string
     */
    public function saswp_get_reviews_schema_markup($reviews){
                            
                            $sumofrating = 0;
                            $avg_rating  = 1;
                            $reviews_arr = array();
                            $input1      = array();
                            
                            if($reviews){
                                
                                foreach($reviews as $rv){
                                                                        
                                    $sumofrating += $rv['saswp_review_rating'];
                                    
                                    if($rv['saswp_review_rating'] && $rv['saswp_reviewer_name']){
                                        
                                        $reviews_arr[] = array(
                                            '@type'         => 'Review',
                                            'author'        => $rv['saswp_reviewer_name'],
                                            'datePublished' => $rv['saswp_review_date'],
                                            'description'   => $rv['saswp_review_text'],
                                            'reviewRating'  => array(
                                                        '@type'       => 'Rating',
                                                        'bestRating'  => 5,
                                                        'ratingValue' => $rv['saswp_review_rating'],
                                                        'worstRating' => 1
                                            ),
                                       );
                                        
                                    }
                                    
                                }
                                
                                    if($sumofrating> 0){
                                      $avg_rating = $sumofrating /  count($reviews); 
                                    }
                                
                                    if(!empty($reviews_arr)){
                                       
                                        $input1['review'] = $reviews_arr;
                                        
                                    }

                                    $input1['aggregateRating'] = array(
                                        '@type'       => 'AggregateRating',
                                        'reviewCount' => count($reviews),
                                        'ratingValue' => esc_attr($avg_rating),                                        
                                     );
                                
                                }
                            return $input1;                                      
                        
    }
    
    /**
     * Function to generate reviews html
     * @param type $reviews
     * @return string
     */
    public function saswp_reviews_html_markup($reviews){
        
        $output = '';
        if($reviews){
                        
            foreach ($reviews as $review){

                    $review_rating = $review['saswp_review_rating'];

                       $starating = saswp_get_rating_html_by_value($review_rating);

                        $term      = get_term( $review['saswp_review_platform'], 'platform' );
                        $term_slug  = ''; 
                    
                        if(is_object($term)){
                            $term_slug = $term->slug; 
                        }
                        
                        $img_src = SASWP_DIR_URI.'/admin_section/images/default_user.jpg';
                                                
                        if(isset($review['saswp_reviewer_image']) && $review['saswp_reviewer_image'] !=''){
                            $img_src = $review['saswp_reviewer_image'];
                        }
                                                                        
                        $output.= '<div class="saswp-g-review-panel">
                              <div class="saswp-glg-review-body">
                                <div class="saswp-rv-img">
                                    <img src="'.esc_url($img_src).'" alt="'.esc_attr($review['saswp_reviewer_name']).'">
                                </div>
                                <div class="saswp-rv-cnt">
                                    <div class="saswp-r5-rng">
                                        <div class="saswp-str">
                                            <span class="saswp-athr">'.esc_attr($review['saswp_reviewer_name']).'</span>
                                            '.$starating.'                                  
                                        </div> 
                                        <span class="saswp-g-plus">
                                            <a target="_blank" href="'.esc_attr($review['saswp_review_link']).'"><img src="'.SASWP_PLUGIN_URL.'/admin_section/images/reviews_platform_icon/'.esc_attr($term_slug).'-img.png'.'"></a>
                                        </span>
                                    </div>                                                
                                    <p>'.substr($review['saswp_review_text'],0,300).'</p>
                                </div>
                              </div>
                          </div>';
                                                                
                }

             wp_enqueue_style( 'saswp-style', SASWP_PLUGIN_URL . 'admin_section/css/saswp-style.min.css', false , SASWP_VERSION );       

            } 
        return $output;            
        
    }
    
    public function saswp_fetch_google_reviews(){
                
                if ( ! current_user_can( 'manage_options' ) ) {
                    return;
                }
        
                if ( ! isset( $_POST['saswp_security_nonce'] ) ){
                    return; 
                }
                
                if ( !wp_verify_nonce( $_POST['saswp_security_nonce'], 'saswp_ajax_check_nonce' ) ){
                   return;  
                }
                
                global $sd_data;
                
                $location  = $blocks = $premium_status = $g_api = $reviews_api = $reviews_api_status = '';
                
                if(isset($_POST['reviews_api'])){
                    $reviews_api = sanitize_text_field($_POST['reviews_api']);
                }
                
                if(isset($_POST['reviews_api_status'])){
                    $reviews_api_status = sanitize_text_field($_POST['reviews_api_status']);
                }
                                
                if(isset($_POST['location'])){
                    $location = sanitize_text_field($_POST['location']);
                }
                
                if(isset($_POST['g_api'])){                    
                    $g_api = sanitize_text_field($_POST['g_api']);                                        
                }
                
                if(isset($_POST['premium_status'])){
                    $premium_status = sanitize_text_field($_POST['premium_status']);
                }
                
                if(isset($_POST['blocks'])){
                    $blocks = intval($_POST['blocks']);
                }
                                                
                if($location){
                    
                   if(isset($sd_data['saswp_reviews_location_name'])){
                          
                       if(!in_array($location, $sd_data['saswp_reviews_location_name'])){
                           array_push($sd_data['saswp_reviews_location_name'], $location);                       
                       }
                                              
                   }else{
                       $sd_data['saswp_reviews_location_name'] = array($location);  
                       
                   }
                                      
                   if(isset($sd_data['saswp_reviews_location_blocks'])){
                          
                       if(!in_array($blocks, $sd_data['saswp_reviews_location_blocks'])){
                           array_push($sd_data['saswp_reviews_location_blocks'], $blocks);                       
                       }
                                              
                   }else{
                       
                           $sd_data['saswp_reviews_location_blocks'] = array($blocks);  
                       
                   }
                        
                  $sd_data['saswp-google-review']        = 1;
                  $sd_data['saswp_google_place_api_key'] = $g_api;
                  update_option('sd_data', $sd_data);    
                                    
                  $result         = null;                                    
                  $user_id        = get_option('reviews_addon_user_id');
                    
                  if($reviews_api){                       
                        
                      if($premium_status == 'premium'){
                        
                        if($reviews_api_status == 'active'){
                          
                            if($user_id){
                             
                                if(function_exists('saswp_get_paid_reviews_data')){

                                $result = saswp_get_paid_reviews_data($location, $reviews_api, $user_id, $blocks); 

                                if($result['status'] && is_numeric($result['message'])){
                                    
                                    $rv_limits = get_option('reviews_addon_reviews_limits');
                                    
                                    $result['message'] = esc_html__('Reviews fetched','schema-and-structured-data-for-wp').' : '. $rv_limits - $result['message']. ', '.esc_html__('Remains Limit','schema-and-structured-data-for-wp').' : '.$result['message'];                                    
                                    
                                    update_option('reviews_addon_reviews_limits', intval($result['message']));
                                }

                                }else{
                                    $result['status']  = false;
                                    $result['message'] = esc_html__( 'Reviews for schema plugin is not activated', 'schema-and-structured-data-for-wp' );
                                }
                                
                            }else{
                                $result['status']  = false;
                                $result['message'] = esc_html__( 'User is not register', 'schema-and-structured-data-for-wp' );
                            }                                                        
                            
                        }else{
                                $result['status']  = false;
                                $result['message'] = esc_html__( 'License key is not active', 'schema-and-structured-data-for-wp' );
                        }  
                                                  
                        
                      }else{
                          
                          if($g_api){
                                                                          
                             $result = $this->saswp_get_free_reviews_data($location, $g_api);                                                                                                                                  
                             
                         }
                         
                      }
                                              
                  }else{
                      
                      if($g_api){
                                                                              
                          $result = $this->saswp_get_free_reviews_data($location, $g_api);                                                                                                                                  
                      }                      
                      
                  }  
                                                             
                  echo json_encode($result);
                    
                }else{
                    
                  echo json_encode(array('status' => false, 'message' => esc_html__( 'Place id is empty', 'schema-and-structured-data-for-wp' ))); 
                  
                }
                
            wp_die();
        
    }
        
    /**
     * Function to show value using shortcode "saswp-reviews"
     * @param type $attr
     * @return type
     */
    public function saswp_reviews_front_output($attr){
        
            global $sd_data;
            $reviews = $this->saswp_get_reviews_list_by_parameters($attr);
                        
            $output = $html = '';
            
            if($reviews){
                
               $output = $this->saswp_reviews_html_markup($reviews);  
                            
               if(saswp_global_option()){
                
                 $rv_markup = $this->saswp_get_reviews_schema_markup($reviews);
                 
                 if($rv_markup){
                                          
                        $input1['@context'] = saswp_context_url();
                        $input1['@type']    = (isset($sd_data['saswp_organization_type']) && $sd_data['saswp_organization_type'] !='' )? $sd_data['saswp_organization_type'] : 'Organization';
                        $input1['name']     = (isset($sd_data['sd_name']) && $sd_data['sd_name'] !='' )? $sd_data['sd_name'] : get_bloginfo();
                                          
                        $input1  = $input1 + $rv_markup;
                      
                        $html .= "\n";
                        $html .= '<!-- Schema & Structured Data For Reviews v'.esc_attr(SASWP_VERSION).' - -->';
                        $html .= "\n";
                        $html .= '<script type="application/ld+json" class="saswp-reviews-markup">'; 
                        $html .= "\n";       
                        $html .= saswp_json_print_format($input1);       
                        $html .= "\n";
                        $html .= '</script>';
                        $html .= "\n\n";
                      
                      $output = $output.$html;

                  }
          
                }
                              
            }
            
            return $output;
                                        
    }
    
    public function saswp_reviews_shortcode($attr){
                                                        
        $response = $this->saswp_reviews_front_output($attr);
                                               
        return $response;
        
    }
                            
    public function saswp_save_free_reviews_data($result, $place_id) {
                
        $place_saved   = array();
        $reviews_saved = array();
        
        if (isset($result['place_id']) && $result['place_id'] != '') {
                                                                   
                $user_id     = get_current_user_id();
                $postarr = array(
                    'post_author'           => $user_id,                                                            
                    'post_title'            => $result['name'],                    
                    'post_status'           => 'publish',                                                            
                    'post_name'             => $result['name'],                                                            
                    'post_type'             => 'saswp_rvs_location',
                                                                             
                );
                   
                $post_id = wp_insert_post(  $postarr );   
                $place_saved[] = $post_id;                                                  
                $review_meta = array(
                        'saswp_rvs_loc_id'                 => $result['place_id'],      
                        'saswp_rvs_loc_review_count'       => $result['user_ratings_total'], 
                        'saswp_rvs_loc_avg_rating'         => $result['rating'],
                        'saswp_rvs_loc_icon'               => $result['icon'],
                        'saswp_rvs_loc_address'            => $result['formatted_address'],
                );

                if($post_id && !empty($review_meta) && is_array($review_meta)){
                                        
                    foreach ($review_meta as $key => $val){                     
                        update_post_meta($post_id, $key, $val);  
                    }
            
                 }
                            
        }
        
                                            
        if (isset($result['reviews'])) {
            
            $reviews = $result['reviews'];
            
            foreach ($reviews as $review) {
               
                $user_id     = get_current_user_id();
                $postarr = array(
                    'post_author'           => $user_id,                                                            
                    'post_title'            => $review['author_name'],                    
                    'post_status'           => 'publish',                                                            
                    'post_name'             => 'Default Review',                                                            
                    'post_type'             => 'saswp_reviews',
                                                                             
                );
                   
                $post_id = wp_insert_post(  $postarr );   
                $reviews_saved[] = $post_id;
                $term     = get_term_by( 'slug','google', 'platform' );
                
                $media_detail = array();
                
                if(isset($review['profile_photo_url'])){
                    
                    $image_details = saswp_get_attachment_details(array($review['profile_photo_url']));   
                    
                    $media_detail = array(                                                    
                        'width'      => $image_details[0][0],
                        'height'     => $image_details[0][1],
                        'thumbnail'  => $review['profile_photo_url'],
                    );
                    
                }                
                
                $review_meta = array(
                        'saswp_review_platform'       => $term->term_id,
                        'saswp_review_location_id'    => $place_id,
                        'saswp_review_time'           => $review['time'], 
                        'saswp_review_rating'         => $review['rating'],
                        'saswp_review_text'           => $review['text'],                                
                        'saswp_reviewer_lang'         => $review['language'],
                        'saswp_reviewer_name'         => $review['author_name'],
                        'saswp_review_link'           => isset($review['author_url']) ? $review['author_url'] : null,
                        'saswp_reviewer_image'        => isset($review['profile_photo_url']) ? $review['profile_photo_url'] : SASWP_DIR_URI.'/admin_section/images/default_user.jpg',
                        'saswp_reviewer_image_detail' => $media_detail
                );

                if($post_id && !empty($review_meta) && is_array($review_meta)){
                                        
                    foreach ($review_meta as $key => $val){                     
                        update_post_meta($post_id, $key, $val);  
                    }
            
                 }
                
            }
        }
        
        if(!empty($place_saved) || !empty($reviews_saved)){
            return true;
        }else{
            return false;
        }
                
    }
    
    public function saswp_get_free_reviews_data($place_id, $g_api){
                                                   
        $result = @wp_remote_get('https://maps.googleapis.com/maps/api/place/details/json?placeid='.trim($place_id).'&key='.trim($g_api));                
        
        if(isset($result['body'])){
            
           $result = json_decode($result['body'],true);   
           
           if($result['result']){
               
               $response = $this->saswp_save_free_reviews_data($result['result'], $place_id);
               
               if($response){
                    return array('status' => true, 'message' => esc_html__( 'Fetched Successfully', 'schema-and-structured-data-for-wp' ));
               }else{                                             
                    return array('status' => false, 'message' => esc_html__( 'Not fetched', 'schema-and-structured-data-for-wp' ));
               }
               
           }else{
               if($result['error_message']){
                   return array('status' => false, 'message' => $result['error_message']);
               }else{
                   return array('status' => false, 'message' => esc_html__( 'Something went wrong', 'schema-and-structured-data-for-wp' ));
               }                             
           }
                                                       
        }else{
           return null;
        }        
                                            
    }
    
    public function saswp_get_reviews_list_by_parameters($attr = null, $platform_id = null, $rvcount = null){
            
            $response = array();
                                
            $arg        = array();
            $meta_query = array();
            
            $arg['post_type']      = 'saswp_reviews';
            $arg['numberposts']    = -1;
            $arg['post_status']    = 'publish';
                        
            if($attr){
                
            if(isset($attr['id'])){
              $arg['attachment_id']    = $attr['id'];  
            }
            if(isset($attr['title'])){
              $arg['title']    = $attr['title'];  
            }
            if(isset($attr['count'])){
                $arg['posts_per_page'] = $attr['count'];
            }    
                
            if(isset($attr['rating'])){
                    $meta_query[] = array(
                        'key'     => 'saswp_review_rating',
                        'value'   => $attr['rating'],
                        'compare' => '='
                    );
            }
            if(isset($attr['platform'])){
                $term     = get_term_by( 'slug', $attr['platform'], 'platform' );
                
                  $meta_query[] =   array(
                        'key'     => 'saswp_review_platform',
                        'value'   => $term->term_id,
                        'compare' => '='
                    );
            }
            $meta_query_args = array(            
            array(
                'relation' => 'AND',
                 $meta_query 
                )
            );
            $arg['meta_query'] = $meta_query_args;    
            }
            
            
            if($platform_id && $rvcount){
                
                 $arg['numberposts']    = $rvcount;
                 $arg['meta_query'] = array(
                                        array(
                                            'key'     => 'saswp_review_platform',
                                            'value'   => $platform_id,
                                            'compare' => '==',
                                 )
                            ); 
                
            }
                        
            $posts_list = get_posts($arg); 
                                   
            if($posts_list){
            
             $post_meta = array(                     
              'saswp_reviewer_image',
              'saswp_reviewer_name',
              'saswp_review_rating',
              'saswp_review_date',
              'saswp_review_text',
              'saswp_review_link',
              'saswp_review_platform',
              'saswp_review_platform_icon',
              'saswp_review_platform_name',   
            );
            
            foreach($posts_list as $rv_post){
                
                $review_data = array();                
                                
                foreach($post_meta as $meta_key){
                    
                    $review_data[$meta_key] = get_post_meta($rv_post->ID, $meta_key, true ); 
                                        
                }
                
                if(!$review_data['saswp_reviewer_image']){
                    $review_data['saswp_reviewer_image'] = SASWP_DIR_URI.'/admin_section/images/default_user.jpg';
                }

                $term     = get_term( $review_data['saswp_review_platform'], 'platform' );  
                
                if(!$review_data['saswp_review_platform_icon']){

                    if(isset($term->slug)){

                        $review_data['saswp_review_platform_icon'] = SASWP_PLUGIN_URL.'/admin_section/images/reviews_platform_icon/'.esc_attr($term->slug).'-img.png';
                    }

                }
                
                if(!$review_data['saswp_review_platform_name']){
                    $review_data['saswp_review_platform_name'] = $term->name;
                }
                
                   $review_data['saswp_review_post_id'] = $rv_post->ID;
                   $response[] = $review_data;  
            }
            
        }
                                      
        return $response;
    }
    
    public function saswp_sort_collection($collection, $sorting){
             
         if($collection){
               
               switch($sorting){
                    
                case 'lowest':
                    
                        usort($collection, function($a, $b) {
                            return $a['saswp_review_rating'] - $b['saswp_review_rating'];
                        });
                                                
                        break;
                    
                case 'highest':
                    
                        usort($collection, function($a, $b) {
                            return $a['saswp_review_rating'] - $b['saswp_review_rating'];
                        });
                        
                        $collection = array_reverse($collection);
                            
                        
                        break;
                        
               case 'newest':
               case 'recent':
                   
                        usort($collection, function($a, $b) {                           
                            return strtotime($a['saswp_review_date']) - strtotime($b['saswp_review_date']);
                        });
                        
                        $collection = array_reverse($collection);
                                                                                                             
                    break;
                    
               case 'oldest':
                   
                        usort($collection, function($a, $b) {                           
                            return strtotime($a['saswp_review_date']) - strtotime($b['saswp_review_date']);
                        });
                                                                                                                                                           
                    break; 
                
                case 'random':
                    
                       shuffle($collection);
                                                                                                                  
                    break;
                    
                }
               
           }
                
           return $collection;
                   
    }
    
    public function saswp_convert_datetostring($date_str){
        
        $response = array();
        
        $response['date'] = date('Y-m-d', strtotime($date_str));
        $response['time'] = date('G:i:s', strtotime($date_str));
        
        return $response;
        
    }
    
    public function saswp_create_collection_grid($cols, $collection){
        
           $html          = '';                
           $grid_cols     = '';

           if($collection){

               
               if(saswp_non_amp()){
                   
               $html .= '<div class="saswp-r1">';

               for($i=1; $i <= $cols; $i++ ){
                   $grid_cols .=' 1fr'; 
               }     

               if($cols > 5){

                      $html .= '<ul style="grid-template-columns:'.esc_attr($grid_cols).';overflow-x: scroll;">'; 
                      }else{
                      $html .= '<ul style="grid-template-columns:'.esc_attr($grid_cols).';overflow-x:hidden;">';     
                      }                               

               
               foreach ($collection as $value){

                       $date_str = $this->saswp_convert_datetostring($value['saswp_review_date']); 

                       $html .= '<li>';                       
                       $html .= '<div class="saswp-rc">';
                       $html .= '<div class="saswp-rc-a">';
                       $html .= '<div class="saswp-r1-aimg">';
                       $html .= '<img src="'.esc_url($value['saswp_reviewer_image']).'" width="56" height="56"/>';
                       $html .= '</div>';
                       $html .= '<div class="saswp-rc-nm">';
                       $html .= '<a href="#">'.esc_attr($value['saswp_reviewer_name']).'</a>';
                       $html .= saswp_get_rating_html_by_value($value['saswp_review_rating']);                       
                       $html .= '<span class="saswp-rc-dt">'.esc_attr($date_str['date']).'</span>';
                       $html .= '</div>';
                       $html .= '</div>';

                       $html .= '<div class="saswp-rc-lg">';
                       $html .= '<img src="'.esc_url($value['saswp_review_platform_icon']).'"/>';
                       $html .= '</div>';

                       $html .= '</div>';
                       $html .='<div class="saswp-rc-cnt">';
                       $html .= '<p>'. esc_attr($value['saswp_review_text']).'</p>';
                       $html .= '</div>';
                       $html .= '</li>'; 

               }

               $html .= '</ul>';
               $html .= '</div>';
                   
               }else{
                   
               $html .= '<div class="saswp-r1">';

               for($i=1; $i <= $cols; $i++ ){
                   $grid_cols .=' 1fr'; 
               }                     
               if($cols > 4){

                      $html .= '<ul class="saswp-grid-scroll" style="grid-template-columns:'.esc_attr($grid_cols).';">'; 
                      }else{
                      $html .= '<ul class="saswp-grid-scroll-hidden" style="grid-template-columns:'.esc_attr($grid_cols).';">';     
                      }  
               
               foreach ($collection as $value){

                       $date_str = $this->saswp_convert_datetostring($value['saswp_review_date']); 

                       $html .= '<li>';                       
                       $html .= '<div class="saswp-rc">';
                       $html .= '<div class="saswp-rc-a">';
                       $html .= '<div class="saswp-r1-aimg">';
                       $html .= '<amp-img src="'.esc_url($value['saswp_reviewer_image']).'" width="70" height="56"></amp-img>';                       
                       $html .= '</div>';
                       $html .= '<div class="saswp-rc-nm">';
                       $html .= '<a href="#">'.esc_attr($value['saswp_reviewer_name']).'</a>';
                       $html .= saswp_get_rating_html_by_value($value['saswp_review_rating']);                       
                       $html .= '<span class="saswp-rc-dt">'.esc_attr($date_str['date']).'</span>';
                       $html .= '</div>';
                       $html .= '</div>';

                       $html .= '<div class="saswp-rc-lg">';
                       $html .= '<img src="'.esc_url($value['saswp_review_platform_icon']).'"/>';
                       $html .= '</div>';

                       $html .= '</div>';
                       $html .='<div class="saswp-rc-cnt">';
                       $html .= '<p>'. esc_attr($value['saswp_review_text']).'</p>';
                       $html .= '</div>';
                       $html .= '</li>'; 

               }

               $html .= '</ul>';
               $html .= '</div>';
                   
                   
               }
               
           }           
           return $html;
        
    }
    
    public function  saswp_review_desing_for_slider($value){
                        
                $html = '';
                $date_str = $this->saswp_convert_datetostring($value['saswp_review_date']); 
                
                $html .= '<div class="saswp-r2-sli">';
                $html .= '<div class="saswp-r2-b">';
                
                $html .= '<div class="saswp-r2-q">';
                $html .= '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="Capa_1" x="0px" y="0px" width="95.333px" height="95.332px" viewBox="0 0 95.333 95.332" style="enable-background:new 0 0 95.333 95.332;" xml:space="preserve"><path d="M30.512,43.939c-2.348-0.676-4.696-1.019-6.98-1.019c-3.527,0-6.47,0.806-8.752,1.793    c2.2-8.054,7.485-21.951,18.013-23.516c0.975-0.145,1.774-0.85,2.04-1.799l2.301-8.23c0.194-0.696,0.079-1.441-0.318-2.045    s-1.035-1.007-1.75-1.105c-0.777-0.106-1.569-0.16-2.354-0.16c-12.637,0-25.152,13.19-30.433,32.076    c-3.1,11.08-4.009,27.738,3.627,38.223c4.273,5.867,10.507,9,18.529,9.313c0.033,0.001,0.065,0.002,0.098,0.002    c9.898,0,18.675-6.666,21.345-16.209c1.595-5.705,0.874-11.688-2.032-16.851C40.971,49.307,36.236,45.586,30.512,43.939z"></path><path d="M92.471,54.413c-2.875-5.106-7.61-8.827-13.334-10.474c-2.348-0.676-4.696-1.019-6.979-1.019    c-3.527,0-6.471,0.806-8.753,1.793c2.2-8.054,7.485-21.951,18.014-23.516c0.975-0.145,1.773-0.85,2.04-1.799l2.301-8.23    c0.194-0.696,0.079-1.441-0.318-2.045c-0.396-0.604-1.034-1.007-1.75-1.105c-0.776-0.106-1.568-0.16-2.354-0.16    c-12.637,0-25.152,13.19-30.434,32.076c-3.099,11.08-4.008,27.738,3.629,38.225c4.272,5.866,10.507,9,18.528,9.312    c0.033,0.001,0.065,0.002,0.099,0.002c9.897,0,18.675-6.666,21.345-16.209C96.098,65.559,95.376,59.575,92.471,54.413z"></path></svg>';
                $html .= '</div>';
                $html .= '<div class="saswp-rc-cnt">';
                $html .= '<p>';
                $html .= esc_attr($value['saswp_review_text']);
                $html .= '</p>';
                $html .= '</div>';
                $html .= '<div class="saswp-r2-strs">';
                $html .= '<span class="saswp-r2-s">';
                $html .= saswp_get_rating_html_by_value($value['saswp_review_rating']);
                $html .= '</span>';
                $html .= '</div>';
                $html .= '</div>';
                $html .= '<div class="saswp-rc">';
                $html .= '<div class="saswp-rc-a">';
                $html .= '<img src="'.esc_url($value['saswp_reviewer_image']).'"/>';
                $html .= '<div class="saswp-rc-nm">';
                $html .= '<a href="#">'. esc_attr($value['saswp_reviewer_name']).'</a>';
                $html .= '<span class="saswp-rc-dt">'.esc_attr($date_str['date']).'</span>';
                $html .= '</div>';
                $html .= '</div>';
                $html .= '<div class="saswp-rc-lg">';
                $html .= '<img src="'.esc_attr($value['saswp_review_platform_icon']).'"/>';
                $html .= '</div>';
                $html .= '</div>';
                $html .= '</div>';

                return $html;

    }

    public function saswp_create_collection_slider($g_type, $arrow, $dots, $collection){
                
                $html = '';                               
                
                if($collection){
                    
                    if(saswp_non_amp()){
                      
                        if($g_type == 'slider'){
                          $html .= '<div class="saswp-cst">';  
                        }else{
                          $html .= '<div class="saswp-cct">';  
                        }

                        
                        $html .= '<div class="saswp-cs">';
                        $html .= '<div class="saswp-sic">';
                              
                    if($g_type == 'slider'){
                            
                         foreach ($collection as $value){
                                                          
                                $html .= '<div class="saswp-si">';
                                
                                $html .= $this->saswp_review_desing_for_slider($value);
                                
                                $html .= '</div>';
                             
                            }
                                                                                    
                         }   
                         
                    if($g_type == 'carousel'){
                             
                            $chunkarr = array_chunk($collection,3);
                            
                            if($chunkarr){
                                                                                                                
                            foreach($chunkarr as $coll){
                                
                                $html .= '<div class="saswp-si">';
                                                                    
                                foreach($coll as $value){

                                     $html .= $this->saswp_review_desing_for_slider($value);

                                }
                                
                                $html .= '</div>';   
                                                               
                            }
                                                                
                            }
                                                       
                          }                                                                                     
                    
                    $html .= '</div>';
                                        
                    if($arrow){
                        $html .= '<div class="saswp-slider-controls">';    
                        $html .= '<a href="#" class="saswp-slider-prev-btn"></a>';
                        $html .= '<a href="#" class="saswp-slider-next-btn"></a>';
                        $html .= '</div>';
                    }
                    
                    if($dots){
                    
                    $html .= '<div class="saswp-sd">';
                    $html .= '</div>';
                        
                    }
                    
                    $html .= '</div>';
                    $html .= '</div>';
                        
                    }else{
                        
                     if($collection){
                         
                         $slide_button = '';
                         
                         if($g_type == 'carousel'){
                              $html .= '<amp-carousel class="carousel-type" id="carousel-with-preview" height="290" layout="fixed-height" type="carousel"   delay="2000"  on="slideChange:AMP.setState({currentClass: event.index})">';
                         }
                         if($g_type == 'slider'){
                              $html .= '<amp-carousel class="slider-type" id="carousel-with-preview" height="290" layout="fixed-height" type="slides"  delay="2000" on="slideChange:AMP.setState({currentClass: event.index})">';
                         }
                                                 
                         $i = 0;
                         
                         foreach ($collection as $value){
                             
                             $html .= '<li>';
                             $html .= $this->saswp_review_desing_for_slider($value);
                             $html .= '</li>';
                             
                             if($i == 0){
                                 $slide_button .= '<button on="tap:carousel-with-preview.goToSlide(index='.$i.'),AMP.setState({currentClass:'.$i.'})" [class]="currentClass=='.$i.' ? \'active\' : \'\' " class="active"></button>';
                             }else{
                                 $slide_button .= '<button on="tap:carousel-with-preview.goToSlide(index='.$i.'),AMP.setState({currentClass:'.$i.'})" [class]="currentClass=='.$i.' ? \'active\' : \'\' "></button>';
                             }
                                                         
                             $i++;
                         }
                                                  
                         $html .= '</amp-carousel>';
                         $html .= '<div class="saswp-cp">';
                         $html .= $slide_button;                         
                         $html .= '</div>';
                         
                     }   
                        
                    $html .= '<div class="saswp-rd2-warp">';    
                    
                    $html .= '</div>';    
                        
                        
                    }
                                                                                 
                 }
                 
                 return $html;
                
    }
    public function saswp_create_collection_badge($collection){
        
                $html = '';                
                if($collection){
            
                    if(saswp_non_amp()){
                        
                    $html .= '<div class="saswp-r3">';
                    $html .= '<ul>';
                                                            
                    foreach ($collection as $platform_wise){

                        $platform_icon  = '';
                        $platform_name  = '';
                        $review_count   = 0;                        
                        $sum_of_rating  = 0;
                        $average_rating = 1;
                        
                        foreach ($platform_wise as $key => $value){
                            
                            $platform_name  = $value['saswp_review_platform_name'];
                            $platform_icon  = $value['saswp_review_platform_icon'];
                            $sum_of_rating += $value['saswp_review_rating'];
                            $review_count++;
                            
                        }
                        
                      if($sum_of_rating > 0){
                        
                            $average_rating = $sum_of_rating / $review_count;
                            
                        }
                            
                      $html .= '<li>';                       
                      $html .= '<a href="#">'; 
                      $html .= '<div class="saswp-r3-lg">';
                      $html .= '<span>';
                      $html .= '<img src="'.esc_url($platform_icon).'"/>';
                      $html .= '</span>';
                      $html .= '<span class="saswp-r3-tlt">'.esc_attr($platform_name).'</span>';                      
                      $html .= '</div>';
                      $html .= '<div class="saswp-r3-rtng">';
                      $html .= '<div class="saswp-r3-rtxt">';
                      $html .= '<span class="saswp-r3-num">';
                      $html .= esc_attr(number_format($average_rating,1));
                      $html .= '</span>';
                      $html .= '<span class="saswp-stars">';
                      $html .= saswp_get_rating_html_by_value($average_rating); 
                      $html .= '</span>';
                      $html .= '</div>';
                      $html .= '<span class="saswp-r3-brv">';
                      $html .= esc_html__('Based on','schema-and-structured-data-for-wp').' '.esc_attr($review_count).' '.esc_html__('Reviews','schema-and-structured-data-for-wp');
                      $html .= '</span>';
                      $html .= '</div>';
                      $html .= '</a>';
                      $html .= '</li>';                                                                     

                    }      
                    
                    $html .= '</ul>';
                    $html .= '</div>';
                        
                    }else{
                        
                    $html .= '<div class="saswp-r3">';
                    $html .= '<ul>';
                                                            
                    foreach ($collection as $platform_wise){

                        $platform_icon  = '';
                        $platform_name  = '';
                        $review_count   = 0;                        
                        $sum_of_rating  = 0;
                        $average_rating = 1;
                        
                        foreach ($platform_wise as $key => $value){
                            
                            $platform_name  = $value['saswp_review_platform_name'];
                            $platform_icon  = $value['saswp_review_platform_icon'];
                            $sum_of_rating += $value['saswp_review_rating'];
                            $review_count++;
                            
                        }
                        
                      if($sum_of_rating > 0){
                        
                            $average_rating = $sum_of_rating / $review_count;
                            
                        }
                            
                      $html .= '<li>';                       
                      $html .= '<a href="#">'; 
                      $html .= '<div class="saswp-r3-lg">';
                      $html .= '<span>';
                      $html .= '<amp-img src="'.esc_url($platform_icon).'" width="70" height="56"></amp-img>'; 
                      $html .= '</span>';
                      $html .= '<span class="saswp-r3-tlt">'.esc_attr($platform_name).'</span>';                      
                      $html .= '</div>';
                      $html .= '<div class="saswp-r3-rtng">';
                      $html .= '<div class="saswp-r3-rtxt">';
                      $html .= '<span class="saswp-r3-num">';
                      $html .= esc_attr(number_format($average_rating, 1));
                      $html .= '</span>';
                      $html .= '<span class="saswp-stars">';
                      $html .= saswp_get_rating_html_by_value($average_rating); 
                      $html .= '</span>';
                      $html .= '</div>';
                      $html .= '<span class="saswp-r3-brv">';
                      $html .= esc_html__('Based on','schema-and-structured-data-for-wp').' '.esc_attr($review_count).' '.esc_html__('Reviews','schema-and-structured-data-for-wp');
                      $html .= '</span>';
                      $html .= '</div>';
                      $html .= '</a>';
                      $html .= '</li>';                                                                     

                    }      
                    
                    $html .= '</ul>';
                    $html .= '</div>';
                        
                    }
                    
                                         
                }
        
        return $html;
        
    }
    public function saswp_create_collection_popup($collection){
                
                   $html          = '';                
                   $html_list     = '';
                
                if($collection){
                        
                        $review_count   = 0;                        
                        $sum_of_rating  = 0;
                        $average_rating = 1;
                            
                        foreach($collection as $value){
                                                        
                            $sum_of_rating += $value['saswp_review_rating'];
                            $review_count++;
                            
                            $date_str = $this->saswp_convert_datetostring($value['saswp_review_date']); 
                            
                            $html_list .= '<li>';
                            $html_list .= '<div class="saswp-r4-b">';
                            $html_list .= '<span class="saswp-r4-str">';
                            $html_list .= saswp_get_rating_html_by_value($value['saswp_review_rating']);
                            $html_list .= '</span>';
                            $html_list .= '<span class="saswp-r4-tx">'. esc_attr($date_str['date']).'</span>';
                            $html_list .= '</div>';
                            
                            $html_list .= '<div class="saswp-r4-cnt">';
                            $html_list .= '<h3>'. esc_attr($value['saswp_reviewer_name']).'</h3>';
                            $html_list .= '<p>'. esc_attr($value['saswp_review_text']).'</p>';
                            $html_list .= '</div>';
                            
                            $html_list .= '</li>';
                            
                        }
                       
                        if($sum_of_rating > 0){
                        
                            $average_rating = $sum_of_rating / $review_count;
                            
                        }                                                                                                                
                    
                    if($review_count > 0){
                        
                        if(saswp_non_amp()){
                         $html .= '<div id="saswp-sticky-review">';
                        $html .= '<div class="saswp-open-class saswp-popup-btn">';
                        $html .= '<div class="saswp-opn-cls-btn">';

                        $html .= '<div class="saswp-onclick-hide">';
                        $html .= '<span>';
                        $html .= saswp_get_rating_html_by_value($average_rating);
                        $html .= '</span>';
                        $html .= '<span class="saswp-r4-rnm">'.esc_attr(number_format ($average_rating, 1)).' from '.esc_attr($review_count).' '.esc_html__('reviews','schema-and-structured-data-for-wp').'</span>';                    
                        $html .= '</div>';

                        $html .= '<div class="saswp-onclick-show">';
                        $html .= '<span>'.esc_html__('Ratings and reviews','schema-and-structured-data-for-wp').'</span>';                    
                        $html .= '<span class="saswp-mines"></span>';                    
                        $html .= '</div>';

                        $html .= '</div>';
                        $html .= '<div id="saswp-reviews-cntn">';
                        $html .= '<div class="saswp-r4-info">';
                        $html .= '<ul>';

                        $html .= '<li class="saswp-r4-r">';
                        $html .= '<span>';
                        $html .= saswp_get_rating_html_by_value($average_rating);
                        $html .= '</span>';
                        $html .= '<span class="saswp-r4-rnm">'. esc_attr(number_format ($average_rating, 1)).' from '. esc_attr($review_count).' '.esc_html__('reviews','schema-and-structured-data-for-wp').'</span>';                    
                        $html .= '</li>';                                        
                        $html .= $html_list;
                        $html .= '</ul>';                    
                        $html .= '</div>';
                        $html .= '</div>';
                        $html .= '</div>';
                        $html .= '</div>';
                        }else{
                            
                        $html .= '<div id="saswp-sticky-review">';    
                        
                        $html .= '<div class="btn" [class]="review==1 ? '."'open-class btn'".': '."'btn'".'"  id="open" >';                        
                        
                        $html .= '<div class="saswp-opn-cls-btn" role="1" tabindex="1" on="tap:AMP.setState({review: ( review==1? 0 : 1 ) })">';
                        $html .= '<div class="saswp-onclick-hide">';
                        $html .= '<span>';
                        $html .= saswp_get_rating_html_by_value($average_rating);
                        $html .= '</span>';
                        $html .= '<span class="saswp-r4-rnm">'.esc_attr(number_format($average_rating, 1) ).' from '.esc_attr($review_count).' '.esc_html__('reviews','schema-and-structured-data-for-wp').'</span>';                    
                        $html .= '</div>';
                        $html .= '<div class="saswp-onclick-show">';
                        $html .= '<span>'.esc_html__('Ratings and reviews','schema-and-structured-data-for-wp').'</span>';                    
                        $html .= '<span class="saswp-mines"></span>';                    
                        $html .= '</div>';
                        $html .= '</div>';
                                                
                        $html .= '<div id="saswp-reviews-cntn">';
                        $html .= '<div class="saswp-r4-info">';
                        $html .= '<ul>';

                        $html .= '<li class="saswp-r4-r">';
                        $html .= '<span>';
                        $html .= saswp_get_rating_html_by_value($average_rating);
                        $html .= '</span>';
                        $html .= '<span class="saswp-r4-rnm">'. esc_attr(number_format($average_rating, 1)).' from '. esc_attr($review_count).' reviews</span>';                    
                        $html .= '</li>';                                        
                        $html .= $html_list;
                        $html .= '</ul>';                    
                        $html .= '</div>';
                        $html .= '</div>';
                        $html .= '</div>';
                        $html .= '</div>';
                            
                        }
                                                
                    }
                                           
                }
                
                return $html;
                
    }
    public function saswp_create_collection_fomo($f_interval, $f_visibility, $collection){
            
        
        $html = '';
        if($collection){
            
            if(saswp_non_amp()){
                
             $i=0;
            
            $html .= '<input type="hidden" id="saswp_fomo_interval" value="'.esc_attr($f_interval).'">';
            foreach ($collection as $value){
                
                    $date_str = $this->saswp_convert_datetostring($value['saswp_review_date']); 

                    $html .= '<div id="'.$i.'" class="saswp-r5">';
                    $html .= '<div class="saswp-r5-r">';                            
                    $html .= '<div class="saswp-r5-lg">';
                    $html .= '<span>';
                    $html .= '<img height="70" width="70" src="'. esc_attr($value['saswp_review_platform_icon']).'"/>';
                    $html .= '</span>';
                    $html .= '</div>';                            
                    $html .= '<div class="saswp-r5-rng">';
                    $html .= saswp_get_rating_html_by_value($value['saswp_review_rating']);
                    $html .='<div class="saswp-r5-txrng">';
                    $html .='<span>'. esc_attr($value['saswp_review_rating']).' Stars</span>';
                    $html .='<span>by</span>';
                    $html .= '<span>'.esc_attr($value['saswp_reviewer_name']).'</span>';
                    $html .='</div>';
                    $html .= '<span class="saswp-r5-dt">'.esc_attr($date_str['date']).'</span>';
                    $html .= '</div>';                            
                    $html .= '</div>';
                    $html .= '</div>';     
    
                    $i++;
            }
            
               
            }else{
               
            $i=0;
            
            $html .='<amp-carousel id="saswp-reviews-fomo-amp" height="50" layout="fixed-height" type="slides"  autoplay delay="10000">';
            
            foreach ($collection as $value){
                
                    $date_str = $this->saswp_convert_datetostring($value['saswp_review_date']); 

                    $html .= '<div id="'.$i.'" class="saswp-r5">';
                    $html .= '<div class="saswp-r5-r">';                            
                    $html .= '<div class="saswp-r5-lg">';
                    $html .= '<span>';
                    $html .= '<img height="70" width="70" src="'. esc_attr($value['saswp_review_platform_icon']).'"/>';
                    $html .= '</span>';
                    $html .= '</div>';                            
                    $html .= '<div class="saswp-r5-rng">';
                    $html .= saswp_get_rating_html_by_value($value['saswp_review_rating']);
                    $html .='<div class="saswp-r5-txrng">';
                    $html .='<span>'. esc_attr($value['saswp_review_rating']).' Stars</span>';
                    $html .='<span> by</span>';
                    $html .= '<span>'.esc_attr($value['saswp_reviewer_name']).'</span>';
                    $html .='</div>';
                    $html .= '<span class="saswp-r5-dt">'.esc_attr($date_str['date']).'</span>';
                    $html .= '</div>';                            
                    $html .= '</div>';
                    $html .= '</div>';     
    
                    $i++;
            }
            $html .= ' </amp-carousel>';
                                                           
            }
        }
                
        return $html;
        
    }
    
    public function saswp_collection_desing_available(){
     
        global $post;
        $design = '';
        
        if(is_object($post)){
               
                $pattern = get_shortcode_regex();

                if (   preg_match_all( '/'. $pattern .'/s', $post->post_content, $matches )
                    && array_key_exists( 2, $matches )
                    && in_array( 'saswp-reviews-collection', $matches[2] ) )
                {
       
                foreach ($matches[0] as $matche){

                 $mached = rtrim($matche, ']'); 
                 $mached = ltrim($mached, '[');
                 $mached = trim($mached);
                 $attr   = shortcode_parse_atts('['.$mached.' ]');  

                 $design = get_post_meta($attr['id'], 'saswp_collection_design',true);
                 
                 break;
                 }   
        
             }
                              
          }
                    
        return $design;   
                
    }
        	                      
}

$saswp_service_obj = new saswp_reviews_service();
$saswp_service_obj->saswp_service_hooks();
?>