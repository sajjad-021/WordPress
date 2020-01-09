<?php
defined('ABSPATH') || die();
/** @var $this NextendSocialProviderAdmin */

$provider = $this->getProvider();
?>
<div class="nsl-admin-sub-content">
    <h2 class="title"><?php _e('Getting Started', 'nextend-facebook-connect'); ?></h2>

    <p style="max-width:55em;"><?php printf(__('To allow your visitors to log in with their %1$s account, first you must create an %1$s App. The following guide will help you through the %1$s App creation process. After you have created your %1$s App, head over to "Settings" and configure the given "%2$s" and "%3$s" according to your %1$s App.', 'nextend-facebook-connect'), "PayPal", "Client ID", "Secret"); ?></p>

    <h2 class="title"><?php printf(_x('Create %s', 'App creation', 'nextend-facebook-connect'), 'PayPal App'); ?></h2>

    <ol>
        <li><?php printf(__('Navigate to %s', 'nextend-facebook-connect'), '<a href="https://developer.paypal.com/developer/applications/" target="_blank">https://developer.paypal.com/developer/applications/</a>'); ?></li>
        <li><?php printf(__('Log in with your %s credentials if you are not logged in.', 'nextend-facebook-connect'), 'PayPal'); ?></li>
        <li><?php _e('Scroll down to  "REST API apps".', 'nextend-facebook-connect') ?></li>
        <li><?php _e('Click the "Create App" button.', 'nextend-facebook-connect') ?></li>
        <li><?php _e('Fill the "App Name" field and click "Create App" button.', 'nextend-facebook-connect') ?></li>
        <li><?php _e('Select the "Live" option on the top-right side. ', 'nextend-facebook-connect') ?></li>
        <li><?php _e('Scroll down to "LIVE APP SETTINGS", search the "Live Return URL" heading and click "Show".', 'nextend-facebook-connect') ?></li>
        <li><?php printf(__('Add the following URL to the "Live Return URL" field <b>%s</b> ', 'nextend-facebook-connect'), $provider->getLoginUrl()) ?></li>
        <li><?php _e('Scroll down to "App feature options" section and tick "Log In with PayPal".', 'nextend-facebook-connect') ?></li>
        <li><?php _e('Click "Advanced Options" which can be found at the end of text after "Connect with PayPal (formerly Log In with PayPal)".', 'nextend-facebook-connect') ?></li>
        <li><?php _e('Tick "Full name".', 'nextend-facebook-connect') ?></li>
        <li><?php _e('"Email address" now requires an App Review by PayPal. To get the email address as well, please submit your App for a review after your App configuration is finished. Once the App review is succesfull, you need to pick "Email address" here to retrieve the email of the user. Until then make sure the Email scope is not "Enabled" in our PayPal Settings tab.', 'nextend-facebook-connect') ?></li>

        <li><?php _e('Fill "Privacy policy URL" and  "User agreement URL".', 'nextend-facebook-connect') ?></li>
        <li><?php _e('When all fields are filled, click "Save".', 'nextend-facebook-connect') ?></li>
        <li><?php _e('Scroll up to "LIVE API CREDENTIALS" section and find the necessary "Client ID" and "Secret"! ( Make sure you are in "Live" mode and not "Sandbox". )', 'nextend-facebook-connect') ?></li>
    </ol>

    <a href="<?php echo $this->getUrl('settings'); ?>"
       class="button button-primary"><?php printf(__('I am done setting up my %s', 'nextend-facebook-connect'), 'PayPal App'); ?></a>
</div>