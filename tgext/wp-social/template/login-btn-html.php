<?php
$buttonStyle = isset($style_data['login_button_style']) ? $style_data['login_button_style'] : 'style1';

$className = 'xs-login-global xs-login-'.$buttonStyle.'';
?>
<div id="<?php echo esc_attr( 'XS_social_login_div' );?>" class="<?php echo  esc_attr( $className );?> ">
	<ul class="<?php echo  esc_attr( '_login_button_style__ul' ); ?>">
	<?php
		if(!is_user_logged_in()){
			if(strlen(xs_current_url_custom()) > 2){
				if($typeCurrent == 'show'){
					$correntUrl 	= '?XScurrentPage='.xs_current_url_custom().'';
				}
			}else{
				$correntUrl 	  	= '';
			}
			foreach(xs_services_provider() AS $keyType=>$valueType):
				if(isset($provider_data[$keyType]['enable']) ? $provider_data[$keyType]['enable'] : 0):
					if(in_array($keyType, $attr) OR in_array('all', $attr)){
						$btn_text = isset($provider_data[$keyType]['login_label']) ? $provider_data[$keyType]['login_label'] : 'Login with <b>'.$valueType.'</b>';
						
						?>
						<li class="<?php echo  esc_attr('xs-li-'.$buttonStyle.' '.$keyType); ?>">
							<a href="<?php echo esc_url(get_site_url().'/wp-json/wslu-social-login/type/'.$keyType.''.$correntUrl); ?>">
								<div class="xs-social-icon">
									<?php if(in_array($buttonStyle, array('style1', 'style2', 'style4', 'style5'))){
									$lineIconSet = '';
									if(in_array($buttonStyle, array('style4', 'style5', 'style6')) ){
										$lineIconSet = '-line';
									}
									?>
									<div class="social-icon">
										<span class="met-social met-social-<?php echo $keyType;?>"></span>
									</div>
									<?php }
									 if( in_array($buttonStyle, array('style1', 'style3', 'style4', 'style6'))){ ?>
										<span class="login-button-text"> <?php echo esc_html($btn_text); ?> </span>
									<?php }?>
								</div>
							</a>
						</li>

						<?php
					}
				endif;
			endforeach;
		}else{
			$correntUrlLogout = esc_url(xs_current_url_custom());
		?>
			<li> <a href="<?php echo wp_logout_url( $correntUrlLogout ); ?>"><?php echo __('Logout'); ?></a> </li>
	<?php }?>
	</ul>
</div>