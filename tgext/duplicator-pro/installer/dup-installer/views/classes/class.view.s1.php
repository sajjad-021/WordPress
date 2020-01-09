<?php
/**
 * 
 * Standard: PSR-2
 * @link http://www.php-fig.org/psr/psr-2 Full Documentation
 *
 * @package SC\DUPX
 *
 */
defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/**
 * View s3 functions
 */
class DUPX_View_S1
{

    public static function infoTabs()
    {
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

        $hostManager = DUPX_Custom_Host_Manager::getInstance();

        $opened = $hostManager->isManaged();
        ?>
        <div class="hdr-sub1 toggle-hdr <?php echo $opened ? 'close' : 'open'; ?>" data-type="toggle" data-target="#s1-area-archive-file">
            <a id="s1-area-archive-file-link"><i class="fa fa-plus-square"></i>Info</a>
            <div class="<?php echo ($arcCheck == 'Pass') ? 'status-badge status-badge-pass' : 'status-badge status-badge-fail'; ?>">
                <?php echo $arcCheck; ?>
            </div>
        </div>
        <div id="s1-area-archive-file" class="hdr-sub1-area tabs-area <?php echo $opened ? '' : 'no-display'; ?>" >
            <div id="tabs">
                <ul>
                    <?php if ($hostManager->isManaged()) { ?>
                        <li><a href="#tabs-2" >Managed Hosting</a></li>
                    <?php } ?>
                    <li><a href="#tabs-1">Archive</a></li>

                </ul>
                <?php
                self::managedInfoTab();
                self::archiveInfoTab();
                ?>
            </div>
        </div>
        <?php
    }

    protected static function archiveInfoTab()
    {
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
        ?>
        <div id="tabs-1">
            <table class="s1-archive-local">
                <tr>
                    <td colspan="2"><div class="hdr-sub3">Site Details</div></td>
                </tr>
                <tr>
                    <td>Site:</td>
                    <td><?php echo DUPX_U::esc_html($GLOBALS['DUPX_AC']->blogname); ?> </td>
                </tr>
                <tr>
                    <td>Notes:</td>
                    <td><?php echo strlen($GLOBALS['DUPX_AC']->package_notes) ? "{$GLOBALS['DUPX_AC']->package_notes}" : " - no notes - "; ?></td>
                </tr>
                <?php if ($GLOBALS['DUPX_AC']->exportOnlyDB) : ?>
                    <tr>
                        <td>Mode:</td>
                        <td>Archive only database was enabled during package package creation.</td>
                    </tr>
                <?php endif; ?>
            </table>

            <table class="s1-archive-local">
                <tr>
                    <td colspan="2"><div class="hdr-sub3">File Details</div></td>
                </tr>
                <tr>
                    <td>Size:</td>
                    <td><?php echo DUPX_U::readableByteSize(DUPX_Conf_Utils::archiveSize()); ?> </td>
                </tr>
                <tr>
                    <td>Path:</td>
                    <td><?php echo $GLOBALS['DUPX_ROOT']; ?> </td>
                </tr>
                <tr>
                    <td>Archive:</td>
                    <td><?php echo DUPX_ArchiveConfig::getInstance()->package_name; ?> </td>
                </tr>
                <tr>
                    <td style="vertical-align:top">Status:</td>
                    <td>
                        <?php if ($arcCheck == 'Fail' || $arcCheck == 'Warn') : ?>
                            <span class="dupx-fail" style="font-style:italic">
                                <?php
                                if ($arcCheck == 'Warn') {
                                    ?>
                                    The archive file named above must be the <u>exact</u> name of the archive file placed in the root path (character for character). But you can proceed with choosing Manual Archive Extraction.
                                    <?php
                                } else {
                                    ?>
                                    The archive file named above must be the <u>exact</u> name of the archive file placed in the root path (character for character).
                                    When downloading the package files make sure both files are from the same package line.  <br/><br/>

                                    If the contents of the archive were manually transferred to this location without the archive file then simply create a temp file named with
                                    the exact name shown above and place the file in the same directory as the installer.php file.  The temp file will not need to contain any data.
                                    Afterward, refresh this page and continue with the install process.
                                    <?php
                                }
                                ?>
                            </span>
                        <?php else : ?>
                            <span class="dupx-pass">Archive file successfully detected.</span>                                
                        <?php endif; ?>
                    </td>
                </tr>
            </table>

        </div>
        <?php
    }

    protected static function managedInfoTab()
    {
        $hostManager = DUPX_Custom_Host_Manager::getInstance();
        if (($identifier  = $hostManager->isManaged()) === false) {
            return;
        }
        $hostObj = $hostManager->getHosting($identifier);
        ?>
        <div id="tabs-2">
            <h3><b><?php echo $hostObj->getLabel(); ?></b> managed hosting detected</h3>
            <p>
                The installation is occurring on a WordPress managed host. Managed hosts are more restrictive than standard shared hosts so some installer settings cannot be changed. These settings include new path, new URL, database connection data, and wp-config settings.
            </p>
        </div>
        <?php
    }

    public static function generalOptions()
    {
        $paramsManager = DUPX_Paramas_Manager::getInstance();
        ?>
        <div class="hdr-sub3">General</div>  

        <div class="dupx-opts dupx-advopts">
            <?php
            $paramsManager->getHtmlFormParam(DUPX_Paramas_Manager::PARAM_ARCHIVE_ENGINE);
            if (!DupProSnapLibOSU::isWindows()) {
                ?>
                <div class="param-wrapper" >
                    <?php $paramsManager->getHtmlFormParam(DUPX_Paramas_Manager::PARAM_SET_FILE_PERMS); ?>
                    &nbsp;
                    <?php $paramsManager->getHtmlFormParam(DUPX_Paramas_Manager::PARAM_FILE_PERMS_VALUE); ?>
                </div>
                <div class="param-wrapper" >
                    <?php $paramsManager->getHtmlFormParam(DUPX_Paramas_Manager::PARAM_SET_DIR_PERMS); ?>
                    &nbsp;
                    <?php $paramsManager->getHtmlFormParam(DUPX_Paramas_Manager::PARAM_DIR_PERMS_VALUE); ?>
                </div>
            <?php } ?>
        </div>
        <?php
    }

    public static function advancedOptions()
    {
        $archive_config = DUPX_ArchiveConfig::getInstance();
        $paramsManager  = DUPX_Paramas_Manager::getInstance();
        ?>
        <div class="hdr-sub3">Advanced</div>

        <div class="dupx-opts dupx-advopts">
            <?php
            $paramsManager->getHtmlFormParam(DUPX_Paramas_Manager::PARAM_SAFE_MODE);
            $paramsManager->getHtmlFormParam(DUPX_Paramas_Manager::PARAM_WP_CONFIG);
            $paramsManager->getHtmlFormParam(DUPX_Paramas_Manager::PARAM_HTACCESS_CONFIG);
            $paramsManager->getHtmlFormParam(DUPX_Paramas_Manager::PARAM_OTHER_CONFIG);
            $paramsManager->getHtmlFormParam(DUPX_Paramas_Manager::PARAM_FILE_TIME);
            $paramsManager->getHtmlFormParam(DUPX_Paramas_Manager::PARAM_LOGGING);
            if (!$archive_config->isZipArchive()) {
                $paramsManager->getHtmlFormParam(DUPX_Paramas_Manager::PARAM_CLIENT_KICKOFF);
            }
            if (!$GLOBALS['DUPX_AC']->exportOnlyDB) {
                $paramsManager->getHtmlFormParam(DUPX_Paramas_Manager::PARAM_REMOVE_RENDUNDANT);
            }
            ?>
        </div>
        <?php
    }

    public static function multisiteOptions()
    {
        if (!DUPX_Conf_Utils::showMultisite()) {
            return;
        }
        $archive_config = DUPX_ArchiveConfig::getInstance();
        $paramsManager  = DUPX_Paramas_Manager::getInstance();
        ?>
        <div class="hdr-sub1 toggle-hdr close" data-type="toggle" data-target="#s1-multisite">
            <a href="javascript:void(0)"><i class="fa fa-minus-square"></i>Multisite</a>
        </div>
        <div id="s1-multisite" class="hdr-sub1-area dupx-opts" >
            <?php
            $paramsManager->getHtmlFormParam(DUPX_Paramas_Manager::PARAM_MULTISITE_INST_TYPE);
            $paramsManager->getHtmlFormParam(DUPX_Paramas_Manager::PARAM_SUBSITE_ID);
            ?>
        </div>
        <?php
    }

    public static function acceptAndContinue()
    {
        $paramsManager = DUPX_Paramas_Manager::getInstance();
        include ('view.s1.terms.php');
        $paramsManager->getHtmlFormParam(DUPX_Paramas_Manager::PARAM_ACCEPT_TERM_COND);
    }
}