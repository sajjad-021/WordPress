<?php
defined('ABSPATH') || die();
/** @var $this NextendSocialProviderAdmin */

$provider = $this->getProvider();
?>
<ol>
    <li><?php printf(__('Navigate to %s', 'nextend-facebook-connect'), '<a href="https://developer.yahoo.com/apps/" target="_blank">https://developer.yahoo.com/apps/</a>'); ?></li>
    <li><?php printf(__('Log in with your %s credentials if you are not logged in', 'nextend-facebook-connect'), 'Yahoo'); ?></li>
    <li><?php printf(__('Click on the App which has its credentials associated with the plugin.', 'nextend-facebook-connect'), 'Yahoo'); ?></li>
    <li><?php printf(__('Check if the saved "Callback Domain" matches with your domain: <b>%s</b>', 'nextend-facebook-connect'), str_replace('www.', '', $_SERVER['HTTP_HOST'])) ?></li>
    <li><?php printf(__('If the Callback Domain matches with your domain, then your don\'t have anything else to do with this %s app.', 'nextend-facebook-connect'), 'Yahoo'); ?></li>
    <li><?php printf(__('The Callback Domain of %1$s apps can not be modified. So if the Callback Domain differs from your domain, you need to create a new app as you see in the Getting Started section of the %1$s provider.', 'nextend-facebook-connect'), 'Yahoo'); ?></li>
    <li><?php _e('Replace your old "Client ID" and "Client Secret" with the one of the new app!', 'nextend-facebook-connect'); ?></li>
</ol>