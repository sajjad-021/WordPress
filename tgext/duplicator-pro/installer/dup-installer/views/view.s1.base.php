<?php
defined("DUPXABSPATH") or die("");
/** IDE HELPERS */
/* @var $GLOBALS['DUPX_AC'] DUPX_ArchiveConfig */
/* @var $archive_config DUPX_ArchiveConfig */
/* @var $installer_state DUPX_InstallerState */

require_once($GLOBALS['DUPX_INIT'].'/classes/config/class.archive.config.php');
require_once($GLOBALS['DUPX_INIT'].'/views/classes/class.view.s1.php');

$paramsManager = DUPX_Paramas_Manager::getInstance();
//ARCHIVE FILE
if (DUPX_Conf_Utils::archiveExists()) {
    $arcCheck = 'Pass';
} else {
    if (DUPX_Conf_Utils::isConfArkPresent()) {
        $arcCheck = 'Warn';
    } else {
        $arcCheck = 'Fail';
    }
}

$installer_state		= DUPX_InstallerState::getInstance();
$is_overwrite_mode		= ($installer_state->mode === DUPX_InstallerMode::OverwriteInstall);
$is_wordpress			= DUPX_Server::isWordPress();
$is_dbonly				= $GLOBALS['DUPX_AC']->exportOnlyDB;

//REQUIRMENTS
$req = array();
$ret_is_dir_writable = DUPX_Server::is_dir_writable($GLOBALS['DUPX_ROOT']);
$req['10'] = $ret_is_dir_writable['ret'] ? 'Pass' : 'Fail';
$req['20'] = function_exists('mysqli_connect') ? 'Pass' : 'Fail';
$req['30'] = DUPX_Server::$php_version_safe ? 'Pass' : 'Fail';
$req['40'] = (DUPX_Custom_Host_Manager::getInstance()->isManaged() 
    && $GLOBALS['DUPX_AC']->wp_tableprefix != DUPX_WPConfig::getValueFromLocalWpConfig('table_prefix', 'variable'))
				? 'Fail' : 'Pass';

$all_req = in_array('Fail', $req) ? 'Fail' : 'Pass';
if ('Fail' == $all_req)	DUPX_U::maintenanceMode(false, $GLOBALS['DUPX_ROOT']);

//NOTICES
$openbase	= ini_get("open_basedir");
$datetime1	= $GLOBALS['DUPX_AC']->created;
$datetime2	= date("Y-m-d H:i:s");
$fulldays	= round(abs(strtotime($datetime1) - strtotime($datetime2))/86400);
$max_time_zero = ($GLOBALS['DUPX_ENFORCE_PHP_INI']) ? false : @set_time_limit(0);
$max_time_size = 314572800;  //300MB
$max_time_ini = ini_get('max_execution_time');
$max_time_warn = (is_numeric($max_time_ini) && $max_time_ini < 31 && $max_time_ini > 0) && DUPX_Conf_Utils::archiveSize() > $max_time_size;
$parent_has_wordfence = file_exists($GLOBALS['DUPX_ROOT'].'/../wp-content/plugins/wordfence/wordfence.php');


$notice = array();
$notice['10'] = ! $is_overwrite_mode ? 'Good' : 'Warn';
$notice['20'] = ! DUPX_Conf_Utils::isConfArkPresent() ? 'Good' : 'Warn';
if ($is_dbonly) {
	$notice['25'] =	$is_wordpress ? 'Good' : 'Warn';
}
$notice['30'] = $fulldays <= 180 ? 'Good' : 'Warn';
$notice['40'] = DUPX_Server::$php_version_53_plus	 ? 'Good' : 'Warn';

$packagePHP = $GLOBALS['DUPX_AC']->version_php;
$currentPHP = DUPX_Server::$php_version;
$packagePHPMajor = intval($packagePHP);
$currentPHPMajor = intval($currentPHP);
$notice['45'] = ($packagePHPMajor === $currentPHPMajor || $GLOBALS['DUPX_AC']->exportOnlyDB) ? 'Good' : 'Warn';

$notice['50'] = empty($openbase) ? 'Good' : 'Warn';
$notice['60'] = !$max_time_warn ? 'Good' : 'Warn';
$notice['70'] = !$parent_has_wordfence ? 'Good' : 'Warn';
$notice['80'] = !$GLOBALS['DUPX_AC']->is_outer_root_wp_config_file	? 'Good' : 'Warn';
if ($GLOBALS['DUPX_AC']->exportOnlyDB) {
	$notice['90'] = 'Good';
} else {
	$notice['90'] = (!$GLOBALS['DUPX_AC']->is_outer_root_wp_content_dir) 
						? 'Good' 
						: 'Warn';
}

$space_free = @disk_free_space($GLOBALS['DUPX_ROOT']); 
$archive_size = DUPX_Conf_Utils::archiveSize();
$notice['100'] = ($space_free && $archive_size > 0 && $archive_size > $space_free) 
                    ? 'Warn'
                    : 'Good';

$all_notice = in_array('Warn', $notice) ? 'Warn' : 'Good';

//SUMMATION
$req_success	= ($all_req == 'Pass');
$req_notice		= ($all_notice == 'Good');
$all_success	= ($req_success && $req_notice);
$agree_msg		= "To enable this button the checkbox above under the 'Terms & Notices' must be checked.";

$archive_config			= DUPX_ArchiveConfig::getInstance();

DUPX_Log::logTime('VIEW STEP 1 CHECK END', DUPX_Log::LV_DETAILED);
?>


<form id="s1-input-form" method="post" class="content-form">
    <?php DUPX_U_Html::getHeaderMain('Step <span class="step">1</span> of 4: Deployment'); ?>
    <input type="hidden" name="<?php echo DUPX_Security::VIEW_TOKEN; ?>" value="<?php echo DUPX_U::esc_attr(DUPX_CSRF::generate('step1')); ?>">
    <input type="hidden" name="<?php echo DUPX_Security::CTRL_TOKEN; ?>" value="<?php echo DUPX_CSRF::generate('ctrl-step1'); ?>"> 
    <input type="hidden" id="s1-input-dawn-status" name="dawn_status" />
    <?php 
    $paramsManager->getHtmlFormParam(DUPX_Paramas_Manager::PARAM_CTRL_ACTION, 'ctrl-step1');
    $paramsManager->getHtmlFormParam(DUPX_Paramas_Manager::PARAM_VIEW, 'step1');
    ?>
    <!-- ====================================
    ARCHIVE
    ==================================== -->    
    <?php DUPX_View_S1::infoTabs()?>

    <!-- ====================================
    VALIDATION
    ==================================== -->
    <div class="hdr-sub1 toggle-hdr open" data-type="toggle" data-target="#s1-area-sys-setup">
        <a id="s1-area-sys-setup-link"><i class="fa fa-plus-square"></i>Validation</a>
        <div class="<?php echo ( $req_success) ? 'status-badge status-badge-pass' : 'status-badge status-badge-fail'; ?>	">
            <?php echo ( $req_success) ? 'Pass' : 'Fail'; ?>
        </div>
    </div>
    <div id="s1-area-sys-setup" class="hdr-sub1-area no-display" >
        <div class='info-top'>The system validation checks help to make sure the system is ready for install.</div>

        <!-- REQUIREMENTS -->
        <div class="s1-reqs" id="s1-reqs-all">
            <div class="header">
                <table class="s1-checks-area">
                    <tr>
                        <td class="title">Requirements <small>(must pass)</small></td>
                        <td class="toggle"><a href="javascript:void(0)" onclick="DUPX.toggleAll('#s1-reqs-all')">[toggle]</a></td>
                    </tr>
                </table>
            </div>

		<!-- REQ 10 -->
		<div class="status <?php echo strtolower($req['10']); ?>"><?php echo $req['10']; ?></div>
		<div class="title" data-type="toggle" data-target="#s1-reqs10"><i class="fa fa-caret-right"></i> Permissions</div>
		<div class="info" id="s1-reqs10">
			<table>
				<tr>
					<td><b>Deployment Path:</b> </td>
					<td><i><?php echo "{$GLOBALS['DUPX_ROOT']}"; ?></i> </td>
				</tr>
				<tr>
					<td><b>Suhosin Extension:</b> </td>
					<td><?php echo extension_loaded('suhosin') ? "<i class='dupx-fail'>Enabled</i>" : "<i class='dupx-pass'>Disabled</i>"; ?> </td>
				</tr>
				<tr>
					<td><b>PHP Safe Mode:</b> </td>
					<td><?php echo (DUPX_Server::$php_safe_mode_on) ? "<i class='dupx-fail'>Enabled</i>" : "<i class='dupx-pass'>Disabled</i>"; ?> </td>
				</tr>
                <?php
                if (!empty($ret_is_dir_writable['failedObjects'])) {
                ?>
                    <tr>
                        <td colspan="2">
                        &nbsp;
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <b>Overwrite fails for these folders or files (change permissions or remove then restart):</b><br/>
                            <ul style="color:maroon; word-break: break-word; margin: 0 0 0 0; padding: 4px 0 0 15px; line-height: 1.7em;">
                                <?php
                                echo '<li>'.implode('</li><li>', $ret_is_dir_writable['failedObjects']).'</li>';
                                ?>
                            </ul>
                        </td>
                    </tr>
                <?php
                }
                ?>
			</table>                     
            
            <br/>
			The deployment path must be writable by PHP in order to extract the archive file.  Incorrect permissions and extension such as
			<a href="https://suhosin.org/stories/index.html" target="_blank">suhosin</a> can interfere with PHP's ability to write/extract files.
			Please see the <a href="https://snapcreek.com/duplicator/docs/faqs-tech/#faq-trouble-055-q" target="_blank">FAQ permission</a> help link for details.
			PHP with <a href='http://php.net/manual/en/features.safe-mode.php' target='_blank'>safe mode</a> should be disabled.  If Safe Mode is enabled then
			contact your hosting provider or server administrator to disable PHP safe mode.
		</div>
		<!-- REQ 20 -->
		<div class="status <?php echo strtolower($req['20']); ?>"><?php echo $req['20']; ?></div>
		<div class="title" data-type="toggle" data-target="#s1-reqs20"><i class="fa fa-caret-right"></i> PHP Mysqli</div>
		<div class="info" id="s1-reqs20">
			Support for the PHP <a href='http://us2.php.net/manual/en/mysqli.installation.php' target='_blank'>mysqli extension</a> is required.
			Please contact your hosting provider or server administrator to enable the mysqli extension.  <i>The detection for this call uses
				the function_exists('mysqli_connect') call.</i>
		</div>

		<!-- REQ 30 -->
		<div class="status <?php echo strtolower($req['30']); ?>"><?php echo $req['30']; ?></div>
		<div class="title" data-type="toggle" data-target="#s1-reqs30"><i class="fa fa-caret-right"></i> PHP Version</div>
		<div class="info" id="s1-reqs30">
            This server is running PHP: <b><?php echo DUPX_Server::$php_version ?></b>. <i>A minimum of PHP <?php echo DUPX_Boot::MINIMUM_PHP_VERSION; ?> is required</i>.
			Contact your hosting provider or server administrator and let them know you would like to upgrade your PHP version.
		</div>
        <?php    if (DUPX_Custom_Host_Manager::getInstance()->isManaged()) { ?>
		<!-- REQ 40 -->
		<div class="status <?php echo strtolower($req['40']); ?>"><?php echo $req['40']; ?></div>
		<div class="title" data-type="toggle" data-target="#s1-reqs40"><i class="fa fa-caret-right"></i> Table prefix of managed hosting</div>
		<div class="info" id="s1-reqs40">
			You are installing the package in the managed hosting.
			Your existing WordPress setup table prefix doesn't match the package creation source site's table prefix.
		</div>
        <?php } ?>
	</div><br/>


        <!-- ====================================
        NOTICES  -->
        <div class="s1-reqs" id="s1-notice-all">
            <div class="header">
                <table class="s1-checks-area">
                    <tr>
                        <td class="title">Notices <small>(optional)</small></td>
                        <td class="toggle"><a href="javascript:void(0)" onclick="DUPX.toggleAll('#s1-notice-all')">[toggle]</a></td>
                    </tr>
                </table>
            </div>

		<!-- NOTICE 10: OVERWRITE INSTALL -->
		<?php if ($is_overwrite_mode && $is_wordpress) :?>
			<div class="status fail">Warn</div>
			<div class="title" data-type="toggle" data-target="#s1-notice10"><i class="fa fa-caret-right"></i> Overwrite Install</div>
			<div class="info" id="s1-notice10">
				<b>Deployment Path:</b> <i><?php echo "{$GLOBALS['DUPX_ROOT']}"; ?></i>
				<br/><br/>

				Duplicator is in "Overwrite Install" mode because it has detected an existing WordPress site at the deployment path above.  This mode allows for the installer
				to be dropped directly into an existing WordPress site and overwrite its contents.   Any content inside of the archive file
				will <u>overwrite</u> the contents from the deployment path.  To continue choose one of these options:

				<ol>
					<li>Ignore this notice and continue with the install if you want to overwrite this sites files.</li>
					<li>Move this installer and archive to another empty directory path to keep this sites files.</li>
				</ol>

				<small style="color:maroon">
					<b>Notice:</b> Existing content such as plugin/themes/images will still show-up after the install is complete if they did not already exist in
					the archive file. For example if you have an SEO plugin in the current site but that same SEO plugin <u>does not exist</u> in the archive file
					then that plugin will display as a disabled plugin after the install is completed. The same concept with themes and images applies.  This will
					not impact the sites operation, and the behavior is expected.
				</small>
				<br/><br/>


				<small style="color:#025d02">
					<b>Recommendation:</b> It is recommended you only overwrite WordPress sites that have a minimal	setup (plugins/themes).  Typically a fresh install or a
					cPanel 'one click' install is the best baseline to work from when using this mode but is not required.
				</small>
			</div>

		<!-- NOTICE 20: ARCHIVE EXTRACTED -->
		<?php elseif (DUPX_Conf_Utils::isConfArkPresent()) :?>
			<div class="status fail">Warn</div>
			<div class="title" data-type="toggle" data-target="#s1-notice20"><i class="fa fa-caret-right"></i> Archive Extracted</div>
			<div class="info" id="s1-notice20">
				<b>Deployment Path:</b> <i><?php echo "{$GLOBALS['DUPX_ROOT']}"; ?></i>
				<br/><br/>

				The installer has detected that the archive file has been extracted to the deployment path above.  To continue choose one of these options:

				<ol>
					<li>Skip the extraction process by <a href="javascript:void(0)" onclick="DUPX.getManaualArchiveOpt()">[enabling manual archive extraction]</a> </li>
					<li>Ignore this message and continue with the install process to re-extract the archive file.</li>
				</ol>

				<small>Note: This test looks for a file named <i>dup-wp-config-arc__[HASH].txt</i> in the dup-installer directory.  If the file exists then this notice is shown.
				The <i>dup-wp-config-arc__[HASH].txt</i> file is created with every archive and removed once the install is complete.  For more details on this process see the
				<a href="https://snapcreek.com/duplicator/docs/faqs-tech/#faq-installer-015-q" target="_blank">manual extraction FAQ</a>.</small>
			</div>
		<?php endif; ?>

		<!-- NOTICE 25: DATABASE ONLY -->
		<?php if ($is_dbonly && ! $is_wordpress) :?>
			<div class="status fail">Warn</div>
			<div class="title" data-type="toggle" data-target="#s1-notice25"><i class="fa fa-caret-right"></i> Database Only</div>
			<div class="info" id="s1-notice25">
				<b>Deployment Path:</b> <i><?php echo "{$GLOBALS['DUPX_ROOT']}"; ?></i>
				<br/><br/>

				The installer has detected that a WordPress site does not exist at the deployment path above. This installer is currently in 'Database Only' mode because that is
				how the archive was created.  If core WordPress site files do not exist at the path above then they will need to be placed there in order for a WordPress site
				to properly work.  To continue choose one of these options:

				<ol>
					<li>Place this installer and archive at a path where core WordPress files already exist to hide this message. </li>
					<li>Create a new package that includes both the database and the core WordPress files.</li>
					<li>Ignore this message and install only the database (for advanced users only).</li>
				</ol>

				<small>Note: This test simply looks for the directories <?php echo DUPX_Server::$wpCoreDirsList; ?> and a wp-config.php file.  If they are not found in the
				deployment path above then this notice is shown.</small>

			</div>
		<?php endif; ?>
			

		<!-- NOTICE 30 -->
		<div class="status <?php echo ($notice['30'] == 'Good') ? 'pass' : 'fail' ?>"><?php echo $notice['30']; ?></div>
		<div class="title" data-type="toggle" data-target="#s1-notice30"><i class="fa fa-caret-right"></i> Package Age</div>
		<div class="info" id="s1-notice30">
			This package is <?php echo "{$fulldays}"; ?> day(s) old. Packages older than 180 days might be considered stale.  It is recommended to build a new
			package unless your aware of the content and its data.  This is message is simply a recommendation.
		</div>

        <!-- NOTICE 45 -->
		<div class="status <?php echo ($notice['45'] == 'Good') ? 'pass' : 'fail' ?>"><?php echo $notice['45']; ?></div>
		<div class="title" data-type="toggle" data-target="#s1-notice45"><i class="fa fa-caret-right"></i> PHP Version mismatch</div>
		<div class="info" id="s1-notice45">
			<?php
                $cssStyle   = $notice['45'] == 'Good' ? 'color:green' : 'color:red';
				echo "<b style='{$cssStyle}'>You are migrating site from PHP {$packagePHP} to PHP {$currentPHP}</b>.<br/>"
                    ."If the PHP version of your website is different than the PHP version of your package 
                    it MAY cause problems with the functioning of your website.<br/>";
                ?>
            </div>

		<!-- NOTICE 50 -->
		<div class="status <?php echo ($notice['50'] == 'Good') ? 'pass' : 'fail' ?>"><?php echo $notice['50']; ?></div>
		<div class="title" data-type="toggle" data-target="#s1-notice50"><i class="fa fa-caret-right"></i> PHP Open Base</div>
		<div class="info" id="s1-notice50">
			<b>Open BaseDir:</b> <i><?php echo $notice['50'] == 'Good' ? "<i class='dupx-pass'>Disabled</i>" : "<i class='dupx-fail'>Enabled</i>"; ?></i>
			<br/><br/>

                If <a href="http://php.net/manual/en/ini.core.php#ini.open-basedir" target="_blank">open_basedir</a> is enabled and you're
                having issues getting your site to install properly please work with your host and follow these steps to prevent issues:
                <ol style="margin:7px; line-height:19px">
                    <li>Disable the open_basedir setting in the php.ini file</li>
                    <li>If the host will not disable, then add the path below to the open_basedir setting in the php.ini<br/>
                        <i style="color:maroon">"<?php echo str_replace('\\', '/', dirname( __FILE__ )); ?>"</i>
                    </li>
                    <li>Save the settings and restart the web server</li>
                </ol>
                Note: This warning will still show if you choose option #2 and open_basedir is enabled, but should allow the installer to run properly.  Please work with your
                hosting provider or server administrator to set this up correctly.
            </div>

		<!-- NOTICE 60 -->
		<div class="status <?php echo ($notice['60'] == 'Good') ? 'pass' : 'fail' ?>"><?php echo $notice['60']; ?></div>
		<div class="title" data-type="toggle" data-target="#s1-notice60"><i class="fa fa-caret-right"></i> PHP Timeout</div>
		<div class="info" id="s1-notice60">
			<b>Archive Size:</b> <?php echo DUPX_U::readableByteSize(DUPX_Conf_Utils::archiveSize()) ?>  <small>(detection limit is set at <?php echo DUPX_U::readableByteSize($max_time_size) ?>) </small><br/>
			<b>PHP max_execution_time:</b> <?php echo "{$max_time_ini}"; ?> <small>(zero means not limit)</small> <br/>
			<b>PHP set_time_limit:</b> <?php echo ($max_time_zero) ? '<i style="color:green">Success</i>' : '<i style="color:maroon">Failed</i>' ?>
			<br/><br/>

                The PHP <a href="http://php.net/manual/en/info.configuration.php#ini.max-execution-time" target="_blank">max_execution_time</a> setting is used to
                determine how long a PHP process is allowed to run.  If the setting is too small and the archive file size is too large then PHP may not have enough
                time to finish running before the process is killed causing a timeout.
                <br/><br/>

                Duplicator Pro attempts to turn off the timeout by using the
                <a href="http://php.net/manual/en/function.set-time-limit.php" target="_blank">set_time_limit</a> setting.   If this notice shows as a warning then it is
                still safe to continue with the install.  However, if a timeout occurs then you will need to consider working with the max_execution_time setting or extracting the
                archive file using the 'Manual Archive Extraction' method.
                Please see the	<a href="https://snapcreek.com/duplicator/docs/faqs-tech/#faq-trouble-100-q" target="_blank">FAQ timeout</a> help link for more details.
        </div>

        <!-- NOTICE 70 -->
        <div class="status <?php echo ($notice['70'] == 'Good') ? 'pass' : 'fail' ?>"><?php echo $notice['70']; ?></div>
        <div class="title" data-type="toggle" data-target="#s1-notice08"><i class="fa fa-caret-right"></i> Wordfence</div>
        <div class="info" id="s1-notice08">
            <?php if( $parent_has_wordfence): ?>
            You are installing in a subdirectory of another site that has Wordfence installed.
            Temporarily deactivate Wordfence on the parent site before continuing with the install.
            <?php else: ?>
            Having Wordfence in a parent site can interfere with the install, however no such condition was detected.
            <?php endif;?>
        </div>

        <!-- NOTICE 80 -->
		<div class="status <?php echo ($notice['80'] == 'Good') ? 'pass' : 'fail' ?>"><?php echo DUPX_U::esc_html($notice['80']); ?></div>
		<div class="title" data-type="toggle" data-target="#s1-notice80"><i class="fa fa-caret-right"></i> wp-config.php file location</div>
		<div class="info" id="s1-notice80">
			When this item shows a warning, it indicates the wp-config.php file was detected in the directory above the WordPress root folder on the source site. 
			<br/><br/>
			The Duplicator Installer will place the wp-config.php file in the root folder of the WordPress installation. This will not affect operation of the site.
		</div>

		<!-- NOTICE 90 -->
		<div class="status <?php echo ($notice['90'] == 'Good') ? 'pass' : 'fail' ?>"><?php echo DUPX_U::esc_html($notice['90']); ?></div>
		<div class="title" data-type="toggle" data-target="#s1-notice90"><i class="fa fa-caret-right"></i> wp-content directory location</div>
		<div class="info" id="s1-notice90">
			When this item shows a warning, it indicates the wp-content directory was not in the WordPress root folder on the source site.
			<br/><br/>
			The Duplicator Installer will place the wp-content directory in the WordPress root folder of the WordPress installation. This will not affect operation of the site.
		</div>

        <!-- NOTICE 100 -->
		<div class="status <?php echo ($notice['100'] == 'Good') ? 'pass' : 'fail' ?>"><?php echo DUPX_U::esc_html($notice['100']); ?></div>
		<div class="title" data-type="toggle" data-target="#s1-notice100"><i class="fa fa-caret-right"></i> Sufficient disk space</div>
		<div class="info" id="s1-notice100">
        <?php
        echo ($notice['100'] == 'Good')
                ? 'You have sufficient disk space in your machine to extract the archive.'
                : 'You don’t have sufficient disk space in your machine to extract the archive. Ask your host to increase disk space.'
        ?>
		</div>
        </div>
    </div>

    <!-- ====================================
    MULTISITE PANEL
    ==================================== -->
    <?php DUPX_View_S1::multisiteOptions(); ?>
    
    <!-- ====================================
    OPTIONS
    ==================================== -->
    <div class="hdr-sub1 toggle-hdr open" data-type="toggle" data-target="#s1-area-adv-opts">
        <a href="javascript:void(0)"><i class="fa fa-plus-square"></i>Options</a>
    </div>
    <div id="s1-area-adv-opts" class="hdr-sub1-area no-display">
        <div class="help-target">
            <?php DUPX_View_Funcs::helpIconLink('step1'); ?>
        </div>
        <?php DUPX_View_S1::generalOptions(); ?>
        <br/><br/>
        <?php DUPX_View_S1::advancedOptions(); ?>
    </div>

    <?php
    $req_counts = array_count_values($req);
    $is_only_permission_issue = (isset($req_counts['Fail']) && 1 == $req_counts['Fail'] && 'Fail' == $req[10] && 'Fail' == $all_req && 'Fail' != $arcCheck);
    ?>

    <?php if (!$req_success || $arcCheck == 'Fail') : ?>
        <div class="s1-err-msg" <?php if ($is_only_permission_issue) { ?>style="padding: 0 0 20px 0;"<?php } ?>>
            <i>
                This installation will not be able to proceed until the archive and validation sections both pass. Please adjust your servers settings or contact your
                server administrator, hosting provider or visit the resources below for additional help.
            </i>
            <div style="padding:10px">
                &raquo; <a href="https://snapcreek.com/duplicator/docs/faqs-tech/" target="_blank">Technical FAQs</a> <br/>
                &raquo; <a href="https://snapcreek.com/support/docs/" target="_blank">Online Documentation</a> <br/>
            </div>
        </div>
    <?php
        $is_next_btn_html = false;
    else :
        $is_next_btn_html = true;
    endif;
    
    if ($is_only_permission_issue) { ?>
        <div class="s1-accept-check">
            <input id="accept-perm-error" name="accept-perm-error" type="checkbox" onclick="DUPX.showHideNextBtn(this)" />
            <label for="accept-perm-error" style="color: #AF0000;">I would like to proceed with my own risk despite the permission error</label><br/>
        </div>
    <?php
    }
    if ($is_next_btn_html || $is_only_permission_issue) {
    ?>
        <div class="footer-buttons" <?php if ($is_only_permission_issue) { ?>style="display: none;"<?php } ?>>
            <div class="content-left">
                <?php DUPX_View_S1::acceptAndContinue(); ?>
            </div>
            <div class="content-right" >
            <button id="s1-deploy-btn" type="button" title="<?php echo $agree_msg; ?>" onclick="DUPX.processNext()"  class="default-btn"> Next <i class="fa fa-caret-right"></i> </button>
            </div>
       </div>
    <?php
    }
    ?>
</form>


<!-- =========================================
VIEW: STEP 1 - AJAX RESULT
Auto Posts to view.step2.php
========================================= -->

<form id='s1-result-form' method="post" class="content-form" style="display:none">
    <?php DUPX_U_Html::getHeaderMain('Step <span class="step">1</span> of 4: Extraction'); ?>
    <!--  POST PARAMS -->
    <div class="dupx-debug">
        <i>Step 1 - AJAX Response</i>
        <input type="hidden" name="view" value="step2" />
        <input type="hidden" name="<?php echo DUPX_Security::VIEW_TOKEN; ?>" value="<?php echo DUPX_U::esc_attr(DUPX_CSRF::generate('step2')); ?>">
        <input type="hidden" name="json" id="ajax-json" />
        <textarea id='ajax-json-debug' name='json_debug_view'></textarea>
        <input type='submit' value='manual submit'>
    </div>

    <!--  PROGRESS BAR -->
    <div id="progress-area">
        <div style="width:500px; margin:auto">
            <div class="progress-text"><i class="fas fa-circle-notch fa-spin"></i> Extracting Archive Files<span id="progress-pct"></span></div>
            <div id="secondary-progress-text"></div>
            <div id="progress-notice"></div>
            <div id="progress-bar"></div>
            <h3> Please Wait...</h3><br/><br/>
            <i>Keep this window open during the extraction process.</i><br/>
            <i>This can take several minutes.</i>
        </div>
    </div>

    <!--  AJAX SYSTEM ERROR -->
    <div id="ajaxerr-area" style="display:none">
        <p>Please try again an issue has occurred.</p>
        <div style="padding: 0px 10px 10px 0px;">
            <div id="ajaxerr-data">An unknown issue has occurred with the file and database setup process.  Please see the <?php DUPX_View_Funcs::installerLogLink(); ?> file for more details.</div>
            <div style="text-align:center; margin:10px auto 0px auto">
                <input type="button" class="default-btn" onclick="DUPX.hideErrorResult()" value="&laquo; Try Again" /><br/><br/>
                <i style='font-size:11px'>See online help for more details at <a href='https://snapcreek.com/ticket' target='_blank'>snapcreek.com</a></i>
            </div>
        </div>
    </div>
</form>
<?php DUPX_Log::logTime('VIEW STEP 1 SCRIPTS START', DUPX_Log::LV_DETAILED); ?>
<script>
    
    var exeSafeModeInputId = <?php echo DupProSnapJsonU::wp_json_encode($paramsManager->getFormItemId(DUPX_Paramas_Manager::PARAM_SAFE_MODE)); ?>; 
    var htConfigInputId =  <?php echo DupProSnapJsonU::wp_json_encode($paramsManager->getFormItemId(DUPX_Paramas_Manager::PARAM_HTACCESS_CONFIG)); ?>;
    var htConfigWrapperId =  <?php echo DupProSnapJsonU::wp_json_encode($paramsManager->getFormWrapperId(DUPX_Paramas_Manager::PARAM_HTACCESS_CONFIG)); ?>;
    var otConfigInputId =  <?php echo DupProSnapJsonU::wp_json_encode($paramsManager->getFormItemId(DUPX_Paramas_Manager::PARAM_OTHER_CONFIG)); ?>;
    var otConfigWrapperId =  <?php echo DupProSnapJsonU::wp_json_encode($paramsManager->getFormWrapperId(DUPX_Paramas_Manager::PARAM_OTHER_CONFIG)); ?>;
    var clientSideKickoffInputId = <?php echo DupProSnapJsonU::wp_json_encode($paramsManager->getFormItemId(DUPX_Paramas_Manager::PARAM_CLIENT_KICKOFF)); ?>; 
    var archiveEngineInputId = <?php echo DupProSnapJsonU::wp_json_encode($paramsManager->getFormItemId(DUPX_Paramas_Manager::PARAM_ARCHIVE_ENGINE)); ?>; 
    var removeRedundantWrapperId = <?php echo DupProSnapJsonU::wp_json_encode($paramsManager->getFormWrapperId(DUPX_Paramas_Manager::PARAM_REMOVE_RENDUNDANT)); ?>; 
        
    DUPX.toggleSetupType = function ()
    {
        var val = $("input:radio[name='setup_type']:checked").val();
        $('div.s1-setup-type-sub').hide();
        $('#s1-setup-type-sub-' + val).show(200);
    };

    DUPX.getManaualArchiveOpt = function ()
    {
        $("html, body").animate({scrollTop: $(document).height()}, 1500);
        $("div[data-target='#s1-area-adv-opts']").find('i.fa').removeClass('fa-plus-square').addClass('fa-minus-square');
        $('#s1-area-adv-opts').show(1000);
        $('#' + archiveEngineInputId).val('manual').focus();
    };

    DUPX.startExtraction = function()
    {
        var isManualExtraction = ($('#' + archiveEngineInputId).val() == '<?php echo DUP_PRO_Extraction::ENGINE_MANUAL; ?>');
        var zipEnabled = <?php echo DupProSnapLibStringU::boolToString($archive_config->isZipArchive()); ?>;
        var chunkingEnabled  = ($('#' + archiveEngineInputId).val() == '<?php echo DUP_PRO_Extraction::ENGINE_ZIP_CHUNK; ?>');

        $("#operation-text").text("Extracting Archive Files");

        if (zipEnabled || isManualExtraction) {
            if(chunkingEnabled){
                DUPX.runChunkedExtraction(undefined);
            } else {
                DUPX.runStandardExtraction();
            }
        } else {
            DUPX.kickOffDupArchiveExtract();
        }
    }

    DUPX.processNext = function ()
    {
        DUPX.startExtraction();
    };

    DUPX.updateProgressPercent = function (percent)
    {
        var percentString = '';
        if (percent > 0) {
            percentString = ' ' + percent + '%';
        }
        $("#progress-pct").text(percentString);
    };

    DUPX.updateDupArchiveProgress = function(itemIndex, totalItems)
    {
        itemIndex++;
        var itemIndexString		= DUPX.Util.formatBytes(itemIndex);  //itemIndex.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        var totalItemsString	= DUPX.Util.formatBytes(totalItems); //totalItems.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        var s = "Bytes processed: " + itemIndexString + " of " + totalItemsString;
        $("#secondary-progress-text").text(s);
    }

    DUPX.updateZipArchiveProgress = function(itemIndex, totalItems)
    {
        itemIndex++;
        var itemIndexString		= itemIndex.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        var totalItemsString	= totalItems.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        var s =  "Files processed: " + itemIndexString + " of " + totalItemsString;
        $("#secondary-progress-text").text(s);
    }

    DUPX.clearDupArchiveStatusTimer = function ()
    {
        if (DUPX.dupArchiveStatusIntervalID != -1) {
            clearInterval(DUPX.dupArchiveStatusIntervalID);
            DUPX.dupArchiveStatusIntervalID = -1;
        }
    };

    DUPX.getCriticalFailureText = function(failures)
    {
        var retVal = null;

        if((failures !== null) && (typeof failures !== 'undefined')) {
            var len = failures.length;

            for(var j = 0; j < len; j++) {
                failure = failures[j];
                if(failure.isCritical) {
                    retVal = failure.description;
                    break;
                }
            }
        }

        return retVal;
    };

    DUPX.DAWSProcessingFailed = function(errorText)
    {
        DUPX.clearDupArchiveStatusTimer();
        $('#ajaxerr-data').html(errorText);
        DUPX.hideProgressBar();
    };

DUPX.handleDAWSProcessingProblem = function(errorText, pingDAWS)
{
	DUPX.DAWS.FailureCount++;

	if(DUPX.DAWS.FailureCount <= DUPX.DAWS.MaxRetries) {
		var callback = DUPX.pingDAWS;

		if(pingDAWS) {
			console.log('!!!PING FAILURE #' + DUPX.DAWS.FailureCount);
		} else {
			console.log('!!!KICKOFF FAILURE #' + DUPX.DAWS.FailureCount);
			callback = DUPX.kickOffDupArchiveExtract;
		}

		DUPX.throttleDelay = 9;	// Equivalent of 'low' server throttling
		console.log('Relaunching in ' + DUPX.DAWS.RetryDelayInMs);
		setTimeout(callback, DUPX.DAWS.RetryDelayInMs);
	}
	else {
		console.log('Too many failures.');
		DUPX.DAWSProcessingFailed(errorText);
	}
};


DUPX.handleDAWSCommunicationProblem = function(xHr, pingDAWS, textStatus, page)
{
	DUPX.DAWS.FailureCount++;

	if(DUPX.DAWS.FailureCount <= DUPX.DAWS.MaxRetries) {

		var callback = DUPX.pingDAWS;

		if(pingDAWS) {
			console.log('!!!PING FAILURE #' + DUPX.DAWS.FailureCount);
		} else {
			console.log('!!!KICKOFF FAILURE #' + DUPX.DAWS.FailureCount);
			callback = DUPX.kickOffDupArchiveExtract;
		}
		console.log(xHr);
		DUPX.throttleDelay = 9;	// Equivalent of 'low' server throttling
		console.log('Relaunching in ' + DUPX.DAWS.RetryDelayInMs);
		setTimeout(callback, DUPX.DAWS.RetryDelayInMs);
	}
	else {
		console.log('Too many failures.');
		DUPX.ajaxCommunicationFailed(xHr, textStatus, page);
	}
};

// Will either query for status or push it to continue the extraction
DUPX.pingDAWS = function ()
{
	console.log('pingDAWS:start');
	var request = <?php echo DupProSnapJsonU::wp_json_encode_pprint(array(
        DUPX_Ctrl_ajax::AJAX_NAME => true,
        DUPX_Ctrl_ajax::ACTION_NAME => DUPX_Ctrl_ajax::ACTION_DAWN,
        DUPX_Ctrl_ajax::TOKEN_NAME => DUPX_Ctrl_ajax::generateToken(DUPX_Ctrl_ajax::ACTION_DAWN)
    )); ?>;
	var isClientSideKickoff = DUPX.isClientSideKickoff();

	if (isClientSideKickoff) {
		console.log('pingDAWS:client side kickoff');
		request.action = "expand";
		request.client_driven = 1;
		request.throttle_delay = DUPX.throttleDelay;
		request.worker_time = DUPX.DAWS.PingWorkerTimeInSec;
	} else {
		console.log('pingDAWS:not client side kickoff');
		request.action = "get_status";
	}

	console.log("pingDAWS:action=" + request.action);
	console.log("daws url=" + DUPX.DAWS.Url);

	$.ajax({
		type: "POST",
		timeout: DUPX.DAWS.PingWorkerTimeInSec * 2000, // Double worker time and convert to ms
		url: DUPX.DAWS.Url,
		data: request,
		success: function (respData, textStatus, xHr) {
            try {
                var data = DUPX.parseJSON(respData);
            } catch(err) {
                console.error(err);
                console.error('JSON parse failed for response data: ' + respData);
                console.log('AJAX error. textStatus=');
                console.log(textStatus);
                DUPX.handleDAWSCommunicationProblem(xHr, true, textStatus, 'ping');
                return false;
            }

			DUPX.DAWS.FailureCount = 0;
			console.log("pingDAWS:AJAX success. Resetting failure count");

			// DATA FIELDS
			// archive_offset, archive_size, failures, file_index, is_done, timestamp
			if (typeof (data) != 'undefined' && data.pass == 1) {

				console.log("pingDAWS:Passed");

				var status = data.status;
				var percent = Math.round((status.archive_offset * 100.0) / status.archive_size);

				console.log("pingDAWS:updating progress percent");
				DUPX.updateProgressPercent(percent);
                DUPX.updateDupArchiveProgress(status.archive_offset, status.archive_size);

				var criticalFailureText = DUPX.getCriticalFailureText(status.failures);

				if(status.failures.length > 0) {
					console.log("pingDAWS:There are failures present. (" + status.failures.length) + ")";
				}

				if (criticalFailureText === null) {
					console.log("pingDAWS:No critical failures");
					if (status.is_done) {

						console.log("pingDAWS:archive has completed");
						if(status.failures.length > 0) {

							console.log(status.failures);
							var errorMessage = "pingDAWS:Problems during extract. These may be non-critical so continue with install.\n------\n";
							var len = status.failures.length;

							for(var j = 0; j < len; j++) {
								failure = status.failures[j];
								errorMessage += failure.subject + ":" + failure.description + "\n";
							}

							alert(errorMessage);
						}

						DUPX.clearDupArchiveStatusTimer();
						console.log("pingDAWS:calling finalizeDupArchiveExtraction");
						DUPX.finalizeDupArchiveExtraction(status);
						console.log("pingDAWS:after finalizeDupArchiveExtraction");

						var dataJSON = JSON.stringify(data);

						// Don't stop for non-critical failures - just display those at the end
						$("#ajax-json").val(escape(dataJSON));

						<?php if (!$GLOBALS['DUPX_DEBUG']) : ?>
						setTimeout(function () {
							$('#s1-result-form').submit();
						}, 500);
						<?php endif; ?>
						$('#progress-area').fadeOut(1000);
						//Failures aren't necessarily fatal - just record them for later display

						$("#ajax-json-debug").val(dataJSON);
					} else if (isClientSideKickoff) {
						console.log('pingDAWS:Archive not completed so continue ping DAWS in 500');
						setTimeout(DUPX.pingDAWS, 500);
					}
				}
				else {
					// If we get a critical failure it means it's something we can't recover from so no purpose in retrying, just fail immediately.
                    console.log("pingDAWS:critical failures present, data:" , data);
					var errorString = 'Error Processing Step 1<br/>';
					errorString += criticalFailureText;
					DUPX.DAWSProcessingFailed(errorString);
				}
			} else {
				var errorString = 'Error Processing Step 1<br/>';
                console.log("pingDAWS: success but data problem, data:" , data);
				errorString += data.error;
				DUPX.handleDAWSProcessingProblem(errorString, true);
			}
		},
		error: function (xHr, textStatus) {
			console.log('AJAX error. textStatus=');
			console.log(textStatus);
			DUPX.handleDAWSCommunicationProblem(xHr, true, textStatus, 'ping');
		}
	});
};


DUPX.isClientSideKickoff = function()
{
	return $('#' + clientSideKickoffInputId).is(':checked');
};

DUPX.kickOffDupArchiveExtract = function ()
{
	console.log('kickOffDupArchiveExtract:start');
	var $form = $('#s1-input-form');
	var isClientSideKickoff = DUPX.isClientSideKickoff();

	var request = <?php echo DupProSnapJsonU::wp_json_encode_pprint(array(
        DUPX_Ctrl_ajax::AJAX_NAME => true,
        DUPX_Ctrl_ajax::ACTION_NAME => DUPX_Ctrl_ajax::ACTION_DAWN,
        DUPX_Ctrl_ajax::TOKEN_NAME => DUPX_Ctrl_ajax::generateToken(DUPX_Ctrl_ajax::ACTION_DAWN)
    )); ?>;
            
	request.action = "start_expand";
	request.archive_filepath = '<?php echo DUPX_Security::getInstance()->getArchivePath(); ?>';
	request.restore_directory = '<?php echo $GLOBALS['DUPX_ROOT']; ?>';
	request.worker_time = DUPX.DAWS.KickoffWorkerTimeInSec;
	request.client_driven = isClientSideKickoff ? 1 : 0;
	request.throttle_delay = DUPX.throttleDelay;
	request.filtered_directories = ['dup-installer'];

	if (!isClientSideKickoff) {
		console.log('kickOffDupArchiveExtract:Setting timer');
		// If server is driving things we need to poll the status
		DUPX.dupArchiveStatusIntervalID = setInterval(DUPX.pingDAWS, DUPX.DAWS.StatusPeriodInMS);
	}
	else {
		console.log('kickOffDupArchiveExtract:client side kickoff');
	}

	console.log("daws url=" + DUPX.DAWS.Url);

	$.ajax({
		type: "POST",
		timeout: DUPX.DAWS.KickoffWorkerTimeInSec * 2000,  // Double worker time and convert to ms
		url: DUPX.DAWS.Url,
		data: request,
		beforeSend: function () {
			DUPX.showProgressBar();
			$form.hide();
			$('#s1-result-form').show();
			DUPX.updateProgressPercent(0);
		},
		success: function (respData, textStatus, xHr) {
            try {
                var data = DUPX.parseJSON(respData);
            } catch(err) {
                console.error(err);
                console.error('JSON parse failed for response data: ' + respData);
                console.log('kickOffDupArchiveExtract:AJAX error. textStatus=', textStatus);
			    DUPX.handleDAWSCommunicationProblem(xHr, false, textStatus);
                return false;
            }

			console.log('kickOffDupArchiveExtract:success');
			if (typeof (data) != 'undefined' && data.pass == 1) {

				var criticalFailureText = DUPX.getCriticalFailureText(status.failures);

				if (criticalFailureText === null) {

					var dataJSON = JSON.stringify(data);

					//RSR TODO:Need to check only for FATAL errors right now - have similar failure check as in pingdaws
					DUPX.DAWS.FailureCount = 0;
					console.log("kickOffDupArchiveExtract:Resetting failure count");

					$("#ajax-json-debug").val(dataJSON);
					if (typeof (data) != 'undefined' && data.pass == 1) {

						if (isClientSideKickoff) {
							console.log('kickOffDupArchiveExtract:Initial ping DAWS in 500');
							setTimeout(DUPX.pingDAWS, 500);
						}

					} else {
                        console.log("kickOffDupArchiveExtract: success but data problem, data:" , data);
						$('#ajaxerr-data').html('Error Processing Step 1');
						DUPX.hideProgressBar();
					}
				} else {
					// If we get a critical failure it means it's something we can't recover from so no purpose in retrying, just fail immediately.
                    console.log("kickOffDupArchiveExtract: success but data problem, data:" , data);
					var errorString = 'kickOffDupArchiveExtract:Error Processing Step 1<br/>';
					errorString += criticalFailureText;
					DUPX.DAWSProcessingFailed(errorString);
				}
			} else {
                console.log("kickOffDupArchiveExtract: success but data problem, data:" , data);
				var errorString = 'kickOffDupArchiveExtract:Error Processing Step 1<br/>';
				errorString += data.error;
				DUPX.handleDAWSProcessingProblem(errorString, false);
			}
		},
		error: function (xHr, textStatus) {

			console.log('kickOffDupArchiveExtract:AJAX error. textStatus=', textStatus);
			DUPX.handleDAWSCommunicationProblem(xHr, false, textStatus);
		}
	});
};

DUPX.finalizeDupArchiveExtraction = function(dawsStatus)
{
	console.log("finalizeDupArchiveExtraction:start");
	var $form = $('#s1-input-form');
	$("#s1-input-dawn-status").val(JSON.stringify(dawsStatus));
	console.log("finalizeDupArchiveExtraction:after stringify dawsstatus");
	var formData = $form.serialize();

	$.ajax({
		type: "POST",
		timeout: 30000,
		url: window.location.href,
		data: formData,
		beforeSend: function () {

		},
		success: function (respData, textStatus, xHr) {
            try {
                var data = DUPX.parseJSON(respData);
            } catch(err) {
                console.error(err);
                console.error('JSON parse failed for response data: ' + respData);
                console.log("finalizeDupArchiveExtraction:error");
                console.log(xHr.statusText);
                console.log(xHr.getAllResponseHeaders());
                console.log(xHr.responseText);
                return false;
            }
			console.log("finalizeDupArchiveExtraction:success");
		},
		error: function (xHr) {
			console.log("finalizeDupArchiveExtraction:error");
			console.log(xHr.statusText);
			console.log(xHr.getAllResponseHeaders());
			console.log(xHr.responseText);
		}
	});
};

/**
 * Performs Ajax post to either do a zip or manual extract and then create db
 */
DUPX.runStandardExtraction = function ()
{
	var $form = $('#s1-input-form');

	//1800000 = 30 minutes
	//If the extraction takes longer than 30 minutes then user
	//will probably want to do a manual extraction or even FTP
	$.ajax({
		type: "POST",
		timeout: 1800000,
		url: window.location.href,
		data: $form.serialize(),
		beforeSend: function () {
			DUPX.showProgressBar();
			$form.hide();
			$('#s1-result-form').show();
		},
		success: function (respData, textStatus, xHr) {
            $("#ajax-json-debug").val(respData);
            var dataJSON = respData;
            try {
                var data = DUPX.parseJSON(respData);
            } catch(err) {
                console.error(err);
                console.error('JSON parse failed for response data: ' + respData);
                DUPX.ajaxCommunicationFailed(xHr, textStatus, 'extract');
                return false;
            }
			if (typeof (data) != 'undefined' && data.pass == 1) {
                $("#ajax-json").val(escape(dataJSON));
                
				<?php if (!$GLOBALS['DUPX_DEBUG']) : ?>
					setTimeout(function () {$('#s1-result-form').submit();}, 500);
				<?php endif; ?>
				$('#progress-area').fadeOut(1000);
			} else {
                console.log('runStandardExtraction: success but data return problem', data);
				$('#ajaxerr-data').html('Error Processing Step 1');
				DUPX.hideProgressBar();
			}
		},
		error: function (xHr, textStatus) {
			DUPX.ajaxCommunicationFailed(xHr, textStatus, 'extract');
		}
	});
};

DUPX.runChunkedExtraction = function (data)
{
    var $form = $('#s1-input-form');
    var dataToSend;
    var chunkData;

    console.log('runChunkedExtraction called.');

    if(typeof (data) == 'undefined'){
        $("#progress-pct").text("");
        $("#secondary-progress-text").text("");
        chunkData = {
            archive_offset: 0,
            pass: -1
        };
    }else{
        chunkData = data;
    }

    dataToSend = $form.serialize()+'&'+$.param(chunkData);

    $.ajax({
        type: "POST",
        timeout: 1800000,
        url: window.location.href,
        data: dataToSend,
        beforeSend: function () {
            if(typeof (data) == 'undefined'){
                DUPX.showProgressBar();
                $form.hide();
                $('#s1-result-form').show();
                DUPX.updateProgressPercent(0);
            }
        },
        success: function (respData, textStatus, xHr) {
            if(typeof (respData) != 'undefined'){
                var dataJSON = respData;
                $("#ajax-json-debug").val(respData);
                try {
                    var data = DUPX.parseJSON(respData);
                } catch(err) {
                    console.error(err);
                    console.error('JSON parse failed for response data: ' + respData);
                    DUPX.ajaxCommunicationFailed(xHr, textStatus, 'extract');
                    return false;
                }
                if (data.pass == 1) {
                    $("#ajax-json").val(escape(dataJSON));
                    <?php if (!$GLOBALS['DUPX_DEBUG']) : ?>
                    setTimeout(function () {
                        $('#s1-result-form').submit();
                    }, 500);
                    <?php endif; ?>
                    $('#progress-area').fadeOut(1000);
                } else if(data.pass == -1){
                    var percent = Math.round((data.archive_offset * 100.0) / data.num_files);
                    $("#progress-notice").html(data.zip_arc_chunk_notice);
                    
                    DUPX.updateProgressPercent(percent);
                    DUPX.updateZipArchiveProgress(data.archive_offset, data.num_files);
                    DUPX.runChunkedExtraction(data);
                } else {
                    console.log('runChunkedExtraction: success but data return problem', data);
                    $('#ajaxerr-data').html('Error Processing Step 1');
                    DUPX.hideProgressBar();
                }
            }
        },
        error: function (xHr, textStatus) {
            DUPX.ajaxCommunicationFailed(xHr, textStatus, 'extract');
        }
    });
};


DUPX.ajaxCommunicationFailed = function (xhr, textStatus, page)
{
	var status = "<b>Server Code:</b> " + xhr.status + "<br/>";
	status += "<b>Status:</b> " + xhr.statusText + "<br/>";
	status += "<b>Response:</b> " + xhr.responseText + "<hr/>";

	if(textStatus && textStatus.toLowerCase() == "timeout" || textStatus.toLowerCase() == "service unavailable") {

		var default_timeout_message = "<b>Recommendation:</b><br/>";
			default_timeout_message += "See <a target='_blank' href='https://snapcreek.com/duplicator/docs/faqs-tech/?180116102141#faq-trouble-100-q'>this FAQ item</a> for possible resolutions.";
			default_timeout_message += "<hr>";
			default_timeout_message += "<b>Additional Resources...</b><br/>";
			default_timeout_message += "With thousands of different permutations it's difficult to try and debug/diagnose a server. If you're running into timeout issues and need help we suggest you follow these steps:<br/><br/>";
			default_timeout_message += "<ol>";
				default_timeout_message += "<li><strong>Contact Host:</strong> Tell your host that you're running into PHP/Web Server timeout issues and ask them if they have any recommendations</li>";
				default_timeout_message += "<li><strong>Dedicated Help:</strong> If you're in a time-crunch we suggest that you contact <a target='_blank' href='https://snapcreek.com/duplicator/docs/faqs-tech/?180116150030#faq-resource-030-q'>professional server administrator</a>. A dedicated resource like this will be able to work with you around the clock to the solve the issue much faster than we can in most cases.</li>";
				default_timeout_message += "<li><strong>Consider Upgrading:</strong> If you're on a budget host then you may run into constraints. If you're running a larger or more complex site it might be worth upgrading to a <a target='_blank' href='https://snapcreek.com/duplicator/docs/faqs-tech/?180116150030#faq-resource-040-q'>managed VPS server</a>. These systems will pretty much give you full control to use the software without constraints and come with excellent support from the hosting company.</li>";
				default_timeout_message += "<li><strong>Contact SnapCreek:</strong> We will try our best to help configure and point users in the right direction, however these types of issues can be time-consuming and can take time from our support staff.</li>";
			default_timeout_message += "</ol>";

		if(page)
		{
			switch(page)
			{
				default:
					status += default_timeout_message;
					break;
				case 'extract':
					status += "<b>Recommendation:</b><br/>";
					status += "See <a target='_blank' href='https://snapcreek.com/duplicator/docs/faqs-tech/#faq-installer-015-q'>this FAQ item</a> for possible resolutions.<br/><br/>";
					break;
				case 'ping':
					status += "<b>Recommendation:</b><br/>";
					status += "See <a target='_blank' href='https://snapcreek.com/duplicator/docs/faqs-tech/?180116152758#faq-trouble-030-q'>this FAQ item</a> for possible resolutions.<br/><br/>";
					break;
                case 'delete-site':
                    status += "<b>Recommendation:</b><br/>";
					status += "See <a target='_blank' href='https://snapcreek.com/duplicator/docs/faqs-tech/?180116153643#faq-installer-120-q'>this FAQ item</a> for possible resolutions.<br/><br/>";
					break;
			}
		}
		else
		{
			status += default_timeout_message;
		}

	}
	else if ((xhr.status == 403) || (xhr.status == 500)) {
		status += "<b>Recommendation:</b><br/>";
		status += "See <a target='_blank' href='https://snapcreek.com/duplicator/docs/faqs-tech/#faq-installer-120-q'>this FAQ item</a> for possible resolutions.<br/><br/>"
	} else if ((xhr.status == 0) || (xhr.status == 200)) {
		status += "<b>Recommendation:</b><br/>";
		status += "Possible server timeout! Performing a 'Manual Extraction' can avoid timeouts.";
		status += "See <a target='_blank' href='https://snapcreek.com/duplicator/docs/faqs-tech/#faq-installer-015-q'>this FAQ item</a> for a complete overview.<br/><br/>"
	} else {
		status += "<b>Additional Resources:</b><br/> ";
		status += "&raquo; <a target='_blank' href='https://snapcreek.com/duplicator/docs/'>Help Resources</a><br/>";
		status += "&raquo; <a target='_blank' href='https://snapcreek.com/duplicator/docs/faqs-tech/'>Technical FAQ</a>";
	}

	$('#ajaxerr-data').html(status);
	DUPX.hideProgressBar();
};

/** Go back on AJAX result view */
DUPX.hideErrorResult = function ()
{
	$('#s1-result-form').hide();
	$('#s1-input-form').show(200);
}

/**
 * show next button */
DUPX.showHideNextBtn = function (evtSrc)
{
    var target = $(".footer-buttons");
    if (evtSrc.checked) {
        target.slideDown();
    } else {
        target.slideUp();
    }
};

/**
 * Accetps Usage Warning */
DUPX.acceptWarning = function ()
{
	if ($("#<?php echo $paramsManager->getFormItemId(DUPX_Paramas_Manager::PARAM_ACCEPT_TERM_COND); ?>").is(':checked')) {
		$("#s1-deploy-btn").removeAttr("disabled");
		$("#s1-deploy-btn").removeAttr("title");
	} else {
		$("#s1-deploy-btn").attr("disabled", "true");
		$("#s1-deploy-btn").attr("title", "<?php echo $agree_msg; ?>");
	}
};

DUPX.onSafeModeSwitch = function ()
{
    var safeObj = $('#' + exeSafeModeInputId)
    var mode = safeObj ? parseInt(safeObj.val()) : 0;
    var htWr = $('#' + htConfigWrapperId);
    var otWr = $('#' + otConfigWrapperId);

    switch (mode) {
        case 1:
        case 2:
            htWr.find('#' + htConfigInputId + '_0').prop("checked", true);
            htWr.find('input').prop("disabled", true);
            otWr.find('#' + otConfigInputId + '_0').prop("checked", true);
            otWr.find('input').prop("disabled", true);
            break;
        case 0:
        default:
            htWr.find('input').prop("disabled", false);
            otWr.find('input').prop("disabled", false);
            break;
    }
    console.log("mode set to"+mode);
};
//DOCUMENT LOAD
$(document).ready(function ()
{
	DUPX.DAWS = new Object();
	DUPX.DAWS.Url = window.location.href; // + '?is_daws=1&<?php echo DUPX_Security::DAWN_TOKEN; ?>=<?php echo urlencode(DUPX_CSRF::generate('daws'));?>';
	DUPX.DAWS.StatusPeriodInMS = 10000;
	DUPX.DAWS.PingWorkerTimeInSec = 9;
	DUPX.DAWS.KickoffWorkerTimeInSec = 6; // Want the initial progress % to come back quicker

    DUPX.DAWS.MaxRetries = 10;
	DUPX.DAWS.RetryDelayInMs = 8000;

	DUPX.dupArchiveStatusIntervalID = -1;
	DUPX.DAWS.FailureCount = 0;
	DUPX.throttleDelay = 0;

	//INIT Routines
	$("*[data-type='toggle']").click(DUPX.toggleClick);
	$("#tabs").tabs();
	DUPX.acceptWarning();
	DUPX.toggleSetupType();

	<?php echo ($arcCheck == 'Fail') ? "$('#s1-area-archive-file-link').trigger('click');" : ""; ?>
	<?php echo (!$all_success) ? "$('#s1-area-sys-setup-link').trigger('click');" : ""; ?>
});
</script>
