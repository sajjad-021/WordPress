<div class="wslu-social-login-main-wrapper">
	<?php 
	require_once( WSLU_LOGIN_PLUGIN . '/template/admin/share/tab-menu.php');
	if($message_global == 'show'){?>
	<div class="admin-page-framework-admin-notice-animation-container">
		<div 0="XS_Social_Login_Settings" id="XS_Social_Login_Settings" class="updated admin-page-framework-settings-notice-message admin-page-framework-settings-notice-container notice is-dismissible" style="margin: 1em 0px; visibility: visible; opacity: 1;">
			<p><?php echo esc_html__('Global setting data have been updated.', 'wslu');?></p>
			<button type="button" class="notice-dismiss"><span class="screen-reader-text"><?php echo esc_html__('Dismiss this notice.', 'wslu');?></span></button>
		</div>
	</div>
	<?php }?>

	<form action="<?php echo esc_url(admin_url().'admin.php?page=wslu_share_setting');?>" name="global_setting_submit_form" method="post" id="xs_global_form">
		<div class="social-block-wraper">
			<div class="global-section">

				<div class="wslu-single-item">

					<div class="wslu-left-label">
						<label class="wslu-sec-title" for=""><?php echo esc_html__('Hide Icon', 'wslu');?></label>
					</div>

					<div class="wslu-right-content">

						<input class="social_switch_button" type="checkbox" id="enable_shoe_icon_enable" name="xs_share[global][show_icon][enable]" value="1" <?php echo (isset($return_data['global']['show_icon']['enable']) && $return_data['global']['show_icon']['enable'] == 1) ? 'checked' : ''; ?> >
						<label for="enable_shoe_icon_enable" class="social_switch_button_label"></label>
						
					</div>
				</div> <!-- ./ end item -->

				<div class="wslu-single-item">

					<div class="wslu-left-label">
						<label class="wslu-sec-title" for=""><?php echo esc_html__('Hide Text', 'wslu');?></label>
					</div>

					<div class="wslu-right-content">

						<input class="social_switch_button" type="checkbox" id="enable_shoe_text_enable" name="xs_share[global][show_text][enable]" value="1" <?php echo (isset($return_data['global']['show_text']['enable']) && $return_data['global']['show_text']['enable'] == 1) ? 'checked' : ''; ?> >
						<label for="enable_shoe_text_enable" class="social_switch_button_label"></label>
						
					</div>
				</div> <!-- ./ end item -->

				<div class="wslu-single-item">

					<div class="wslu-left-label">
						<label class="wslu-sec-title" for=""><?php echo esc_html__('Hide Label', 'wslu');?></label>
					</div>

					<div class="wslu-right-content">

						<input class="social_switch_button" type="checkbox" id="enable_shoe_label_enable" name="xs_share[global][show_label][enable]" value="1" <?php echo (isset($return_data['global']['show_label']['enable']) && $return_data['global']['show_label']['enable'] == 1) ? 'checked' : ''; ?> >
						<label for="enable_shoe_label_enable" class="social_switch_button_label"></label>
						
					</div>
				</div> <!-- ./ end item -->

				<div class="wslu-single-item">

					<div class="wslu-left-label">
						<label class="wslu-sec-title" for=""><?php echo esc_html__('Hide Share Count', 'wslu');?></label>
					</div>

					<div class="wslu-right-content">

						<input class="social_switch_button" type="checkbox" id="enable_shoe_counter_enable" name="xs_share[global][show_counter][enable]" value="1" <?php echo (isset($return_data['global']['show_counter']['enable']) && $return_data['global']['show_counter']['enable'] == 1) ? 'checked' : ''; ?> >
						<label for="enable_shoe_counter_enable" class="social_switch_button_label"></label>
						
					</div>
				</div> <!-- ./ end item -->

				<!-- Submit Button -->
				<div class="wslu-single-item">
					
					<div class="wslu-left-label">&nbsp;</div>

					<div class="wslu-right-content">
						<button type="submit" name="share_settings_submit_form_global" class="xs-btn btn-special small"><?php echo esc_html__('Save Changes');?></button>
					</div>
				</div> <!-- ./ End Single Item -->

				<!-- Shortcode section -->
				<div class="wslu-single-item">
					
					<div class="wslu-left-label"><label class="wslu-sec-title" for=""><?php echo esc_html__('Shortcode ', 'wslu');?></label></div>

					<div class="wslu-right-content">
						<ol class="wslu-social-shortcodes">
							<li>[xs_social_share] </li>
							<li>[xs_social_share provider="facebook,twitter,instagram" class="custom-class"] </li>
							<li>[xs_social_share provider="facebook" class="custom-class" style=""]</li>
						</ol>
					</div>
				</div> <!-- ./ End Single Item -->

			</div>
		</div>
	</form>
</div>