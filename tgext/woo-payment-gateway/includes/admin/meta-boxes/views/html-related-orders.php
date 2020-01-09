<div class="wcs_braintree_related_orders">
	<?php if(empty($subscriptions)):?>
		<?php _e('There are no orders associated with this subscription.', 'woo-payment-gateway')?>
	<?php else:?>
	<table class="woocommerce_order_items">
		<thead>
			<tr>
				<th><?php _e('Subscription Number', 'woo-payment-gateway' )?></th>
				<th><?php _e('Start Date', 'woo-payment-gateway' )?></th>
				<th><?php _e('Status', 'woo-payment-gateway' )?></th>
				<th><?php _e('Total', 'woo-payment-gateway' )?></th>
			</tr>
		</thead>
		<tbody>
		<?php foreach($subscriptions as $subscription):?>
			<tr>
				<td><a
					href="<?php echo get_edit_post_link($subscription->get_id())?>">#<?php echo $subscription->get_order_number()?></a></td>
				<td><?php echo $subscription->get_formatted_date('start')?></td>
				<td><?php echo wc_get_order_status_name($subscription->get_status())?></td>
				<td><?php echo $subscription->get_formatted_total()?></td>
			</tr>
			<?php endforeach;?>
		</tbody>
	</table>
	<?php endif;?>
</div>