<?php
defined('ABSPATH') || die();
/** @var $this NextendSocialProviderAdmin */

$provider = $this->getProvider();
?>
<div class="nsl-admin-sub-content">
    <h2 class="title"><?php _e('Getting Started', 'nextend-facebook-connect'); ?></h2>

    <p style="max-width:55em;"><?php printf(__('To allow your visitors to log in with their %1$s account, first you must create an %1$s App. The following guide will help you through the %1$s App creation process. After you have created your %1$s App, head over to "Settings" and configure the given "%2$s" and "%3$s" according to your %1$s App.', 'nextend-facebook-connect'), "Yahoo
    ", "Client ID", "Client Secret"); ?></p>

    <h2 class="title"><?php printf(_x('Create %s', 'App creation', 'nextend-facebook-connect'), 'Yahoo App'); ?></h2>

    <ol>
        <li><?php printf(__('Navigate to %s', 'nextend-facebook-connect'), '<a href="https://developer.yahoo.com/apps/" target="_blank">https://developer.yahoo.com/apps/</a>'); ?></li>
        <li><?php printf(__('Log in with your %s credentials if you are not logged in.', 'nextend-facebook-connect'), 'Yahoo'); ?></li>
        <li><?php _e('Click on the "Create an App" button on the top right corner.', 'nextend-facebook-connect') ?></li>
        <li><?php _e('Fill the "Application Name" and select "Web Application" at "Application Type".', 'nextend-facebook-connect') ?></li>
        <li><?php _e('Enter a "Description" for your app!', 'nextend-facebook-connect') ?></li>
        <li><?php printf(__('Enter the URL of your site to the "Home Page URL" field: <b>%s</b>', 'nextend-facebook-connect'), site_url()); ?></li>
        <li><?php printf(__('Fill the "Callback Domain" field with your domain name probably: <b>%s</b> ', 'nextend-facebook-connect'), str_replace('www.', '', $_SERVER['HTTP_HOST'])) ?>
            <ul>
                <li><?php _e('<i>The value of the "Callback Domain" field can not be modified. If it would be necessary, you must create a new App!</i>', 'nextend-facebook-connect') ?></li>
            </ul>
        </li>
        <li><?php _e('Under the "API Permissions you should select "Profiles (Social Directory)" with either "Read Public" or "Read/Write Public and Private".', 'nextend-facebook-connect') ?>
            <ul>
                <li><?php _e('<u>Read Public:</u> retrieves only the basic fields, email address is not included!', 'nextend-facebook-connect') ?></li>
                <li><?php _e('<u>Read/Write Public and Private:</u> retrieves some extra fields, email address included!', 'nextend-facebook-connect') ?></li>
                <li><?php _e('<i>To modify these values in the future, you must create a new App! Also you will need to select the "API Permission" on our Setting tab according to the selected value!</i>', 'nextend-facebook-connect') ?></li>
            </ul>
        </li>
        <li><?php _e('Click "Create App".', 'nextend-facebook-connect') ?></li>
        <li><?php _e('On the top of the page, you will find the necessary "Client ID" and "Client Secret"! These will be needed in the plugin\'s settings.', 'nextend-facebook-connect') ?></li>
    </ol>

    <a href="<?php echo $this->getUrl('settings'); ?>"
       class="button button-primary"><?php printf(__('I am done setting up my %s', 'nextend-facebook-connect'), 'Yahoo App'); ?></a>
</div>