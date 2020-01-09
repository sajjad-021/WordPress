<?php
/*
* @Author 		pickplugins
* Copyright: 	2015 pickplugins
*/

use Dompdf\Dompdf;

if ( ! defined('ABSPATH')) exit;  // if direct access 

class WOOIN_Functions{
      
    public $shop_name       = null;
    public $shop_tagline    = null;
    public $shop_address    = null;
    public $shop_image_src  = null;
    public $currency_symbol = null;

    public function __construct(){

        add_action( 'init', array( $this, 'init' ) );
    }

    public function init(){

        $wooin_shop_image       = get_option( 'wooin_shop_image' );
        $this->shop_name        = get_option( 'wooin_shop_name' );
        $this->shop_tagline     = get_option( 'wooin_shop_tagline' );
        $this->shop_address     = get_option( 'wooin_shop_address' );
        $this->shop_image_src   = wp_get_attachment_url( $wooin_shop_image );
        $this->shop_image_path  = get_attached_file( $wooin_shop_image );
        $this->currency_symbol  = get_woocommerce_currency_symbol();
    }


    public function get_email_types(){

        return apply_filters( 'wooin_filters_woo_email_types', array(
            'new_order'                 => __( 'New Order', 'woo-invoice' ),
            'cancelled_order'           => __( 'Cancelled order', 'woo-invoice' ),
            'failed_order'              => __( 'Failed order', 'woo-invoice' ),
            'customer_on_hold_order'    => __( 'Order on-hold', 'woo-invoice' ),
            'customer_processing_order' => __( 'Processing order', 'woo-invoice' ),
            'customer_completed_order'  => __( 'Completed order', 'woo-invoice' ),
            'customer_refunded_order'   => __( 'Refunded order', 'woo-invoice' ),
            'customer_invoice'          => __( 'Customer invoice / Order details', 'woo-invoice' ),
            'customer_note'             => __( 'Customer note', 'woo-invoice' ),
        ) );
    }

    public function get_templates(){

        $templates      = array();
        $arr_templates  = apply_filters( 'wooin_invoice_templates_internal', array(



            'flat'          => __('Flat', 'woo-invoice'),
            'teamplateA'          => __('Teamplate A', 'woo-invoice'),
            'teamplateB'          => __('Teamplate B', 'woo-invoice'),
            //'header-styled'          => __('Header styled', 'woo-invoice'),
            )
        );

        foreach( $arr_templates as $template_name => $label ){

            $templates[ $template_name ] = array(
                'label'      => $label,
                'thumb'      => sprintf( '%1$stemplates/invoice-templates/%2$s/%2$s.png', WOOIN_PLUGIN_URL, $template_name ),
                'template'   => sprintf( '%1$stemplates/invoice-templates/%2$s/template.php', WOOIN_PLUGIN_DIR, $template_name ),
                'stylesheet' => sprintf( '%1$stemplates/invoice-templates/%2$s/style.css', WOOIN_PLUGIN_URL, $template_name ),
            );
        }

        return apply_filters( 'wooin_invoice_templates', $templates );
    }


    public function generate_pdf( $order_id = 0, $html = "", $download_pdf = true, $save_pdf = false ){

        if( $order_id == 0 ) return new WP_Error( 'invalid_data', __('Invalid order id provided', 'woo-invoice') );

        require WOOIN_PLUGIN_DIR . 'dompdf/autoload.inc.php';

        $html   = empty( $html ) ? $this->get_order_invoice_html( $order_id, 'pdf' ) : $html;
        $dompdf = new Dompdf( array(
            'isRemoteEnabled' => true,
        ) );

        $dompdf->setPaper( 'A4', 'portrait' );
        $dompdf->set_option('isHtml5ParserEnabled', true);
        $dompdf->loadHtml( $html, 'UTF-8' );
        $dompdf->render();

        $output = $dompdf->output(); 

        if( $save_pdf ) {

            $invoice_file_name = sprintf( '%s/uploads/invoice-%s-%s.pdf', WP_CONTENT_DIR, $order_id, time("H:s") );
            file_put_contents( $invoice_file_name, $output);
            return $invoice_file_name;
        }

        if( $download_pdf ) {
            
            $dompdf->stream( sprintf( "%s-%s", __('Invoice', 'woo-invoice'), $order_id ) );
            return true;
        }

        return $output;
    }


    public function get_order_invoice_html( $order_id = 0, $type = "" ){

        if( $order_id == 0 ) return new WP_Error( 'invalid_data', __('Invalid order id provided', 'woo-invoice') );

        $shortcode = sprintf( '[order-invoice order_id="%s" type="%s" template="%s"]', 
            $order_id, $type, $this->get_option( 'wooin_invoice_template', 'flat' )
        );

        ob_start();
        echo do_shortcode( $shortcode );
        return ob_get_clean();
    }



    public function get_invoice_ajax_url( $args = array() ){
        
        $default_args = array(
            'action'            => 'generate_wooin_invoice',
            'doccument_type'    => 'pdf',
            'order_id'          => '',
        );
        
        $args       = wp_parse_args( $args, $default_args );
        $build_url  = http_build_query( $args );
        $nonce_url  = wp_nonce_url( admin_url( "admin-ajax.php?" . $build_url ), $args['action'] );
        
        return apply_filters( 'wooin_filters_invoice_ajax_url', $nonce_url );
    }



    public function get_order_data( $order_id = 0, $return_as = false ){

        if( $order_id == 0 ) return new WP_Error( 'invalid_data', __('Invalid order id provided', 'woo-invoice') );

        $data   = array();
        $order  = wc_get_order( $order_id );
        $data   = $order->get_data();

        $item_total = 0;
        foreach( $order->get_items() as $item_id => $item ) {

            $total      = isset( $item['total'] ) ? (float) $item['total'] : 0;
            $subtotal   = isset( $item['subtotal'] ) ? (float) $item['subtotal'] : 0;

            $data['items'][] = array_merge( $item->get_data(), array(
                'thumbnail_url' => get_the_post_thumbnail_url( $item->get_product_id(), array( 50, 50 ) ),
                'permalink'     => get_the_permalink( $item->get_product_id() ),
                'discount'      => $subtotal > $total ?  $subtotal - $total : 0,    
            ) );

            // echo '<pre>'; print_r( $itemsubtotal ); echo '</pre>';

            $item_total += $subtotal;
        }

        $data['order_date']     = $order->get_date_created()->date( 'M j, Y' );
        $data['item_total']     = $item_total;
        $data['order']          = $order;

        if( $return_as && $return_as == 'object' ) {
            
            $data['billing']        = isset( $data['billing'] )         ? (object)$data['billing']          : (object)array();
            $data['shipping']       = isset( $data['shipping'] )        ? (object)$data['shipping']         : (object)array();
            
            return (object)$data;
        }
        if( $return_as && $return_as == 'json' ) return json_encode( $data );

        return $data;
    }



    public function print_error( $wp_error ){
        
        $classes = array( $wp_error->get_error_code() );

        if( is_admin() ) $classes[] = 'is-dismissible';

        printf( "<div class='notice notice-error error wooin-notice %s'><p>%s</p></div>", 
            implode( ' ', $classes ), $wp_error->get_error_message() 
        );   
    }


    function get_option( $option_name = '', $default = '' ){

        $option_value = get_option( $option_name, $default );
        $option_value = empty( $option_value ) ? $default : $option_value;

        return $option_value;
    }
}

global $wooin;
$wooin = new WOOIN_Functions();

