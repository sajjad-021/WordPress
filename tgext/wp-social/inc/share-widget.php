<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
* Class Name : xs_social_widget;
* Class Details : Create Widget for XS Social Login Plugin
* 
* @params : void
* @return : void
*
* @since : 1.0
*/
use \XsSocialCounter\Settings as Settings;

class xs_share_widget extends WP_Widget {
	public $styleArr;
	public function __construct() {
		$this->styleArr = Settings::$share_style;
		
		parent::__construct(

			'xs_share_widget', 

			__('WSLU Social Share', 'wslu-social-login'), 
		 
			array( 'description' => __( 'Wp Social Share System for Facebook, Twitter, Linkedin, Pinterest & 13+ providers.', 'wslu-social-login' ), ) 
		);
	}
	
	public static function register(){
		register_widget( 'xs_share_widget' );
	}
		
	public function widget( $args, $instance ) {
		extract( $args );
		
		$title 		= isset($instance['title']) ? $instance['title'] : '';
		$layout 		= isset($instance['layout']) ? $instance['layout'] : '';
		$customclass = isset($instance['customclass']) ? $instance['customclass'] : '';
		$box_only 	= isset($instance['box_only']) ? $instance['box_only'] : false;
		
		$share = New \XsSocialShare\Share(false);
		
		$config = [];
		$config['class'] = $customclass;
		$config['style'] = $layout;
		
		if( !$box_only ){
			echo $before_widget . $before_title . $title . $after_title;
		}

		echo  $share->get_share_data( 'all' , $config);

		if( !$box_only ){
			echo $after_widget;
		}
	}

	public function form( $instance ) {
		$defaults = array( 'title' => __( 'SOCIAL SHARE' , 'wslu-social-login' )  , 'layout' => 'floating' , 'box_only' => false, 'providers' => '', 'customclass' => '');
		$instance = wp_parse_args( (array) $instance, $defaults );
		
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Share Title:' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'layout' ); ?>"><?php _e( 'Style :' , 'wslu-social-login' ) ?></label>
			<select class="widefat" id="<?php echo $this->get_field_id( 'layout' ); ?>" name="<?php echo $this->get_field_name( 'layout' ); ?>" >
				<?php
				 foreach($this->styleArr as $k=>$v):
				?>
					<option value="<?php echo $k;?>" <?php echo ($instance['layout'] == $k ) ? 'selected' : ''; ?>> <?php _e($v, 'wslu-social-login'); ?> </option>
				<?php endforeach;?>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'box_only' ); ?>"><?php _e( 'Show the Social Box only :' , 'wslu-social-login' ) ?></label>
			<input id="<?php echo $this->get_field_id( 'box_only' ); ?>" name="<?php echo $this->get_field_name( 'box_only' ); ?>" value="true" <?php if( $instance['box_only'] ) echo 'checked="checked"'; ?> type="checkbox" />
			<br /><small><?php _e( 'Will show only counter block without title.' , 'wslu-social-login' ) ?></small>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'customclass' ); ?>"><?php _e( 'Custom Class :' , 'wslu-social-login' ) ?> </label>
			<input id="<?php echo $this->get_field_id( 'customclass' ); ?>" name="<?php echo $this->get_field_name( 'customclass' ); ?>" value="<?php echo $instance['customclass']; ?>" class="widefat" type="text" />
		</p>
	<?php 
	}
		 
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['layout'] 	= $new_instance['layout'] ;
		$instance['title'] 		= $new_instance['title'] ;
		$instance['box_only'] 	= $new_instance['box_only'] ;
		$instance['customclass'] 	= $new_instance['customclass'] ;
		return $instance;
	}
} 

add_action( 'widgets_init', 'xs_share_widget::register' );
