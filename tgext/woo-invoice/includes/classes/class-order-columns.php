<?php
/*
* @Author : PickPlugins
* @Copyright : 2015 PickPlugins.com
* @Version : 1.0.9
* @URL : https://github.com/jaedm97/Pick-Settings
*/

if ( ! defined('ABSPATH')) exit;  // if direct access 

class WOOIN_Order_columns {
		
    public function __construct(){
		
        add_action( 'manage_shop_order_posts_columns', array( $this, 'add_columns' ), 16, 1 );
        add_action( 'manage_shop_order_posts_custom_column', array( $this, 'columns_content' ), 10, 2 );

        add_action( 'admin_footer', array( $this, 'preview_modal' ), 10, 2 );
	}
    
    public function add_columns($columns){

        $new = array();
		
		$count = 0;
		foreach ( $columns as $col_id => $col_label ) { $count++;

			if ( $count == 3 ) $new['wooin-invoice'] = '';
			$new[ $col_id ] = $col_label;
		}

		return $new;
    }

    public function columns_content( $column, $post_id ){

        if( 'wooin-invoice' === $column ) {

            global $wooin;

            printf( '<div order_id="%s" class="wooin_invoice_preview wooin-btn hint--top" aria-label="%s"><i class="wooin_animate icofont icofont-file-document"></i></div>', $post_id, __('Preview invoice') );

            printf( '<div target_url="%s"  class="wooin_invoice_download wooin-btn hint--top" aria-label="%s"><i class="icofont icofont-download-alt"></i></div>', $wooin->get_invoice_ajax_url( array( 'order_id' => $post_id ) ), __('Download invoice') );
        }
    }

    public function preview_modal(){

        echo "<div class='wooin_modal_container'>";
            echo "<div class='wooin_modal'>";
                echo "<div class='modal-close'><i class='wooin_modal_close wooin-btn wooin-btn-danger icofont icofont-close'></i></div>";
                echo "<div class='wooin_modal_content'></div>";
            echo "</div>";
        echo "</div>";
        echo "<div class='wooin-invoice-temp wooin-no-display'></div>";
    }
	
}
new WOOIN_Order_columns();