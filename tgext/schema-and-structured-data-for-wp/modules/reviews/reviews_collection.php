<?php
/**
 * Reviews Collection  Class
 *
 * @author   Magazine3
 * @category Admin
 * @path     reviews/reviews_collection
 * @Version 1.9.17
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

class SASWP_Reviews_Collection {
        
        /**
         * Static private variable to hold instance this class
         * @var type 
         */
        private static $instance;
        private $_service = null;

        private function __construct() {
            
          if($this->_service == null){
              
              $this->_service = new saswp_reviews_service();
              
          }  
             
          add_filter( 'get_edit_post_link', array($this, 'saswp_set_collection_edit_link' ), 99, 3); 
          add_action( 'admin_menu', array($this, 'saswp_add_collection_menu_links' ),20);
          add_action( 'init', array($this, 'saswp_register_collection_post_type' ),20);
          add_action( 'admin_init', array($this, 'saswp_save_collection_data' ));
          add_action( 'wp_ajax_saswp_add_to_collection', array($this, 'saswp_add_to_collection' ));
          add_action( 'wp_ajax_saswp_get_collection_platforms', array($this, 'saswp_get_collection_platforms' ));
          add_action( 'amp_post_template_css', array($this, 'saswp_reviews_collection_amp_css'));
          add_action( 'amp_post_template_data', array($this, 'saswp_reviews_collection_amp_script'));                                   
          add_shortcode( 'saswp-reviews-collection', array($this, 'saswp_reviews_collection_shortcode_render' ));
                                 
        }
        
         /**
         * Return the unique instance 
         * @return type instance
         * @since version 1.9.17
         */
        public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
        }
        
        public static function saswp_add_collection_menu_links(){
            
             add_submenu_page( 'edit.php?post_type=saswp',
                esc_html__( 'Structured Data', 'schema-and-structured-data-for-wp' ),
                esc_html__( '', 'schema-and-structured-data-for-wp' ),
                'manage_options',
                'collection',
                array($this, 'saswp_admin_collection_interface_render'));   
            
        }
        
        public static function saswp_set_collection_edit_link($link, $post_id, $context){
                
                if (function_exists('get_current_screen') && (isset(get_current_screen()->id) && get_current_screen()->id == 'edit-saswp-collections' ) && $context == 'display') {

                        return wp_nonce_url(admin_url('admin.php?post_id='.$post_id.'&page=collection'), '_wpnonce');

                } else {

                        return $link;

                }
            
        }
        
        public function saswp_reviews_collection_amp_script($data){
            
            $design = $this->_service->saswp_collection_desing_available();
            
            if($design == 'gallery' || $design == 'fomo'){
                
                if ( empty( $data['amp_component_scripts']['amp-carousel'] ) ) {
                     $data['amp_component_scripts']['amp-carousel'] = "https://cdn.ampproject.org/v0/amp-carousel-latest.js";
                }
            }
            
            if($design == 'popup' || $design == 'gallery' || $design == 'fomo'){
                
                if ( empty( $data['amp_component_scripts']['amp-bind'] ) ) {
                    $data['amp_component_scripts']['amp-bind'] = "https://cdn.ampproject.org/v0/amp-bind-latest.js";
                }
                
            }
            
           return $data;
                        
        }
        
        public function saswp_reviews_collection_amp_css(){            
            
           $global_css  =  SASWP_PLUGIN_DIR_PATH . 'admin_section/css/amp/collection-front-global.css'; 
           $grid_css    =  SASWP_PLUGIN_DIR_PATH . 'admin_section/css/amp/collection-front-grid.css';
           $fomo_css    =  SASWP_PLUGIN_DIR_PATH . 'admin_section/css/amp/collection-front-fomo.css';
           $gallery_css =  SASWP_PLUGIN_DIR_PATH . 'admin_section/css/amp/collection-front-gallery.css';
           $popup_css   =  SASWP_PLUGIN_DIR_PATH . 'admin_section/css/amp/collection-front-popup.css';
           $badge_css   =  SASWP_PLUGIN_DIR_PATH . 'admin_section/css/amp/collection-front-badge.css';
           
           $design = $this->_service->saswp_collection_desing_available();
           
           if($design){
               
                echo @file_get_contents($global_css);
                
                switch ($design) {
                    case 'grid':
                            echo @file_get_contents($grid_css);
                        break;
                    case 'gallery':
                            echo @file_get_contents($gallery_css);
                        break;
                    case 'badge':
                            echo @file_get_contents($badge_css);
                        break;
                    case 'popup':
                            echo @file_get_contents($popup_css);
                        break;
                    case 'fomo':
                            echo @file_get_contents($fomo_css);
                        break;

                    default:
                        break;
                }
               
           }
           
        }
        
        public function saswp_register_collection_post_type(){
                        
            $collections = array(
                    'labels' => array(
                        'name' 			=> esc_html__( 'Collections', 'schema-and-structured-data-for-wp' ),	        
                        'add_new' 		=> esc_html__( 'Add Collection', 'schema-and-structured-data-for-wp' ),
                        'add_new_item'  	=> esc_html__( 'Edit Collection', 'schema-and-structured-data-for-wp' ),
                        'edit_item'             => esc_html__( 'Edit Collection','schema-and-structured-data-for-wp'),                
                    ),
                    'public' 		    => true,
                    'has_archive' 	    => true,
                    'exclude_from_search'   => true,
                    'publicly_queryable'    => true,
                    //'show_in_menu'          => 'edit.php?post_type=saswp',                
                    'show_in_menu'          => false,                
                    'show_ui'               => true,
                    'show_in_nav_menus'     => true,			
                    'show_admin_column'     => true,        
                    'rewrite'               => false,  
            );
            register_post_type( 'saswp-collections', $collections );   
        }
        
        public function saswp_get_collection_platforms(){
                        
            if ( ! isset( $_GET['saswp_security_nonce'] ) ){
                return; 
            }
            if ( !wp_verify_nonce( $_GET['saswp_security_nonce'], 'saswp_ajax_check_nonce' ) ){
               return;  
            }
            
            $collection_id = intval($_GET['collection_id']);            
            
            if($collection_id){
                
            $reviews_list = get_post_meta($collection_id, 'saswp_platform_ids', true);
             
            if($reviews_list){
                
                echo json_encode(array('status' => true, 'message'=> $reviews_list));
                                                  
            }else{
                
                echo json_encode(array('status' => false, 'message'=> 'Data not found'));
                
            }
                                         
            }else{
                
                echo json_encode(array('status' => false, 'message'=> 'Collection id is missing'));
                
            }
                        
            wp_die();
        }
        
        public function saswp_add_to_collection(){
                        
            if ( ! isset( $_GET['saswp_security_nonce'] ) ){
                return; 
            }
            if ( !wp_verify_nonce( $_GET['saswp_security_nonce'], 'saswp_ajax_check_nonce' ) ){
               return;  
            }
            
            $platform_id = intval($_GET['platform_id']);
            $rvcount     = intval($_GET['rvcount']);
            
            if($platform_id  && $rvcount){
                                
            $reviews_list = $this->_service->saswp_get_reviews_list_by_parameters(null, $platform_id, $rvcount); 
             
            if($reviews_list){
                
                echo json_encode(array('status' => true, 'message'=> $reviews_list));
                                                  
            }else{
                
                echo json_encode(array('status' => false, 'message'=> 'Data not found'));
                
            }
                                         
            }else{
                
                echo json_encode(array('status' => false, 'message'=> 'Platform id or review count is missing'));
                
            }
                        
            wp_die();
        }
                            
        public function saswp_reviews_collection_shortcode_render($attr){
            
            $html = $htmlp = '';
            
            if(!is_admin()){
                
                if(isset($attr['id'])){
                
                $collection          = array();  
                $total_collection    = array();
                $dots = $f_interval = $f_visibility = $arrow = 1;
                $g_type = '';
                
                $collection_data = get_post_meta($attr['id'], $key='', true);
                
                $design       = $collection_data['saswp_collection_design'][0];
                $cols         = $collection_data['saswp_collection_cols'][0];
                
                if(isset($collection_data['saswp_gallery_arrow'][0])){
                    
                    $arrow        = $collection_data['saswp_gallery_arrow'][0];
                }
                
                
                if(isset($collection_data['saswp_gallery_dots'][0])){
                    $dots         = $collection_data['saswp_gallery_dots'][0];
                }
                            
                if(isset($collection_data['saswp_collection_gallery_type'][0])){
                    $g_type       = $collection_data['saswp_collection_gallery_type'][0];
                }
                
                if(isset($collection_data['saswp_fomo_interval'][0])){
                    $f_interval   = $collection_data['saswp_fomo_interval'][0];
                }
                
                if(isset($collection_data['saswp_fomo_visibility'][0])){
                    $f_visibility = $collection_data['saswp_fomo_visibility'][0];
                }
                                
                $sorting      = $collection_data['saswp_collection_sorting'][0];                
                $platform_id  = unserialize($collection_data['saswp_platform_ids'][0]);                
                
                if($platform_id){
                    
                    foreach ($platform_id as $key => $val){
                        
                        $reviews_list = $this->_service->saswp_get_reviews_list_by_parameters(null, $key, $val); 
                        $total_collection[] = $reviews_list;
                        
                        if($reviews_list){
                            
                            $collection = array_merge($collection, $reviews_list);
                        }
                        
                    }
                    
                    $collection = $this->_service->saswp_sort_collection($collection, $sorting);
                                                                                      
                }
                                                
                if($collection){
                
                    switch($design) {
                    
                    case "grid":
                        
                        $html = $this->_service->saswp_create_collection_grid($cols, $collection);
                        
                        break;
                        
                    case 'gallery':
                        
                        $html = $this->_service->saswp_create_collection_slider($g_type, $arrow, $dots, $collection);
                        
                        break;
                    
                    case 'badge':
                        
                        $html = $this->_service->saswp_create_collection_badge($total_collection);
                        
                        break;
                        
                    case 'popup':
                        
                        $html = $this->_service->saswp_create_collection_popup($collection);
                        
                        break;
                    
                    case 'fomo':
                        
                        $html = $this->_service->saswp_create_collection_fomo($f_interval, $f_visibility, $collection);
                        
                        
                        break;
                                                                
                }
                                        
                }
                                              
            }
            
                                
            }
            
            $htmlp .= '<div class="saswp-r">';
            $htmlp .= $html;  
            $htmlp .= '</div>';
            return $htmlp;
                                    
        }
        
        public function saswp_admin_collection_interface_render(){
            
             if ( ! current_user_can( 'manage_options' ) ) return;
             if ( !wp_verify_nonce( $_GET['_wpnonce'], '_wpnonce' ) ) return;
             
            $post_meta = array();
            $post_id   = null;            

            if(isset($_GET['post_id'])){

                $post_id = intval($_GET['post_id']);

                $post_meta = get_post_meta($post_id, $key='', true );            


            } else{

                $post    = get_default_post_to_edit( 'saswp-google-review', true );
                $post_id = intval($post->ID);
            }
            
            $coll_desing = array(
                'grid'     => 'Grid',
                'gallery'  => 'Gallery',
                'badge'    => 'Badge',
                'popup'    => 'PopUp',
                'fomo'     => 'Fomo',
            );
       
            $coll_sorting = array(
                'recent'     => 'Recent',
                'oldest'     => 'Oldest',
                'newest'     => 'Newest',
                'highest'    => 'Highest Rating',
                'lowest'     => 'Lowest Rating',
                'random'     => 'Random'
            );
                        
            $coll_display_type = array(
                'shortcode'               => 'Shortcode',  
               // 'before_the_content'      => 'Before the content',
               // 'between_the_content'     => 'Beetween the content',
               // 'after_the_content'       => 'After the content',                              
            );
            
            ?> 

            <div class="saswp-collection-wrapper">  
                
                <form method="post" action="post.php">
                    <input type="hidden" name="saswp_collection_nonce" value="<?php echo wp_create_nonce('saswp_collection_nonce_data');    ?>">
                    <input type="hidden" name="post_type" value="saswp-collections">
                    <input type="hidden" name="saswp-collection-page" value="1">
                    <input type="hidden" id="saswp_collection_id" name="saswp_collection_id" value="<?php echo esc_attr($post_id); ?>">                   
                    
                    <div class="saswp-collection-container">
                      <div class="saswp-collection-body">
                        <div class="saswp-collection-lp">
                            <div class="saswp-collection-title">
                                <input type="text" value="<?php if(get_the_title($post_id) == 'Auto Draft'){ echo 'Untitled'; }else{ echo get_the_title($post_id); } ?>" id="saswp_collection_title" name="saswp_collection_title">
                            </div>
                            <span class="spinner saswp-spinner"></span>
                            <div class="saswp-collection-preview">                                
                                <!-- Collections html will be loaded on ajax call -->
                            </div>
                        </div><!-- /.saswp-collection-lp --> 
                        <div class="saswp-collection-settings">
                            <ul>
                                <li>
                                    <a class="saswp-accordion"><?php echo esc_html__('Reviews Source','schema-and-structured-data-for-wp'); ?></a>
                                    <div class="saswp-accordion-panel">
                                      <?php $platforms = saswp_get_terms_as_array();
                                          if($platforms){
                                          global $wpdb;
                                          $exists_platforms = $wpdb->get_results("
                                            SELECT meta_value, count(meta_value) as meta_count FROM {$wpdb->postmeta} WHERE `meta_key`='saswp_review_platform' group by meta_value",
                                            ARRAY_A
                                         );  ?>
                                        <div class="saswp-plf-lst-rv-cnt">
                                          <?php
                                          echo '<select id="saswp-plaftorm-list" name="saswp-plaftorm-list">';
                                       
                                          $active_options   = '';
                                          $inactive_options = '';
                                          
                                          foreach($platforms as $key => $val){
                                            if(in_array($key, array_column($exists_platforms, 'meta_value'))){
                                                   $active_options .= '<option value="'.esc_attr($key).'">'.esc_attr($val).'</option>';
                                            }else{
                                               $inactive_options.= '<option value="'.esc_attr($key).'" disabled>'.esc_attr($val).'</option>';
                                            }
                                          }
                                          
                                         echo '<optgroup label="Active">';
                                         echo $active_options;
                                         echo '</optgroup>';
                                         echo '<optgroup label="InActive">';
                                         echo $inactive_options;
                                         echo '</optgroup>';
                                         echo '</select>';
                                                    
                                        } ?>   
                                        <input type="number" id="saswp-review-count" name="saswp-review-count" min="0" value="5">
                                        <a class="button button-default saswp-add-to-collection"><?php echo esc_html__('Add','schema-and-structured-data-for-wp'); ?></a>
                                      </div>
                                      <div class="saswp-platform-added-list">  
                                          
                                      </div>
                                    </div>
                                </li>
                                <li>                                     
                                    <a class="saswp-accordion"><?php echo esc_html__('Presentation','schema-and-structured-data-for-wp'); ?></a>
                                    <div class="saswp-accordion-panel">
                                        <div class="saswp-dp-dsg">
                                        <lable><?php echo esc_html__('Design','schema-and-structured-data-for-wp'); ?></lable>  
                                        <select name="saswp_collection_design" class="saswp-collection-desing saswp-coll-settings-options">
                                            <?php
                                            foreach($coll_desing as $key => $val){
                                                
                                                echo '<option value="'.esc_attr($key).'" '.($post_meta['saswp_collection_design'][0] == $key ? 'selected':'').' >'.esc_html__( $val , 'schema-and-structured-data-for-wp' ).'</option>';
                                            }
                                            ?>                                    
                                         </select>
                                        </div>
                                        <div class="saswp-dp-dsg saswp-coll-options saswp-grid-options saswp-dp-dtm">
                                        <label><?php echo esc_html__( 'Columns', 'schema-and-structured-data-for-wp' ); ?></label>
                                        <input type="number" id="saswp-collection-cols" name="saswp_collection_cols" min="0" value="<?php echo (isset($post_meta['saswp_collection_cols'][0]) ? $post_meta['saswp_collection_cols'][0] : '2' ); ?>" class="saswp-number-change saswp-coll-settings-options saswp-coll-options saswp-grid-options">    
                                        </div>
                                        <div class="saswp-dp-dsg saswp-dp-dtm saswp-slider-options saswp-coll-options">
                                         <label><?php echo esc_html__( 'Slider Type', 'schema-and-structured-data-for-wp' ); ?></label>
                                        <select name="saswp_collection_gallery_type" id="saswp_collection_gallery_type" class="saswp-slider-type saswp-slider-options saswp_hide saswp-coll-settings-options saswp-coll-options">
                                            <option value="slider" <?php echo (isset($post_meta['saswp_collection_gallery_type'][0]) && $post_meta['saswp_collection_gallery_type'][0] == 'slider'  ? 'selected' : '' ); ?>><?php echo esc_html__( 'Slider', 'schema-and-structured-data-for-wp' ); ?></option>
                                            <option value="carousel" <?php echo (isset($post_meta['saswp_collection_gallery_type'][0]) && $post_meta['saswp_collection_gallery_type'][0] == 'carousel'  ? 'selected' : '' ); ?>><?php echo esc_html__( 'Carousel', 'schema-and-structured-data-for-wp' ); ?></option>
                                        </select>
                                        </div>
                                        <div class="saswp-slider-display saswp-slider-options saswp_hide saswp-coll-settings-options saswp-coll-options">
                                            <span><input type="checkbox" id="saswp_gallery_arrow" name="saswp_gallery_arrow" value="1" <?php echo (isset($post_meta['saswp_gallery_arrow'][0]) && $post_meta['saswp_gallery_arrow'][0] == 1 ? 'checked' : '' ); ?>> <?php echo esc_html__('Arrows','schema-and-structured-data-for-wp'); ?></span>
                                            <span><input type="checkbox" id="saswp_gallery_dots" name="saswp_gallery_dots" value="1" <?php echo (isset($post_meta['saswp_gallery_dots'][0]) && $post_meta['saswp_gallery_dots'][0] == 1 ? 'checked' : '' ); ?>> <?php echo esc_html__('Dots','schema-and-structured-data-for-wp'); ?></span>
                                        </div>
                                        
                                        <div class="saswp-fomo-options saswp_hide saswp-coll-options"> 
                                            <div class="saswp-dp-dsg saswp-dp-dtm">
                                            <span><?php echo esc_html__('Delay Time','schema-and-structured-data-for-wp'); ?>
                                            </span>
                                            <input type="number" id="saswp_fomo_interval" name="saswp_fomo_interval" class="saswp-number-change" min="1" value="<?php echo (isset($post_meta['saswp_fomo_interval'][0]) ? $post_meta['saswp_fomo_interval'][0] : '3' ); ?>"> 
                                            </div>                                                                           
                                        </div>                                                                        
                                    </div>
                                </li>
                              <li>

                                <a class="saswp-accordion"><?php echo esc_html__('Filter','schema-and-structured-data-for-wp'); ?></a>
                                <div class="saswp-accordion-panel">
                                    <div class="saswp-dp-dsg">
                                        <lable><?php echo esc_html__('Sorting','schema-and-structured-data-for-wp'); ?></lable>  
                                        <select name="saswp_collection_sorting" class="saswp-collection-sorting saswp-coll-settings-options">                                      
                                          <?php
                                            foreach($coll_sorting as $key => $val){
                                                echo '<option value="'.esc_attr($key).'" '.($post_meta['saswp_collection_sorting'][0] == $key ? 'selected':'').' >'.esc_html__( $val , 'schema-and-structured-data-for-wp' ).'</option>';
                                                
                                            }
                                            
                                            ?>
                                        </select>
                                    </div>
                                </div>
                              </li>
                              <li>
                                <a class="saswp-accordion"><?php echo esc_html__('Display','schema-and-structured-data-for-wp'); ?></a>
                                <div class="saswp-accordion-panel">
                                    <div class="saswp-dp-dsg">
                                        <label><?php echo esc_html__( 'Display Type', 'schema-and-structured-data-for-wp' ); ?></label>
                                        <select class="saswp-collection-display-method" name="saswp_collection_display_type">
                                            <?php
                                            foreach($coll_display_type as $key => $val){
                                                
                                                echo '<option value="'.esc_attr($key).'" '.($post_meta['saswp_collection_display_type'][0] == $key ? 'selected':'').' >'.esc_html__( $val , 'schema-and-structured-data-for-wp' ).'</option>';
                                            }
                                            ?> 
                                        </select>
                                    </div>
                                    
                                        <div id="motivatebox" class="saswp_hide saswp-collection-shortcode">
                                            <span class="motivate">
                                            [saswp-reviews-collection id="<?php echo $post_id; ?>"]
                                            </span>
                                        </div>
                                   
                                </div>
                              </li>
                            </ul>
                            <div class="saswp-sv-btn">
                                <button type="submit" class="button button-primary" > 
                                    <?php echo esc_html__('Save Menu','schema-and-structured-data-for-wp'); ?>
                                </button>
                            </div>   
                        </div><!-- /.saswp-collection-body -->
                      </div><!-- /.saswp-collection-body -->
                    </div><!-- /.saswp-collection-container -->
                </form>    
            </div><!-- /.saswp-collection-wrapper -->

            <?php
                                    
        }
                        
        public function saswp_save_collection_data(){
                                    
            if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
            if ( ! current_user_can( 'manage_options' ) ) return ;		    		
            if ( ! isset( $_POST['saswp_collection_nonce'] ) || ! wp_verify_nonce( $_POST['saswp_collection_nonce'], 'saswp_collection_nonce_data' ) ) return;            
            
            if(isset($_POST['saswp_collection_id'])){
                      
            $post_id         = intval($_POST['saswp_collection_id']);
            $collection_page = intval($_POST['saswp-collection-page']);
            $post_title      = sanitize_text_field($_POST['saswp_collection_title']);
                        
            $post = array(                 
                    'ID'                    => $post_id,
                    'post_title'            => $post_title,                    
                    'post_status'           => 'publish',
                    'post_name'             => $post_title,                                        
                    'post_type'             => 'saswp-collections',                                                            
                );
                                          
            wp_update_post($post);                                      
            $post_meta = array();            
            $post_meta['saswp_collection_design']       = sanitize_text_field($_POST['saswp_collection_design']);                        
            $post_meta['saswp_collection_sorting']      = sanitize_text_field($_POST['saswp_collection_sorting']);
            $post_meta['saswp_collection_display_type'] = sanitize_text_field($_POST['saswp_collection_display_type']);
            $post_meta['saswp_collection_gallery_type'] = sanitize_text_field($_POST['saswp_collection_gallery_type']);
            $post_meta['saswp_collection_cols']         = intval($_POST['saswp_collection_cols']);
            $post_meta['saswp_gallery_arrow']           = intval($_POST['saswp_gallery_arrow']);
            $post_meta['saswp_gallery_dots']            = intval($_POST['saswp_gallery_dots']);            
            $post_meta['saswp_fomo_interval']           = intval($_POST['saswp_fomo_interval']);
            $post_meta['saswp_fomo_visibility']         = intval($_POST['saswp_fomo_visibility']);                                                        
            $post_meta['saswp_platform_ids']            = array_map('intval', $_POST['saswp_platform_ids']);
                        
            if(!empty($post_meta)){
                
                foreach($post_meta as $meta_key => $meta_val){
                    
                    update_post_meta($post_id, $meta_key, $meta_val); 
                    
                }
                
            }
                                    
            if($collection_page == 1){
                
                $current_url = htmlspecialchars_decode(wp_nonce_url(admin_url('admin.php?post_id='.$post_id.'&page=collection'), '_wpnonce'));           
                wp_redirect( $current_url );
                exit;
            }
            
         }
                                    
        }
            
}

if ( class_exists( 'SASWP_Reviews_Collection') ) {
	SASWP_Reviews_Collection::get_instance();
}