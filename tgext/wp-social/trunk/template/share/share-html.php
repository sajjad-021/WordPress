<?php if(is_array($counter_provider) && sizeof($counter_provider) > 0):?>
<div class="xs_social_share_widget <?php echo esc_attr($className);?>">
	<ul class="xs_share_url <?php echo  esc_attr($widget_style); ?> <?php echo (!isset($global_data['show_counter']['enable'])) ? 'wslu-social-share' : ''; ?> <?php echo !isset($global_data['show_label']['enable']) && !isset($global_data['show_label']['enable']) ?  'wslu-both-text-label' : ''; ?>">
		<?php

		/*
			wslu-share-box-shaped
			wslu-share-rounded
			wslu-share-m-5
			wslu-share-horizontal

			// wslu-ellipes-shaped
			wslu-fill-colored
			wslu-fill-brand-hover-colored
			wslu-icon-border-colored
			wslu-icon-border-colored-hover
			wslu-icon-border-fill-colored-hover

			wslu-share-shadow
			wslu-share-shadow-inset

			wslu-fill-colored-normal
			wslu-fill-colored-con
			wslu-fill-colored-icon
			
			wslu-icon-colored
			wslu-radius-3
		*/
	
		foreach( $counter_provider AS $k=>$v):
			if( isset($v['enable']) && in_array($k, $provider) ){
				$label = isset($v['data']['label']) ? $v['data']['label'] : '';
				$default = isset($v['data']['value']) ? (int) $v['data']['value'] : 0;
				$text = isset($v['data']['text']) ? $v['data']['text'] : '';
				
				$counter = isset($post_meta[0][$k]) ? $post_meta[0][$k] : 1;
				
				$counter = ($default) > $counter ? $default : $counter;
				
				$id = isset($v['id']) ? $v['id'] : '';
				$type = isset($v['type']) ? $v['type'] : '';
				$url_get = isset($core_provider[$k]['url']) ? $core_provider[$k]['url'] : '';
				$params_data = isset($core_provider[$k]['params']) ? $core_provider[$k]['params'] : '';
				
				$urlCon = array_combine(
								 array_keys($params_data), 
								 array_map( function($v){ 
									global $currentUrl, $title, $author, $details, $source, $media, $app_id;
									return str_replace(['[%url%]', '[%title%]', '[%author%]', '[%details%]', '[%source%]', '[%media%]', '[%app_id%]'], [$currentUrl, $title, $author, $details, $source, $media, $app_id], $v); 
								 }, $params_data)
							);
				
				$params = http_build_query($urlCon, '&');
				
				?>
					<li title="<?php echo $label;?>" class="xs-share-li <?php echo  esc_attr('share-'.$widget_style.' '.$k); ?>   <?php echo (!isset($global_data['show_counter']['enable']) || !isset($global_data['show_text']['enable']) || !isset($global_data['show_label']['enable'])) ? 'wslu-extra-data' : 'wslu-no-extra-data'; ?>" >
						<a href="javascript:void();" id="xs_feed_<?php echo $k?>" onclick="xs_feed_share(this);" data-xs-href="<?php echo $url_get.'?'.$params;?>">
							<?php if(!isset($global_data['show_icon']['enable'])):?>
							<div class="xs-social-icon">
								<span class="met-social met-social-<?php echo $k;?>"></span>
							</div>
							<?php endif; ?>
							
							<?php if(!isset($global_data['show_counter']['enable']) || !isset($global_data['show_text']['enable']) || !isset($global_data['show_label']['enable'])) : ?>
								<div class="wslu-both-counter-text ">
									<?php if(!isset($global_data['show_counter']['enable'])):?>
									<div class="xs-social-follower">
										<?php echo xs_format_num($counter);?>
									</div>
									<?php endif;
									if(!isset($global_data['show_text']['enable'])):?>
									<div class="xs-social-follower-text">
										<?php echo $text;?>
									</div>
									<?php endif; 
							
									if(!isset($global_data['show_label']['enable'])):
									?>
									<div class="xs-social-follower-label">
										<?php echo $label;?>
									</div>
									<?php endif;?>
								</div>
							<?php endif; ?>
						</a>
					</li>
				<?php
			}
		endforeach;
		?>
	</ul>
</div>
<?php endif;?>
<script>
function xs_feed_share(e){
	if(e){
		var getLink = e.getAttribute('data-xs-href');
		window.open(getLink, 'xs_feed_sharer', 'width=626,height=436');
	}
}
</script>