<?php 
/**
 * @version 3.0.0
 * @package Braintree/Templates
 */
do_action('wcs_braintree_recurring_cart_totals_before');
?>
<tr>
	<th class="wc-braintree-recurring-title" colspan="2"><?php _e('Recurring Totals', 'woo-payment-gateway' )?></th>
	<td class="wc-braintree-recurring-title" colspan="2" data-title="<?php _e('Recurring Totals', 'woo-payment-gateway' )?>"></td>
</tr>
<?php foreach($recurring_carts as $key => $cart){?>
<tr>
	<th><?php _e('Subtotal', 'woo-payment-gateway' )?></th>
	<td data-title="<?php _e('Subtotal', 'woo-payment-gateway' )?>"><?php echo wcs_braintree_cart_subtotal_string($cart)?></td>
</tr>
<tr>
	<?php if($cart->shipping_total > 0 && $cart->needs_shipping() && $cart->show_shipping()){?>
		<th><?php _e('Shipping', 'woo-payment-gateway' )?></th>
		<td data-title="<?php _e('Shipping', 'woo-payment-gateway' )?>">
			<?php echo wcs_braintree_cart_shipping_total($cart)?>
			<?php //woocommerce_shipping_calculator()?>
		</td>
	<?php }?>
</tr>
<tr>
	<th><?php _e('Recurring Total', 'woo-payment-gateway' )?></th>
	<td data-title="<?php _e('Recurring Total', 'woo-payment-gateway' )?>">
		<?php echo wcs_braintree_cart_recurring_total_html($cart)?>
		<div class=wcs-braintree-renewal-date>
			<?php printf(__('First renewal date: %s', 'woo-payment-gateway' ), wcs_braintree_cart_formatted_date($cart->next_payment_date, wc_timezone_string()))?>
		</div>
	</td>
</tr>
<?php }?>
<?php do_action('wcs_braintree_recurring_cart_totals_after');?>