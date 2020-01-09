<?php
defined('ABSPATH') || die();
/** @var $this NextendSocialProviderAdmin */

$provider = $this->getProvider();
?>
<ol>
    <li><?php printf(__('Navigate to %s', 'nextend-facebook-connect'), '<a href="https://vk.com/apps?act=manage" target="_blank">https://vk.com/apps?act=manage</a>'); ?></li>
    <li><?php printf(__('Log in with your %s credentials if you are not logged in', 'nextend-facebook-connect'), 'VKontakte'); ?></li>
    <li><?php _e('Click on the Manage button at the App', 'nextend-facebook-connect'); ?></li>
    <li><?php _e('Go to the Settings menu', 'nextend-facebook-connect'); ?></li>
    <li><?php printf(__('Add the following URL to the "Authorized redirect URI:" field: <b>%s</b>', 'nextend-facebook-connect'), $provider->getRedirectUriForApp()); ?></li>
    <li><?php _e('Hit update to save the changes', 'nextend-facebook-connect'); ?></li>
</ol>