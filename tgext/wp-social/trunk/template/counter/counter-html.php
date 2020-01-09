<?php if(is_array($counter_provider) && sizeof($counter_provider) > 0):?>
<div class="xs_social_counter_widget <?php echo esc_attr($className);?>">
	<ul class="xs_counter_url <?php echo esc_attr($columns);?> <?php echo $widget_style;?>">
		<?php
		/*
			wslu-counter-box-shaped
			wslu-counter-fill-colored
			wslu-counter-fill-colored-hover
			wslu-counter-thin-border
			wslu-counter-icon-color
			wslu-counter-icon-fill-color

			wslu-counter-line-shaped
			wslu-counter-rounded
		*/
		
		foreach( $counter_provider AS $k=>$v):
			if( isset($v['enable']) && in_array($k, $provider) ){
				$label = isset($v['label']) ? $v['label'] : '';
				$default = isset($v['data']['value']) ? $v['data']['value'] : 0;
				$text = isset($v['data']['text']) ? $v['data']['text'] : '';
				$counter = isset($counter_data[$k]) ? $counter_data[$k] : 0;
				$counter = ($default) > 0 ? $default : $counter;
				
				$id = isset($v['id']) ? $v['id'] : '';
				$type = isset($v['type']) ? $v['type'] : '';
				$url_get = isset($core_provider[$k]['data']['url']) ? $core_provider[$k]['data']['url'] : '#';
				if($k == 'youtube'){
					$url = sprintf($url_get, strtolower($type), $id);
				}else if($k == 'linkedin'){
					if($type == 'Profile'){
						$url = sprintf($url_get, 'in', $id);
					}else{	
						$url = sprintf($url_get, 'company', $id);
					}
				}else{
					$url = sprintf($url_get, $id);
				}
				?>
					<li title="<?php echo $label;?>" class="xs-counter-li <?php echo  esc_attr('counter-'.$widget_style.' '.$k); ?>" >
						<a href="<?php echo esc_url($url);?>" target="_blank">
							<?php if(!isset($global_data['show_icon']['enable'])):?>
							<div class="xs-social-icon">
								<span class="met-social met-social-<?php echo $k;?>"></span>
							</div>
							<?php endif;
							if(!isset($global_data['show_counter']['enable'])):?>
							<div class="xs-social-follower">
								<?php echo xs_format_num($counter);?>
							</div>
							<?php endif;
							if(!isset($global_data['show_label']['enable'])):?>
							<div class="xs-social-follower-text">
								<?php echo $text;?>
							</div>
							<?php endif;?>
						</a>
					</li>
				<?php
			}
		endforeach;
		?>
	</ul>
</div>
<?php endif;?>