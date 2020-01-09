<?php
defined('ABSPATH') || die();
/** @var $this NextendSocialProviderAdmin */

$provider = $this->getProvider();
?>
<div class="nsl-admin-sub-content">
    <h2 class="title"><?php _e('Getting Started', 'nextend-facebook-connect'); ?></h2>

    <p style="max-width:55em;"><?php printf(__('To allow your visitors to log in with their %1$s account, first you must create a %1$s App. The following guide will help you through the %1$s App creation process. After you have created your %1$s App, head over to "Settings" and configure the given "%2$s" and "%3$s" according to your %1$s App.', 'nextend-facebook-connect'), "VKontakte", "Application ID", "Secure key"); ?></p>

    <h2 class="title"><?php printf(_x('Create %s', 'App creation', 'nextend-facebook-connect'), 'VKontakte App'); ?></h2>

    <ol>
        <li><?php printf(__('Navigate to %s', 'nextend-facebook-connect'), '<a href="https://vk.com/apps?act=manage" target="_blank">https://vk.com/apps?act=manage</a>'); ?></li>
        <li><?php printf(__('Log in with your %s credentials if you are not logged in.', 'nextend-facebook-connect'), 'VK'); ?></li>
        <li><?php _e('Locate the blue "Create application" button and click on it.', 'nextend-facebook-connect'); ?></li>
        <li><?php _e('Enter the title of your app and select "Website".', 'nextend-facebook-connect') ?></li>
        <li><?php printf(__('Fill "Site address" with the url of your homepage, probably: <b>%s</b>', 'nextend-facebook-connect'), site_url()); ?></li>
        <li><?php printf(__('Fill the "Base domain" field with your domain, probably: <b>%s</b>', 'nextend-facebook-connect'), parse_url(site_url(), PHP_URL_HOST)); ?></li>
        <li><?php _e('When all fields are filled, create you app.', 'nextend-facebook-connect') ?></li>        
        <li><?php _e('You\'ll be sent a confirmation code via SMS which you need to type to be able to create the app.', 'nextend-facebook-connect') ?></li>
        <li><?php _e('Fill the form for your app and upload an app icon then hit Save.', 'nextend-facebook-connect') ?></li>
        <li><?php _e('Pick Settings at the left-hand menu ', 'nextend-facebook-connect') ?></li>
        <li><?php printf(__('Add the following URL to the "Authorized redirect URI" field <b>%s</b> ', 'nextend-facebook-connect'), $provider->getRedirectUriForApp()) ?></li>
        <li><?php _e('Save your app', 'nextend-facebook-connect') ?></li>
        <li><?php _e('Find the necessary Application ID and Secure key at the top of the Settings page where you just hit the save button.', 'nextend-facebook-connect') ?></li>
    </ol>

    <a href="<?php echo $this->getUrl('settings'); ?>"
       class="button button-primary"><?php printf(__('I am done setting up my %s', 'nextend-facebook-connect'), 'VKontakte App'); ?></a>

    
</div>