<?php
$tabs = apply_filters ( 'wc_braintree_local_gateways_tab', array() );
?>
<div class="wc-braintree-advanced-settings-nav local-gateways">
	<?php foreach($tabs as $id => $tab):?>
		<a
		class="nav-link <?php if($wc_braintree_subsection === $id){echo 'nav-link-active';}?>"
		href="<?php echo admin_url( 'admin.php?page=wc-settings&tab=checkout&section=braintree_local_gateways&sub_section=' . $id)?>"><?php echo esc_attr($tab)?></a>
	<?php endforeach;?>
</div>
<div class="clear"></div>