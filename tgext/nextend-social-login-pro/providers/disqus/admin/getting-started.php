<?php
defined('ABSPATH') || die();
/** @var $this NextendSocialProviderAdmin */

$provider = $this->getProvider();
?>
<div class="nsl-admin-sub-content">
    <h2 class="title"><?php _e('Getting Started', 'nextend-facebook-connect'); ?></h2>

    <p style="max-width:55em;"><?php printf(__('To allow your visitors to log in with their %1$s account, first you must create an %1$s App. The following guide will help you through the %1$s App creation process. After you have created your %1$s App, head over to "Settings" and configure the given "%2$s" and "%3$s" according to your %1$s App.', 'nextend-facebook-connect'), "Disqus", "API Key", "API Secret"); ?></p>

    <h2 class="title"><?php printf(_x('Create %s', 'App creation', 'nextend-facebook-connect'), 'Disqus App'); ?></h2>

    <ol>
        <li><?php printf(__('Navigate to %s', 'nextend-facebook-connect'), '<a href="https://disqus.com/api/applications/" target="_blank">https://disqus.com/api/applications/</a>'); ?></li>
        <li><?php printf(__('Log in with your %s credentials if you are not logged in.', 'nextend-facebook-connect'), 'Disqus'); ?></li>
        <li><?php _e('Click on the link "registering an application" under the Applications tab.', 'nextend-facebook-connect') ?></li>
        <li><?php _e('Enter a "Label" and "Description" for your App.', 'nextend-facebook-connect') ?></li>
        <li><?php printf(__('Fill "Website" with the url of your homepage, probably: <b>%s</b>', 'nextend-facebook-connect'), site_url()); ?></li>
        <li><?php _e('Complete the Human test and click the "Register my application" button.', 'nextend-facebook-connect') ?></li>
        <li><?php printf(__('Fill the "Domains" field with your domain name like: <b>%s</b>', 'nextend-facebook-connect'), $_SERVER['HTTP_HOST']); ?></li>
        <li><?php _e('Select "Read only" at Default Access under the Authentication section.', 'nextend-facebook-connect') ?></li>
        <li><?php printf(__('Add the following URL to the "Callback URL" field <b>%s</b> ', 'nextend-facebook-connect'), $provider->getLoginUrl()) ?></li>
        <li><?php _e('Click the "Save Changes" button!', 'nextend-facebook-connect') ?></li>
        <li><?php _e('Navigate to the "Details" tab of your Application!', 'nextend-facebook-connect') ?></li>
        <li><?php _e('Here you can see your "API Key" and "API Secret:". These will be needed in the plugin\'s settings.', 'nextend-facebook-connect'); ?></li>
   </ol>

    <a href="<?php echo $this->getUrl('settings'); ?>"
       class="button button-primary"><?php printf(__('I am done setting up my %s', 'nextend-facebook-connect'), 'Disqus App'); ?></a>
    
</div>