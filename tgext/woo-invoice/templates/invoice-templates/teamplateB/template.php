<?php
/*
* Template Name : Flat
* ----------------------------
* Author        : @PickPlugins
* Copyright     : 2015 @ PickPlugins.com
*/

if ( ! defined('ABSPATH')) exit;  // if direct access  

global $wooin;

$order_data = wooin_get_order( $order_id, 'object' );
$shop_image = $type == 'pdf' ? $wooin->shop_image_path : $wooin->shop_image_src;



//echo '<pre>'; print_r( $order_data ); echo '</pre>';

?>

    <div class="wooin-invoice">

        <!-- Section image and shop information -->

        <div class="wooin-invoice-section section-header">
            <div class="section-col " style="width: 25%;display: inline-block;vertical-align: top">
                <div class="wooin-shop-image"><span><img width="70%" src="<?php echo $shop_image; ?>" /></span></div>

            </div>

            <div class="section-col " style="width: 35%;display: inline-block;vertical-align: top">

                <div class="wooin-shop-info">
                    <div class="order-info wooin_shop_name"><?php echo $wooin->shop_name; ?></div>
                    <div class="order-info wooin_shop_address"><?php echo $wooin->shop_address; ?></div>
                </div>
            </div>


            <div class="section-col " style="width: 39%;display: inline-block;vertical-align: top">

                <div class="order-info">
                    <div class="item-label"><?php _e('Order ID', 'woo-invoice'); ?></div>
                    <div class="item-label-value">: <?php echo $order_data->id; ?></div>
                </div>

                <div class="order-info">
                    <div class="item-label"><?php _e('Order status', 'woo-invoice'); ?></div>
                    <div class="item-label-value">: <?php echo ucwords( $order_data->status ); ?></div>
                </div>
                <div class="order-info">
                    <div class="item-label"><?php _e('Order date', 'woo-invoice'); ?></div>
                    <div class="item-label-value">: <?php echo $order_data->order_date; ?></div>
                </div>

                <div class="order-info">
                    <div class="item-label"><?php _e('Payment method', 'woo-invoice'); ?></div>
                    <div class="item-label-value">: <?php echo $order_data->payment_method_title; ?></div>
                </div>
            </div>



        </div>

        
        <!-- Biiling and shipping information -->

        <div class="wooin-invoice-section section-address">

            <div class="section-col" style="width: 49%;display: inline-block;vertical-align: top">
                <div class="wooin-billing">

                    <div class="section-label"><?php _e( 'Invoice to', 'woo-invoice'); ?> </div>

                    <div class="item-info">
                        <?php echo $order_data->billing->company; ?>
                    </div>

                    <div class="item-info">
                        <?php echo $order_data->billing->first_name .' '. $order_data->billing->last_name; ?>
                    </div>

                    <div class="item-info">
                        <?php echo $order_data->billing->address_1; ?>
                    </div>

                    <div class="item-info">
                        <?php echo $order_data->billing->address_2; ?>
                    </div>

                    <div class="item-info">
                        <?php echo $order_data->billing->city; ?>
                    </div>

                    <div class="item-info">
                        <?php echo $order_data->billing->postcode; ?> - <?php echo $order_data->billing->country; ?>
                    </div>

                    <div class="item-info">
                        <?php echo $order_data->billing->phone; ?>
                    </div>


                </div>
            </div>

            <div class="section-col" style="width: 49%;display: inline-block;vertical-align: top">
                <div class="wooin-shipping">

                    <div class="section-label"><?php _e( 'Shipping to', 'woo-invoice'); ?> </div>


                    <div class="item-info">
                        <?php echo $order_data->shipping->company; ?>
                    </div>

                    <div class="item-info">
                        <?php echo $order_data->shipping->first_name .' '. $order_data->billing->last_name; ?>
                    </div>

                    <div class="item-info">
                        <?php echo $order_data->shipping->address_1; ?>
                    </div>

                    <div class="item-info">
                        <?php echo $order_data->shipping->address_2; ?>
                    </div>

                    <div class="item-info">
                        <?php echo $order_data->shipping->city; ?>
                    </div>

                    <div class="item-info">
                        <?php echo $order_data->shipping->postcode; ?> - <?php echo $order_data->billing->country; ?>
                    </div>





                </div>
            </div>



        </div>

        <br></br>
        <div class="wooin-invoice-section section-items">

            <div class="section-label">
                <?php _e('Ordered items', 'woo-invoice') ?>
            </div>
            
            <div class="wooin-items">

                <div class="wooin-item item-header">
                    <div class="item-inline item-description">
                        <?php _e('Item Description', 'woo-invoice') ?>
                    </div>
                    <div class="item-inline item-quantity">
                        <?php _e('Quantity', 'woo-invoice') ?>
                    </div>
                    <div class="item-inline item-price">
                        <?php _e('Price', 'woo-invoice') ?>
                    </div>
                    <div class="item-inline item-discount">
                        <?php _e('Discount', 'woo-invoice') ?>
                    </div>
                    <div class="item-inline item-subtotal">
                        <?php _e('Total', 'woo-invoice') ?>
                    </div>
                </div>

                <!-- ====== -->
                <!-- Display line items -->
                <!-- ====== -->

                <?php foreach( $order_data->items as $item ) : $item = (object)$item; ?>

                <div class="wooin-item">
                    <div class="item-inline item-description">
                        <div class="item-product-name">
                            <a href="<?php echo $item->permalink; ?>" target="_blank"><?php echo $item->name; ?> </a>
                        </div>
                    </div>
                    <div class="item-inline item-quantity">
                        <?php echo $item->quantity; ?>
                    </div>
                    <div class="item-inline item-price">
                        <?php echo $item->subtotal; ?>
                         <?php echo $wooin->currency_symbol; ?>
                    </div>
                    <div class="item-inline item-discount">
                        -
                        <?php echo $item->discount; ?>
                         <?php echo $wooin->currency_symbol; ?>
                    </div>
                    <div class="item-inline item-mobile">
                        <div class="wooin-help-top hint--top hint--medium" aria-label="<?php echo sprintf('Quantity: %2$s, Price: %3$s %1$s, Discount: - %4$s %1$s', $wooin->currency_symbol, $item->quantity, $item->subtotal, $item->discount ); ?>">?</div>
                    </div>
                    <div class="item-inline item-subtotal">
                        <?php echo $item->total; ?>
                         <?php echo $wooin->currency_symbol; ?>
                    </div>
                </div>

                <?php endforeach; ?>


                <!-- ====== -->
                <!-- Display subtotal -->
                <!-- ====== -->

                <div class="wooin-item item-footer-subtotal">
                    <div class="item-inline item-description"><?php _e('Subtotal', 'woo-invoice'); ?></div>
                    <div class="item-inline item-quantity">--</div>
                    <div class="item-inline item-price"><?php echo $order_data->item_total; ?> <?php echo $wooin->currency_symbol; ?></div>
                    <div class="item-inline item-discount">- <?php echo $order_data->order->get_discount_total(); ?> <?php echo $wooin->currency_symbol; ?></div>
                    <?php if( $type != 'pdf' ) : ?> <div class="item-inline item-mobile"></div> <?php endif; ?>
                    <div class="item-inline item-subtotal"><?php echo $order_data->order->get_subtotal() - $order_data->order->get_discount_total(); ?> <?php echo $wooin->currency_symbol; ?></div>
                </div>


                <!-- ====== -->
                <!-- Display shipping lines -->
                <!-- ====== -->

                <?php foreach( $order_data->shipping_lines as $line_item ) : ?>

                <div class="wooin-item item-footer-shipping">
                    <div class="item-inline item-description">
                        <?php echo $line_item->get_name(); ?> |
                        <?php _e('Tax : ', 'woo-invoice' ); echo $line_item->get_total_tax(); ?>
                         <?php echo $wooin->currency_symbol; ?>
                        <?php if( $type != 'pdf' ) : ?>
                            <div class="hint--top wooin-help-top" aria-label="<?php _e('This amount is not included to Subtotal') ?>"> ?</div>
                        <?php endif; ?>
                    </div>
                    <div class="item-inline item-quantity">--</div>
                    <div class="item-inline item-price">--</div>
                    <div class="item-inline item-discount">--</div>
                    <div class="item-inline item-mobile">--</div>
                    <div class="item-inline item-subtotal"><?php echo $line_item->get_total(); ?> <?php echo $wooin->currency_symbol; ?></div>
                </div>

                <?php endforeach; ?>


                <!-- ====== -->
                <!-- Display Fees -->
                <!-- ====== -->

                <?php foreach( $order_data->fee_lines as $line_item ) : ?>

                <div class="wooin-item item-footer-fees">
                    <div class="item-inline item-description"><?php echo $line_item->get_name(); ?></div>
                    <div class="item-inline item-quantity">--</div>
                    <div class="item-inline item-price">--</div>
                    <div class="item-inline item-discount">--</div>
                    <div class="item-inline item-mobile">--</div>
                    <div class="item-inline item-subtotal"><?php echo $line_item->get_total(); ?> <?php echo $wooin->currency_symbol; ?></div>
                </div>

                <?php endforeach; ?>



                <!-- ====== -->
                <!-- Display Taxes -->
                <!-- ====== -->

                <?php foreach( $order_data->tax_lines as $line_item ) : ?>

                <div class="wooin-item item-footer-tax">
                    <div class="item-inline item-description"><?php echo $line_item->get_label(); ?></div>
                    <div class="item-inline item-quantity">--</div>
                    <div class="item-inline item-price">--</div>
                    <div class="item-inline item-discount">--</div>
                    <div class="item-inline item-mobile">--</div>
                    <div class="item-inline item-subtotal"><?php echo $order_data->total_tax; ?> <?php echo $wooin->currency_symbol; ?></div>
                </div>

                <?php endforeach; ?>


                <div class="wooin-item item-footer-total">
                    <div class="item-inline item-description"><?php _e('Total amount', 'woo-invoice'); ?></div>
                    <div class="item-inline item-quantity">--</div>
                    <div class="item-inline item-price">--</div>
                    <div class="item-inline item-discount">--</div>
                    <div class="item-inline item-mobile">--</div>
                    <div class="item-inline item-subtotal"><?php echo $order_data->total; ?> <?php echo $wooin->currency_symbol; ?></div>
                </div>

            </div>

        </div>

        <div class="wooin-invoice-section section-footer">
            <span class="tagline"><?php echo $wooin->shop_tagline; ?></span> - <span class="shop-name"><?php echo $wooin->shop_name; ?></span>

        </div>

    </div>

    <!-- <script>
        var vueData = new Vue({
            el: '#woo-invoice-<?php // echo $unique_id; ?>',
            data: <?php // echo $wooin->get_order_data( $order_id, 'json' ) ?>,
            methods: {
                wooinCalculateItemTotal(item, index) {
                    var itemTotal = parseFloat(item.quantity) * parseFloat(item.subtotal);
                    return this.items[index].total = itemTotal;
                },
                wooinCalculateSubTotal() {
                    var subTotal = 0;
                    for (i in this.items) subTotal += this.items[i].total;
                    return subTotal;
                },
                wooinCalculateTotal() {
                    return parseFloat(this.shipping_total) + parseFloat(this.total_tax) + this.wooinCalculateSubTotal();
                }
            },
        });
    </script> -->