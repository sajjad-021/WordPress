<?php
defined('ABSPATH') || die();
/** @var $this NextendSocialProviderAdmin */

$provider = $this->getProvider();
?>
<div class="nsl-admin-sub-content">
    <h2 class="title"><?php _e('Getting Started', 'nextend-facebook-connect'); ?></h2>

    <p style="max-width:55em;"><?php printf(__('To allow your visitors to log in with their %1$s account, first you must create an %1$s App. The following guide will help you through the %1$s App creation process. After you have created your %1$s App, head over to "Settings" and configure the given "%2$s" and "%3$s" according to your %1$s App.', 'nextend-facebook-connect'), "WordPress.com", "Client ID", "Client Secret"); ?></p>

    <h2 class="title"><?php printf(_x('Create %s', 'App creation', 'nextend-facebook-connect'), 'WordPress.com App'); ?></h2>

    <ol>
        <li><?php printf(__('Navigate to %s', 'nextend-facebook-connect'), '<a href="https://developer.wordpress.com/apps/" target="_blank">https://developer.wordpress.com/apps/</a>'); ?></li>
        <li><?php printf(__('Log in with your %s credentials if you are not logged in.', 'nextend-facebook-connect'), 'WordPress.com'); ?></li>
        <li><?php _e('Click on the "Create New Application" button.', 'nextend-facebook-connect') ?></li>
        <li><?php _e('Enter a "Name" and "Description" for your App.', 'nextend-facebook-connect') ?></li>
        <li><?php printf(__('Fill "Website URL" with the url of your homepage, probably: <b>%s</b>', 'nextend-facebook-connect'), site_url()); ?></li>
        <li><?php printf(__('Add the following URL to the "Redirect URLs" field <b>%s</b> ', 'nextend-facebook-connect'), $provider->getLoginUrl()) ?></li>
        <li><?php _e('You can leave the "Javascript Origins" field blank!', 'nextend-facebook-connect') ?></li>
        <li><?php _e('Complete the human verification test.', 'nextend-facebook-connect'); ?></li>
        <li><?php _e('At the "Type" make sure "Web" is selected!', 'nextend-facebook-connect'); ?></li>
        <li><?php _e('Click the "Create" button!', 'nextend-facebook-connect'); ?></li>
        <li><?php _e('Click the name of your App either in the Breadcrumb navigation or next to Editing!', 'nextend-facebook-connect'); ?></li>
        <li><?php _e('Here you can see your "Client ID" and "Client Secret". These will be needed in the plugin\'s settings.', 'nextend-facebook-connect'); ?></li>
   </ol>

    <a href="<?php echo $this->getUrl('settings'); ?>"
       class="button button-primary"><?php printf(__('I am done setting up my %s', 'nextend-facebook-connect'), 'WordPress.com App'); ?></a>
    
</div>