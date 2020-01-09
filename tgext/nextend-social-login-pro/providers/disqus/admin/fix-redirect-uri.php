<?php
defined('ABSPATH') || die();
/** @var $this NextendSocialProviderAdmin */

$provider = $this->getProvider();
?>
<ol>
    <li><?php printf(__('Navigate to %s', 'nextend-facebook-connect'), '<a href="https://disqus.com/api/applications/" target="_blank">https://disqus.com/api/applications/</a>'); ?></li>
    <li><?php printf(__('Log in with your %s credentials if you are not logged in.', 'nextend-facebook-connect'), 'Disqus'); ?></li>
    <li><?php printf(__('Click on the name of your %s App.', 'nextend-facebook-connect'), 'Disqus'); ?></li>

    <li><?php _e('Select the "Settings" tab and scroll down to the Authentication section!', 'nextend-facebook-connect') ?></li>
    <li><?php printf(__('Add the following URL to the "Callback URL" field <b>%s</b> ', 'nextend-facebook-connect'), $provider->getLoginUrl()) ?></li>
    <li><?php _e('Click on the "Save Changes" button.', 'nextend-facebook-connect'); ?></li>
</ol>