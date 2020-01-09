<?php
defined('ABSPATH') || die();
/** @var $this NextendSocialProviderAdmin */

$provider = $this->getProvider();
?>
<ol>
    <li><?php printf(__('Navigate to %s', 'nextend-facebook-connect'), '<a href="https://developer.wordpress.com/apps/" target="_blank">https://developer.wordpress.com/apps/</a>'); ?></li>
    <li><?php printf(__('Log in with your %s credentials if you are not logged in.', 'nextend-facebook-connect'), 'WordPress.com'); ?></li>
    <li><?php printf(__('Click on the name of your %s App.', 'nextend-facebook-connect'), 'WordPress.com'); ?></li>

    <li><?php _e('Click "Manage Settings" under the Tools section!', 'nextend-facebook-connect') ?></li>
    <li><?php printf(__('Add the following URL to the "Redirect URLs" field <b>%s</b> ', 'nextend-facebook-connect'), $provider->getLoginUrl()) ?></li>
    <li><?php _e('Click on "Update"', 'nextend-facebook-connect'); ?></li>
</ol>