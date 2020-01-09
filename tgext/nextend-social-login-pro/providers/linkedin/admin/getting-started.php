<?php
defined('ABSPATH') || die();
/** @var $this NextendSocialProviderAdmin */

$provider = $this->getProvider();
?>
<div class="nsl-admin-sub-content">
    <h2 class="title"><?php _e('Getting Started', 'nextend-facebook-connect'); ?></h2>

    <p style="max-width:55em;"><?php printf(__('To allow your visitors to log in with their %1$s account, first you must create a %1$s App. The following guide will help you through the %1$s App creation process. After you have created your %1$s App, head over to "Settings" and configure the given "%2$s" and "%3$s" according to your %1$s App.', 'nextend-facebook-connect'), "LinkedIn", "Client  ID", "Client  secret"); ?></p>

    <h2 class="title"><?php printf(_x('Create %s', 'App creation', 'nextend-facebook-connect'), 'LinkedIn App'); ?></h2>

    <ol>
        <li><?php printf(__('Navigate to %s', 'nextend-facebook-connect'), '<a href="https://www.linkedin.com/developer/apps" target="_blank">https://www.linkedin.com/developer/apps</a>'); ?></li>
        <li><?php printf(__('Log in with your %s credentials if you are not logged in', 'nextend-facebook-connect'), 'LinkedIn'); ?></li>
        <li><?php _e('Locate the "Create app" button and click on it.', 'nextend-facebook-connect'); ?></li>
        <li><?php _e('Enter the name of your App to the "App name" field.', 'nextend-facebook-connect'); ?></li>
        <li><?php _e('Fill all the "App information" related fields!', 'nextend-facebook-connect'); ?></li>
        <li><?php _e('Read and agree the "API Terms of Use" then click the "Create App" button!', 'nextend-facebook-connect'); ?></li>
        <li><?php _e('You will end up in the App setting area. Click on the Auth tab.', 'nextend-facebook-connect'); ?></li>
        <li><?php printf(__('Find "OAuth 2.0 settings" section and add the following URL to the "Redirect URLs:" field: <b>%s</b>', 'nextend-facebook-connect'), $provider->getLoginUrl()); ?></li>
        <li><?php _e('Hit update to save the changes', 'nextend-facebook-connect'); ?></li>
        <li><?php _e('Find the necessary "Client ID" and "Client Secret" under the Application credentials section, on the Auth tab.', 'nextend-facebook-connect'); ?></li>
    </ol>

    <a href="<?php echo $this->getUrl('settings'); ?>"
       class="button button-primary"><?php printf(__('I am done setting up my %s', 'nextend-facebook-connect'), 'LinkedIn App'); ?></a>

    <br>
    <div class="nsl-admin-embed-youtube">
        <div></div>
        <iframe src="https://www.youtube.com/embed/Dfm-FeXuLNI?rel=0" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
    </div>
</div>