<div class="wslu-social-login-main-wrapper">
	<?php 
	require_once( WSLU_LOGIN_PLUGIN . '/template/admin/share/tab-menu.php');
	if($message_provider == 'show'){?>
	<div class="admin-page-framework-admin-notice-animation-container">
		<div 0="XS_Social_Login_Settings" id="XS_Social_Login_Settings" class="updated admin-page-framework-settings-notice-message admin-page-framework-settings-notice-container notice is-dismissible" style="margin: 1em 0px; visibility: visible; opacity: 1;">
			<p><?php echo esc_html__('Providers data have been updated.', 'wslu');?></p>
			<button type="button" class="notice-dismiss"><span class="screen-reader-text"><?php echo esc_html__('Dismiss this notice.', 'wslu');?></span></button>
		</div>
	</div>
	<?php }?>


	<form action="<?php echo esc_url(admin_url().'admin.php?page=wslu_share_setting&tab=wslu_providers');?>" name="xs_provider_submit_form" method="post" id="xs_provider_form">
		<div class="xs-social-block-wraper">
			<ul class="xs-social-block">
			<?php
				foreach( $share_provider AS $k=>$v):
				$name = isset($v['label']) ? $v['label'] : '';
				
				$setLabel = (isset($share_provider[$k]['data']['label']) && strlen($share_provider[$k]['data']['label']) > 2) ? $share_provider[$k]['data']['label'] : $name;
				
			?>
			<li>
				<div class="xs-single-social-block <?php echo $k;?>">
					<div class="xs-block-header" data-type="modal-trigger" data-target="example-modal-<?php echo $k;?>">
						<span class="drag-icon"></span>
						<div class="xs-social-icon">
							<span class="met-social met-social-<?php echo $k;?>"></span>
						</div>
						<h2 class="xs-social-icon-title"><?php echo esc_html($setLabel, 'wslu');?></h2>
					</div>
					<div class="xs-block-footer">
						<div class="left-content">
							<div class="configure">

								<span class="wslu-social-cond-text <?php echo isset($share_provider[$k]['enable']) ? 'enable' : 'disable';?>"><?php echo isset($share_provider[$k]['enable']) ? '<span class="wslu-icon met-social met-social-tick"></span>'.esc_html__('Enabled', 'wslu') : '<span class="wslu-icon met-social met-social-cancel"></span>'.esc_html__('Disabled', 'wslu');?></span>

							</div>
						</div>
						<div class="right-content">
							<a href="javascript:void()" class="wslu-social-provider-btn xs-btn btn-special small" data-type="modal-trigger" data-target="example-modal-<?php echo $k;?>"> <?php if( isset($share_provider[$k]['enable']) ? $share_provider[$k]['enable'] : 0 == 1){ echo esc_html__('Settings', 'wslu');?> <?php }else{?> <?php echo esc_html__('Getting Started', 'wslu'); }?></a>
						</div>
					</div>
				</div>
			</li>
				<?php 
				endforeach;?>
		</ul>
	</div>
		<?php
			foreach( $share_provider AS $kk=>$vv ):
				$classSet = 'setting';
				$name = isset($vv['label']) ? $vv['label'] : '';
				
				$setLabel = (isset($share_provider[$kk]['data']['label']) && strlen($share_provider[$kk]['data']['label']) > 2) ? $share_provider[$kk]['data']['label'] : $name;
				
				$defaultText = isset($vv['data']['text']) ? $vv['data']['text'] : 'Share';
				$belowText = (isset($share_provider[$kk]['data']['text']) && strlen($share_provider[$kk]['data']['text']) > 2) ? $share_provider[$kk]['data']['text'] : $defaultText;
				
				$belowValue = (isset($share_provider[$kk]['data']['value']) && $share_provider[$kk]['data']['value'] > 0) ? $share_provider[$kk]['data']['value'] : 0;
				if(strlen($kk) > 2){
		?>

		<div class="xs-modal-dialog" id="example-modal-<?php echo $kk;?>">
			<div class="xs-modal-content post__tab">
				<div class="xs-modal-header clear-both">
					<div class="tabHeader">
						<ul class="tab__list clear-both">
							<li class="<?php if($classSet == 'setting'){ echo 'active';}?> tab__list__item"><?php echo esc_html__('Settings', 'wslu');?></li>
						</ul>
						<button type="button" class="xs-btn" data-modal-dismiss="modal"><span class="wslu-icon met-social met-social-cross"></span></button>
					</div>
					
				</div>
				<div class="xs-modal-body">
					<div class="ekit--tab__post__details tabContent">
						<h6 class="wslu-popup-provider-title"><?php echo esc_html__( $setLabel , 'wslu');?></h6>
						
						<div class="tabItem <?php if($classSet == 'setting'){ echo 'active';}?>">
							<div class="setting-section">

								<div class="wslu-popup-data">

									<div class="wslu-single-popup-item wslu-inline">
										<div class="setting-label-wraper">
											<label class="setting-label wslu-sec-title" for="<?php echo $kk;?>_enable"><?php echo __('Enable', 'wslu');?> </label>
										</div>

										<input class="social_switch_button" type="checkbox" id="<?php echo $kk;?>_enable" name="xs_share[social][<?php echo $kk;?>][enable]" value="1" <?php if( isset($share_provider[$kk]['enable']) ? $share_provider[$kk]['enable'] : 0 == 1){ echo 'checked';}?> >
										<label for="<?php echo $kk;?>_enable" class="social_switch_button_label"></label>
									</div>

									<div class="wslu-single-popup-item">
										<div class="setting-label-wraper">
											<label class="setting-label wslu-sec-title" for="<?php echo $kk;?>_value"><?php echo __( 'Default '.$setLabel.' Share Count' , 'wslu');?> </label>
										</div>

										<input name="xs_share[social][<?php echo $kk;?>][data][value]" type="text" id="xs_<?php echo $kk;?>_value" value="<?php echo esc_html($belowValue);?>" class="wslu-global-input">
									</div>

									<div class="wslu-single-popup-item">
										<div class="setting-label-wraper">
											<label class="setting-label wslu-sec-title" for="<?php echo $kk;?>_text"><?php echo __( 'Text below the number' , 'wslu');?> </label>
										</div>

										<input name="xs_share[social][<?php echo $kk;?>][data][text]" type="text" id="xs_<?php echo $kk;?>_text" value="<?php echo esc_html($belowText);?>" class="wslu-global-input">
									</div>

									<div class="wslu-single-popup-item">
										<div class="setting-label-wraper">
											<label class="setting-label wslu-sec-title" for="<?php echo $kk;?>_label"><?php echo __( 'Label Name' , 'wslu');?> </label>
										</div>

										<input name="xs_share[social][<?php echo $kk;?>][data][label]" type="text" id="xs_<?php echo $kk;?>_label" value="<?php echo esc_html($setLabel);?>" class="wslu-global-input">
									</div>

								</div>  <!-- ./ End Popup Data -->
								
							</div>
						</div>
						
					</div>
				</div>
				<div class="xs-modal-footer">
					<button type="submit" name="share_settings_submit_form" class="xs-btn btn-special"><?php echo esc_html__('Save Changes');?></button>
				</div>
			</div>
		</div>
		<?php }
		endforeach;?>
		<div class="xs-backdrop"></div>
	</form>
</div>