<?php
/*
* @Author 		pickplugins
* Copyright: 	pickplugins.com
*/

if ( ! defined('ABSPATH')) exit;  // if direct access 


function wooin_add_attachment_email( $attachments, $email_type, $order ){

    update_option( 'email_type', $email_type );


    global $wooin;

    // Check settings for - wooin_invoice_attachment
    // -------------------------------------------------- //
    $invoice_attachment  = get_option( 'wooin_invoice_attachment' );
    $invoice_attachment  = empty( $invoice_attachment ) ? 'yes' : $invoice_attachment;
    
    if( $invoice_attachment != 'yes' ) return $attachments;


    // Check settings for - wooin_attachment_on_type
    // -------------------------------------------------- //
    $attachment_on_type  = get_option( 'wooin_attachment_on_type' );
    $attachment_on_type  = empty( $attachment_on_type ) ? array() : $attachment_on_type;

    if( ! in_array( $email_type, $attachment_on_type ) && ! empty( $attachment_on_type ) ) 
    return $attachments;


    $attathment_file_url = $wooin->generate_pdf( $order->get_id(), "", false, true );
    
    if( ! is_wp_error( $attathment_file_url ) ) $attachments[] = $attathment_file_url;

    return $attachments;
}
add_filter( 'woocommerce_email_attachments', 'wooin_add_attachment_email', 10, 3 );



function my_account_pdf_download_buttons( $actions, $order ){

    global $wooin;

    $actions['wooin-invoice'] = array(
        'url'   => $wooin->get_invoice_ajax_url( array( 'order_id' => $order->get_id() ) ),
        'name'  => __( 'Download Invoice', 'woo-invoice' ),
    );
    
    return apply_filters( 'wooin_filter_myaccount_actions', $actions, $order );
}
add_filter( 'woocommerce_my_account_my_orders_actions', 'my_account_pdf_download_buttons', 10, 2 );




function generate_wooin_invoice(){
	
    if( empty( $_GET['action'] ) || ! check_admin_referer( $_GET['action'] ) ) {
        wp_die( __( 'You do not have sufficient permissions to access this page.', 'woo-invoice' ) );
    }

    $order_id       = isset( $_GET[ 'order_id' ] ) ? sanitize_text_field( $_GET[ 'order_id' ] ) : ''; 
    $document_type  = isset( $_GET[ 'document_type' ] ) ? sanitize_text_field( $_GET[ 'document_type' ] ) : ''; 


    global $wooin;
    
    header("Content-Type: application/pdf; charset=UTF-8");

    echo $wooin->generate_pdf( $order_id );
	exit;
}
add_action('wp_ajax_generate_wooin_invoice', 'generate_wooin_invoice');



function wooin_admin_ajax_load_invoice(){
	
    $order_id = isset( $_POST[ 'order_id' ] ) ? sanitize_text_field( $_POST[ 'order_id' ] ) : ''; 

    if( empty( $order_id ) ) die();

    global $wooin;

	echo json_encode( array(
        'status'        => true,
        'html'          => sprintf( "%s", $wooin->get_order_invoice_html( $order_id ) ),
        'invoice_url'   => WOOIN_PLUGIN_URL . 'invoices/1525503793.pdf',
    ) );
    
	die();
}
add_action('wp_ajax_wooin_admin_ajax_load_invoice', 'wooin_admin_ajax_load_invoice');



function wooin_ajax_load_product_data(){
	
    $order_id = isset( $_POST[ 'order_id' ] ) ? sanitize_text_field( $_POST[ 'order_id' ] ) : ''; 

    if( empty( $order_id ) ) die();


	echo json_encode( array(
		'items' => array(
            array(
                'thumbnail_url' => get_the_post_thumbnail_url( 22, array( 50, 50 ) ),
                'name'          => get_the_title( 22 ),
                'product_id'    => '#22',
                'quantity'      => 1,
                'price'         => 120,
                'item_total'    => 120,
                
            ),
        ),
        'shipping_name'         => 'Jaed Mosharraf',
        'billing_name'          => 'Jaed Mosharraf 2',
        'wooin_shipping'        => 25,
        'wooin_sales_tax'       => 14,
	) );
	die();
}
add_action('wp_ajax_wooin_ajax_load_product_data', 'wooin_ajax_load_product_data');



if( ! function_exists( 'wooin_get_order' ) ){
    function wooin_get_order( $order_id = 0, $return_as = false ){

        if( $order_id == 0 ) 
        return new WP_Error( 'invalid_data', __('Invalid order id provided', 'woo-invoice') );

        global $wooin;
        return $wooin->get_order_data( $order_id, $return_as );
    }
} 