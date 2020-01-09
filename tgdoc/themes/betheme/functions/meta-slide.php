<?php
/**
 * Slide custom meta fields.
 *
 * @package Betheme
 * @author Muffin group
 * @link https://muffingroup.com
 */

/**
 * Create new post type
 */

if (! function_exists('mfn_slide_post_type')) {
	function mfn_slide_post_type()
	{
		$slide_item_slug = mfn_opts_get('slide-slug', 'slide-item');

		$labels = array(
			'name' => esc_html__('Slides', 'mfn-opts'),
			'singular_name' => esc_html__('Slide', 'mfn-opts'),
			'add_new' => esc_html__('Add New', 'mfn-opts'),
			'add_new_item' => esc_html__('Add New Slide', 'mfn-opts'),
			'edit_item' => esc_html__('Edit Slide', 'mfn-opts'),
			'new_item' => esc_html__('New Slide', 'mfn-opts'),
			'view_item' => esc_html__('View Slides', 'mfn-opts'),
			'search_items' => esc_html__('Search Slides', 'mfn-opts'),
			'not_found' => esc_html__('No slides found', 'mfn-opts'),
			'not_found_in_trash' => esc_html__('No slides found in Trash', 'mfn-opts'),
		  );

		$args = array(
			'labels' => $labels,
			'menu_icon' => 'dashicons-slides',
			'public' => false,
			'show_ui' => true,
			'supports' => array('title', 'page-attributes', 'thumbnail'),
		);

		register_post_type('slide', $args);

		register_taxonomy('slide-types', 'slide', array(
			'label' =>  esc_html__('Slide categories', 'mfn-opts'),
			'hierarchical' => true,
		));
	}
}
add_action('init', 'mfn_slide_post_type');

/**
 * Edit columns
 */

if (! function_exists('mfn_slide_edit_columns')) {
	function mfn_slide_edit_columns($columns)
	{
		$newcolumns = array(
			"cb" => '<input type="checkbox" />',
			"slide_thumbnail"	=> esc_html__('Photo', 'mfn-opts'),
			"title" => esc_html__('Title', 'mfn-opts'),
			"slide_types" => esc_html__('Categories', 'mfn-opts'),
			"slide_order" => esc_html__('Order', 'mfn-opts'),
		);
		$columns = array_merge($newcolumns, $columns);

		return $columns;
	}
}
add_filter("manage_edit-slide_columns", "mfn_slide_edit_columns");

/**
 * Custom columns
 */

if (! function_exists('mfn_slide_custom_columns')) {
	function mfn_slide_custom_columns($column)
	{
		global $post;
		switch ($column) {
			case "slide_thumbnail":
				if (has_post_thumbnail()) {
					the_post_thumbnail('50x50');
				}
				break;
			case "slide_types":
				echo get_the_term_list($post->ID, 'slide-types', '', ', ', '');
				break;
			case "slide_order":
				echo esc_attr($post->menu_order);
				break;
		}
	}
}
add_action("manage_posts_custom_column", "mfn_slide_custom_columns");

/**
 *	Define Metabox Fields
 */

$mfn_slide_meta_box = array(
	'id' => 'mfn-meta-slide',
	'title' => esc_html__('Slide Options', 'mfn-opts'),
	'page' => 'slide',
	'context' => 'normal',
	'priority' => 'high',
	'fields' => array(

		array(
			'id' => 'mfn-post-desc',
			'type' => 'custom',
			'title' => __('Featured Image size', 'mfn-opts'),
			'sub_desc' => __('recommended', 'mfn-opts'),
			'desc' => __('1630px x 860px', 'mfn-opts'),
			'action' => 'description',
		),

		array(
			'id' => 'mfn-post-link',
			'type' => 'text',
			'title' => __('Link', 'mfn-opts'),
		),

		array(
			'id' => 'mfn-post-target',
			'type' => 'select',
			'title' => __('Target', 'mfn-opts'),
			'options'	=> array(
				0 => __('Default | _self', 'mfn-opts'),
				1 => __('New Tab or Window | _blank', 'mfn-opts'),
				'lightbox' => __('Lightbox (image or embed video)', 'mfn-opts'),
			),
		),

		array(
			'id' => 'mfn-post-desc',
			'type' => 'textarea',
			'title' => __('Description', 'mfn-opts'),
			'sub_desc' => __('for Slider Style: Image & Text', 'mfn-opts'),
		),

	),
);

/**
 * Add metabox to edit page
 */

if (! function_exists('mfn_slide_meta_add')) {
	function mfn_slide_meta_add()
	{
		global $mfn_slide_meta_box;
		add_meta_box($mfn_slide_meta_box['id'], $mfn_slide_meta_box['title'], 'mfn_slide_show_box', $mfn_slide_meta_box['page'], $mfn_slide_meta_box['context'], $mfn_slide_meta_box['priority']);
	}
}
add_action('admin_menu', 'mfn_slide_meta_add');

/**
 * Callback function to show fields in meta box
 */

if (! function_exists('mfn_slide_show_box')) {
	function mfn_slide_show_box()
	{
		global $MFN_Options, $mfn_slide_meta_box, $post;
		$MFN_Options->_enqueue();

		echo '<div id="mfn-wrapper">';
			echo '<input type="hidden" name="mfn-builder-nonce" value="'. wp_create_nonce('mfn-builder-nonce') .'" />';
			echo '<table class="form-table">';
				echo '<tbody>';
					foreach ($mfn_slide_meta_box['fields'] as $field) {
						$meta = get_post_meta($post->ID, $field['id'], true);
						if (! key_exists('std', $field)) {
							$field['std'] = '';
						}
						$meta = ($meta || $meta==='0') ? $meta : stripslashes(htmlspecialchars(($field['std']), ENT_QUOTES));
						mfn_meta_field_input($field, $meta);
					}
				echo '</tbody>';
			echo '</table>';
		echo '</div>';
	}
}

/**
 * Save data when post is edited
 */

if (! function_exists('mfn_slide_save_data')) {
	function mfn_slide_save_data($post_id)
	{
		global $mfn_slide_meta_box;

		// verify nonce

		if (key_exists('mfn-builder-nonce', $_POST)) {
			if (! wp_verify_nonce($_POST['mfn-builder-nonce'], 'mfn-builder-nonce')) {
				return $post_id;
			}
		}

		// check autosave

		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return $post_id;
		}

		// check permissions

		if ((key_exists('post_type', $_POST)) && ('page' == $_POST['post_type'])) {
			if (!current_user_can('edit_page', $post_id)) {
				return $post_id;
			}
		} elseif (!current_user_can('edit_post', $post_id)) {
			return $post_id;
		}

		foreach ($mfn_slide_meta_box['fields'] as $field) {
			$old = get_post_meta($post_id, $field['id'], true);
			if (key_exists($field['id'], $_POST)) {
				$new = $_POST[$field['id']];
			} else {
				continue;
			}

			if (isset($new) && $new != $old) {
				update_post_meta($post_id, $field['id'], $new);
			} elseif ('' == $new && $old) {
				delete_post_meta($post_id, $field['id'], $old);
			}
		}
	}
}
add_action('save_post', 'mfn_slide_save_data');
