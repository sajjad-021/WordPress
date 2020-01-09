<?php

/*
Plugin Name: Export All URLs
Plugin URI: http://www.AtlasGondal.com/
Description: This plugin allows you to extract posts/pages Title, URL and Categories. You can write output in CSV or in dashboard.
Version: 3.6
Author: Atlas Gondal
Author URI: http://www.AtlasGondal.com/
License: GPL v2 or higher
License URI: License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/


function extract_all_urls_nav(){

    add_options_page( 'Export All URLs', 'Export All URLs', 'manage_options', 'extract-all-urls-settings', 'include_settings_page' );

}

add_action( 'admin_menu', 'extract_all_urls_nav' );

function include_settings_page(){

    include(plugin_dir_path(__FILE__) . 'extract-all-urls-settings.php');

}

function export_all_urls_on_activate() {
    set_transient( 'eau_export_all_urls_activation_redirect', true, 30 );
}

register_activation_hook( __FILE__, 'export_all_urls_on_activate' );

function redirect_on_export_all_urls_activation() {

    if ( ! get_transient( 'eau_export_all_urls_activation_redirect' ) ) {
        return;
    }

    delete_transient( 'eau_export_all_urls_activation_redirect' );

    wp_safe_redirect( add_query_arg( array( 'page' => 'extract-all-urls-settings' ), admin_url( 'options-general.php' ) ) );

}
add_action( 'admin_init', 'redirect_on_export_all_urls_activation' );

add_filter( 'admin_footer_text', 'eau_admin_footer_text' );
function eau_admin_footer_text( $footer_text ) {

    $current_screen = get_current_screen();

    $is_export_all_urls_screen = ( $current_screen && false !== strpos( $current_screen->id, 'settings_page_extract-all-urls-settings' ) );

    if ( $is_export_all_urls_screen ) {
        $footer_text = 'Enjoyed <strong>Export All URLs</strong>? Please leave us a <a href="https://wordpress.org/support/plugin/export-all-urls/reviews/?filter=5#new-post" target="_blank">&#9733;&#9733;&#9733;&#9733;&#9733;</a> rating. We really appreciate your support! ';
    }

    return $footer_text;
}