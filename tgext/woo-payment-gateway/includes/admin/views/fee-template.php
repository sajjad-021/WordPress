<tr class="wc-braintree-fee-tr" valign="top">
	<td class="">
		<fieldset>
			<div class="wc-braintree-fee-container">
				<p>
					<button class="button wc-braintree-add-fee"><?php _e('Add Fee', 'woo-payment-gateway')?></button>
				</p>
				<table id="wc-braintree-fee-table" class="wc-braintree-fee-table">
					<thead>
						<tr>
							<th><?php _e('Name', 'woo-payment-gateway')?></th>
							<th><?php _e('Calculation', 'woo-payment-gateway')?></th>
							<th><?php _e('Tax Status', 'woo-payment-gateway')?></th>
							<th><?php _e('Gateways', 'woo-payment-gateway')?></th>
							<th><?php _e('Actions', 'woo-payment-gateway')?></th>
						</tr>
					</thead>
					<tbody id="wc-braintree-fee-tbody">

					</tbody>
				</table>
			</div>
		</fieldset>
	</td>
</tr>
<script type="text/template" id="wc-braintree-fee-template">
	<td>
		<input type="text" name="<?php echo $field_key?>[<%=index%>][name]" value="<%=name%>"/>
	</td>
	<td>
		<input type="text" name="<?php echo $field_key?>[<%=index%>][calculation]" value="<%=calculation%>"/>
	</td>
	<td>
		<select name="<?php echo $field_key?>[<%=index%>][tax_status]">
			<option value="taxable" <%= tax_status === "taxable" ? 'selected' : '' %> ><?php _e('Taxable', 'woo-payment-gateway')?></option>
			<option  value="none" <%= tax_status === "none" ? 'selected' : '' %>><?php _e('None', 'woo-payment-gateway')?></option>
		</select>
	</td>
	<td>
		<select multiple class="wc-enhanced-select" name="<?php echo $field_key?>[<%=index%>][gateways][]">
			<?php foreach($gateways as $gateway):?>
				<option value="<?php echo $gateway->id?>"><?php echo $gateway->get_title()?></option>
			<?php endforeach;?>
		</select>
	</td>
	<td>
		<span class="dashicons dashicons-trash wc-braintree-delete-row">
	</td>
</script>


