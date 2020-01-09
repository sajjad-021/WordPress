<?php
/*
* @Author 		pickplugins
* Copyright: 	2015 pickplugins
*/

if ( ! defined('ABSPATH')) exit;  // if direct access 

global $wooin;

$order_id   = isset( $atts['order_id'] ) ? $atts['order_id'] : '';
$type       = isset( $atts['type'] ) ? $atts['type'] : '';
$template   = isset( $atts['template'] ) ? $atts['template'] : $wooin->get_option( 'wooin_invoice_template', 'flat' );


if( empty( $order_id ) || $order_id == 0 ) {

    $wooin->print_error( new WP_Error( 'invalid_data', __('Order ID missing!', 'woo-invoice' ) ) );
    return;
}

$all_templates  = $wooin->get_templates();

$template_dir   = isset( $all_templates[$template]['template'] ) ? $all_templates[$template]['template'] : '';
$template_dir   = apply_filters( 'wooin_filter_invoice_template_dir', $template_dir, $order_id );
$template_dir   = file_exists( $template_dir ) ? $template_dir : '';

$stylesheet_dir = isset( $all_templates[$template]['stylesheet'] ) ? $all_templates[$template]['stylesheet'] : '';
$stylesheet_dir = apply_filters( 'wooin_filter_invoice_stylesheet_dir', $stylesheet_dir, $order_id );


if( empty( $template_dir ) || empty( $stylesheet_dir ) ) {

    $wooin->print_error( new WP_Error( 'invalid_data', sprintf( __( 'Template file missing : <b>%s</b>', 'woo-invoice' ), ucwords( $template ) ) ) );
    return;
}

printf( '<link rel="stylesheet" href="%s">', $stylesheet_dir );
include( $template_dir );
