<?php
defined('ABSPATH') || die();
/** @var $this NextendSocialProviderAdmin */

$provider = $this->getProvider();
?>
<ol>
    <li><?php printf(__('Navigate to %s', 'nextend-facebook-connect'), '<a href="https://developer.paypal.com/developer/applications/" target="_blank">https://developer.paypal.com/developer/applications/</a>'); ?></li>
    <li><?php printf(__('Log in with your %s credentials if you are not logged in.', 'nextend-facebook-connect'), 'PayPal'); ?></li>
    <li><?php _e('Scroll down to  "REST API apps".', 'nextend-facebook-connect') ?></li>
    <li><?php printf(__('Click on the name of your %s App.', 'nextend-facebook-connect'), 'PayPal'); ?></li>
    <li><?php _e('Select the "Live" option on the top-right side. ', 'nextend-facebook-connect') ?></li>
    <li><?php _e('Scroll down to "LIVE APP SETTINGS", search the "Live Return URL" heading and click "Show".', 'nextend-facebook-connect') ?></li>
    <li><?php printf(__('Add the following URL to the "Live Return URL" field <b>%s</b> ', 'nextend-facebook-connect'), $provider->getLoginUrl()) ?></li>
    <li><?php _e('Click on "Save"', 'nextend-facebook-connect'); ?></li>
</ol>