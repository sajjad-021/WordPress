<?php
/*
Plugin Name: WP OpenSearch
Version: 1.0.0
Description: Enable OpenSearch on your website
Author: Vô Minh
Plugin URI: http://laptrinh.senviet.org
*/

include dirname( __FILE__ ) . '/scb/load.php';
include_once dirname( __FILE__ ) . '/core.php';
function _wpos_init() {

// Creating an options object
$options = new scbOptions( 'wpos_options', __FILE__, array(
    'short_name' => get_bloginfo('name'),
    'long_name' => '',
    'description' => 'foo',
    'attribution' => 'Copyright© ' . date('Y') . ' ' . get_bloginfo('name'),
    'haveSsuggestion' => false,
    'autodiscovery' => false,
    'contact' => get_bloginfo('admin_email'),
    'image' => '',
    'syndication_right' => "open",
    'example_query' => "Anything",
    'adult_content'=> false,
    'tags' => ''
) );

    Wpos_Code::instance()->init();
    if ( is_admin() ) {

        require_once( dirname( __FILE__ ) . '/wpos_AdminPage.php' );
new WP_OS_Admin_Page( __FILE__, $options );

register_activation_hook( __FILE__, array('Wpos_Code','wpos_activate') );
register_deactivation_hook( __FILE__, array('Wpos_Code','wpos_deactivate') );
    }
}
scb_init( '_wpos_init' );
