<?php
defined('ABSPATH') || die();
/** @var $this NextendSocialProviderAdmin */

$provider = $this->getProvider();

$settings = $provider->settings;
?>

<div class="nsl-admin-sub-content">
	<?php
    $this->renderSettingsHeader();
    ?>

    <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" novalidate="novalidate">

		<?php wp_nonce_field('nextend-social-login'); ?>
        <input type="hidden" name="action" value="nextend-social-login"/>
        <input type="hidden" name="view" value="provider-<?php echo $provider->getId(); ?>"/>
        <input type="hidden" name="subview" value="settings"/>
        <input type="hidden" name="settings_saved" value="1"/>
        <input type="hidden" name="tested" id="tested" value="<?php echo esc_attr($settings->get('tested')); ?>"/>
        <table class="form-table">
            <tbody>
            <tr>
                <th scope="row"><label for="client_id"><?php _e('Client ID', 'nextend-facebook-connect'); ?>
                            - <em>(<?php _e('Required', 'nextend-facebook-connect'); ?>)</em></label>
                </th>
                <td>
                    <input name="client_id" type="text" id="client_id"
                           value="<?php echo esc_attr($settings->get('client_id')); ?>" class="regular-text">
                    <p class="description"
                       id="tagline-client_id"><?php printf(__('If you are not sure what is your %1$s, please head over to <a href="%2$s">Getting Started</a>', 'nextend-facebook-connect'), 'Client ID', $this->getUrl()); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label
                            for="client_secret"><?php _e('Client Secret', 'nextend-facebook-connect'); ?>
                            - <em>(<?php _e('Required', 'nextend-facebook-connect'); ?>)</em></label></th>
                <td><input name="client_secret" type="text" id="client_secret"
                           value="<?php echo esc_attr($settings->get('client_secret')); ?>" class="regular-text">
                </td>
            </tr>


            <tr>
                <th scope="row"><?php _e('API Permission', 'nextend-facebook-connect'); ?></th>
                <td>
                    <fieldset>
                        <label><input type="radio" name="api_permission"
                                      value="r" <?php if ($settings->get('api_permission') == 'r') : ?> checked="checked" <?php endif; ?>>
                            <span><?php _e('Read Public', 'nextend-facebook-connect'); ?></span></label><br>
                        <label><input type="radio" name="api_permission"
                                      value="rw" <?php if ($settings->get('api_permission') == 'rw') : ?> checked="checked" <?php endif; ?>>
                            <span><?php _e('Read/Write Public and Private', 'nextend-facebook-connect'); ?></span></label><br>
                        <p class="description" id="tagline-api_permission"><?php _e('Email address is private, so you need "Read/Write Public and Private" permission if you want to access it.<br><b>Important note:</b> During the APP configuration, you will also need to select the API Permissions for Profiles (Social Directory) according to the chosen value.', 'nextend-facebook-connect'); ?></p>
                    </fieldset>
                </td>
            </tr>


            </tbody>
        </table>
        <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary"
                                 value="<?php _e('Save Changes'); ?>"></p>

        <?php
        $this->renderOtherSettings();

        $this->renderProSettings();
        ?>
    </form>
</div>