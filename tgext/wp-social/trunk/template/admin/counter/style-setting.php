<div class="wslu-social-login-main-wrapper">
    <?php 
    require_once( WSLU_LOGIN_PLUGIN . '/template/admin/counter/tab-menu.php');
    if($message_provider == 'show'){?>
    <div class="admin-page-framework-admin-notice-animation-container">
        <div 0="XS_Social_Login_Settings" id="XS_Social_Login_Settings" class="updated admin-page-framework-settings-notice-message admin-page-framework-settings-notice-container notice is-dismissible" style="margin: 1em 0px; visibility: visible; opacity: 1;">
            <p><?php echo esc_html__('Styles data have been updated.', 'wslu');?></p>
            <button type="button" class="notice-dismiss"><span class="screen-reader-text"><?php echo esc_html__('Dismiss this notice.', 'wslu');?></span></button>
        </div>
    </div>
    <?php }?>

    <form action="<?php echo esc_url(admin_url().'admin.php?page=wslu_counter_setting&tab=wslu_style_setting');?>" name="xs_style_submit_form" method="post" id="xs_style_form">
        <div class="xs-social-block-wraper">
            <div class="xs-global-section">

            <div class="wslu-social-style-data">

                <?php $td = 1; foreach($styleArr AS $styleKey=>$styleValue): ?>
                <div class="wslu-single-social-style-item">

                    <label for="_login_button_style__<?= $styleKey;?>" class="social_radio_button_label xs_label_wp_login"> 

                        <input class="social_radio_button wslu-global-radio-input" type="radio" id="_login_button_style__<?= $styleKey;?>" name="xs_style[login_button_style]" value="<?= $styleKey;?>" <?php echo (isset($return_data['login_button_style']) && $return_data['login_button_style'] == $styleKey) ? 'checked' : ''; ?> >
                    
                        <?php echo esc_html__($styleValue, 'wslu')?>

                        <div class="wslu-style-img xs-login-<?= $styleKey;?> <?php echo (isset($return_data['login_button_style']) && $return_data['login_button_style'] == $styleKey ) ? 'style-active ' : '';?>">
                            <img src="<?php echo esc_url(WSLU_LOGIN_PLUGIN_URL.'assets/images/screenshort/counter/img-'.$td.'.png'); ?>" alt="style-<?php echo $td; ?>">
                        </div>
                    </label>
                </div>
                <?php $td++;  endforeach; ?>

            </div>

            <button type="submit" name="style_setting_submit_form" class="xs-btn btn-special small"><?php echo esc_html__('Save Changes');?></button>

            </div>
        </div>
        <div class="xs-backdrop"></div>
    </form>
</div>