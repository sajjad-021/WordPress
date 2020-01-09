<?php 
/**
 * @version 3.0.0
 * @package Braintree/Templates
 */
?>
<tr>
	<th colspan="2">
		<?php _e('Recurring Totals', 'woo-payment-gateway' )?>
	</th>
</tr>
<?php foreach($recurring_carts as $cart_key => $cart){?>
	<?php foreach($cart->get_cart_contents() as $cart_item_key => $cart_item):?>
	<?php $_product = $cart_item['data']?>
	<tr class="">
		<td class="product-name">
			<?php echo apply_filters( 'wcs_braintree_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) . '&nbsp;'; ?>
			<?php echo apply_filters( 'woocommerce_checkout_cart_item_quantity', ' <strong class="product-quantity">' . sprintf( '&times; %s', $cart_item['quantity'] ) . '</strong>', $cart_item, $cart_item_key ); ?>
		</td>
		<td class="product-total">
			<?php echo WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] )?>
		</td>
	</tr>
	<?php endforeach;?>
	<tr>
		<th><?php _e('Subtotal', 'woo-payment-gateway' )?></th>
		<td><?php echo wcs_braintree_cart_subtotal_string($cart)?></td>	
	</tr>
		<?php if($cart->shipping_total > 0){?>
		<tr>
			<th><?php _e('Shipping', 'woo-payment-gateway' )?></th>
			<td><?php echo wcs_braintree_cart_shipping_total($cart)?></td>
		</tr>
		<?php }?>
		<?php if(wc_tax_enabled()){?>
			<?php foreach ( $cart->get_tax_totals() as $code => $tax ) {?>
				<tr>
					<th><?php echo esc_html( $tax->label )?></th>
					<td><?php echo wcs_braintree_cart_tax_total_html($tax, $cart)?></td>
				</tr>
			<?php }?>
		<?php }?>
	<tr>
		<th><?php _e('Total', 'woo-payment-gateway' )?></th>
		<td>
			<?php echo wcs_braintree_cart_recurring_total_html($cart)?>
			<span class="wcs-braintree-renewal-date"><?php printf(__('First renewal: %s', 'woo-payment-gateway' ), wcs_braintree_cart_formatted_date($cart->next_payment_date, wc_timezone_string()))?></span>
		</td>
	</tr>
<?php } ?>