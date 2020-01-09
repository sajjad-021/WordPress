<?php
/*
 * Duplicator Website Installer
 * Copyright (C) 2018, Snap Creek LLC
 * website: snapcreek.com
 *
 * Duplicator (Pro) Plugin is distributed under the GNU General Public License, Version 3,
 * June 2007. Copyright (C) 2007 Free Software Foundation, Inc., 51 Franklin
 * St, Fifth Floor, Boston, MA 02110, USA
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
 * ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
 * ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */
if (!defined('DUPXABSPATH')) {
    define('DUPXABSPATH', dirname(__FILE__));
}

try {
    $GLOBALS['DUPX_ROOT']     = str_replace("\\", '/', (realpath(dirname(dirname(__FILE__)))));
    $GLOBALS['DUPX_ROOT_URL'] = rtrim("http".(!empty($_SERVER['HTTPS']) ? "s" : "")."://".$_SERVER['SERVER_NAME'].dirname(dirname($_SERVER['PHP_SELF'])), '/');
    $GLOBALS['DUPX_INIT']     = $GLOBALS['DUPX_ROOT'].'/dup-installer';
    $GLOBALS['DUPX_INIT_URL'] = $GLOBALS['DUPX_ROOT_URL'].'/dup-installer';

    require_once($GLOBALS['DUPX_INIT'].'/classes/config/class.boot.php');

    /**
     * init constants and include
     */
    DUPX_Boot::init();
    DUPX_Log::setThrowExceptionOnError(true);
    DUPX_Log::logTime('INIT END', DUPX_Log::LV_DETAILED);

    // if is ajax always die in controller
    DUPX_Ctrl_ajax::controller();
}
catch (Exception $ex) {
    DUPX_Log::logException($ex, DUPX_Log::LV_DEFAULT, 'EXCEPTION ON INIT: ');
    ob_start();
    ?>
    <div>
        <h1>DUPLICATOR PRO: ISSUE</h1>
        Problem on duplicator init.<br>
        Message: <b><?php echo htmlspecialchars($ex->getMessage()); ?></b>
    </div>
    <?php
    $content = ob_get_clean();
    DUPX_Boot::problemLayout($content);
    die();
}

ob_start();
try {
    $exceptionError = false;
    // DUPX_log::error thotw an exception
    DUPX_Log::setThrowExceptionOnError(true);
    DUPX_Log::logTime('CONTROLLER START', DUPX_Log::LV_DETAILED);

    $paramsManager   = DUPX_Paramas_Manager::getInstance();
    $paramView       = $paramsManager->getValue(DUPX_Paramas_Manager::PARAM_VIEW);
    $paramCtrlAction = $paramsManager->getValue(DUPX_Paramas_Manager::PARAM_CTRL_ACTION);

    if (!empty($paramCtrlAction)) {
        DUPX_Log::info("\n".'---------------', DUPX_Log::LV_DETAILED);
        DUPX_Log::info('CONTROLLER: '.DUPX_Log::varToString($paramCtrlAction), DUPX_Log::LV_DETAILED);
        DUPX_Log::info('---------------'."\n", DUPX_Log::LV_DETAILED);

        switch ($paramCtrlAction) {
            case "ctrl-step0" :
                DUPX_Ctrl_Params::setParamsStep0();
                DUPX_Log::logTime('CONTROLLER PARAMS READ', DUPX_Log::LV_DETAILED);
                DUPX_U::maintenanceMode(true, $GLOBALS['DUPX_ROOT']);
                require_once($GLOBALS['DUPX_INIT'].'/ctrls/ctrl.s0.php');

                if ($GLOBALS['DUPX_AC']->secure_on) {
                    $password = $paramsManager->getValue(DUPX_Paramas_Manager::PARAM_SECURE_PASS);
                    if (!DUPX_Security::passwordArciveCheck($password)) {
                        $paramView = 'secure';
                    } else {
                        $paramView = 'step1';
                    }
                } else {
                    $paramView = 'step1';
                }
                break;
            case "ctrl-step1" :
                DUPX_Ctrl_Params::setParamsStep1();
                DUPX_Log::logTime('CONTROLLER PARAMS READ', DUPX_Log::LV_DETAILED);
                require_once($GLOBALS['DUPX_INIT'].'/ctrls/ctrl.s1.php');
                break;
            case "ctrl-step2" :
                DUPX_Ctrl_Params::setParamsStep2();
                DUPX_Log::logTime('CONTROLLER PARAMS READ', DUPX_Log::LV_DETAILED);
                require_once($GLOBALS['DUPX_INIT'].'/ctrls/ctrl.s2.dbinstall.php');
                require_once($GLOBALS['DUPX_INIT'].'/ctrls/ctrl.s2.base.php');
                break;
            case "ctrl-step3" :
                DUPX_Ctrl_Params::setParamsStep3();
                DUPX_Log::logTime('CONTROLLER PARAMS READ', DUPX_Log::LV_DETAILED);
                require_once($GLOBALS['DUPX_INIT'].'/ctrls/ctrl.s3.php');
                break;
            default:
                DUPX_Log::error('No valid action request '.$paramCtrlAction);
        }
    }
}
catch (Exception $e) {
    $exceptionError = $e;
}

/**
 * clean output
 */
$unespectOutput = trim(ob_get_contents());
ob_end_clean();
if (!empty($unespectOutput)) {
    DUPX_Log::info('ERROR: Unespect output '.DUPX_Log::varToString($unespectOutput));
    $exceptionError = new Exception('Unespected output '.DUPX_Log::varToString($unespectOutput));
}

$bodyClasses = '';
if ($paramsManager->getValue(DUPX_Paramas_Manager::PARAM_DEBUG_PARAMS)) {
    $bodyClasses = 'debug-params';
}

DUPX_Log::logTime('VIEW START', DUPX_Log::LV_DETAILED);
?><!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="robots" content="noindex,nofollow">
        <title>Duplicator Professional</title>

        <link rel="apple-touch-icon" sizes="180x180" href="favicon/pro01_apple-touch-icon.png">
        <link rel="icon" type="image/png" sizes="32x32" href="favicon/pro01_favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="favicon/pro01_favicon-16x16.png">
        <link rel="manifest" href="favicon/site.webmanifest">
        <link rel="mask-icon" href="favicon/pro01_safari-pinned-tab.svg" color="#5bbad5">
        <link rel="shortcut icon" href="favicon/pro01_favicon.ico">
        <meta name="msapplication-TileColor" content="#00aba9">
        <meta name="msapplication-config" content="favicon/browserconfig.xml">
        <meta name="theme-color" content="#ffffff">

        <link rel='stylesheet' href='assets/normalize.css' type='text/css' media='all' />
        <link rel='stylesheet' href='assets/font-awesome/css/all.min.css' type='text/css' media='all' />
        <?php
        require_once($GLOBALS['DUPX_INIT'].'/assets/inc.libs.css.php');
        require_once($GLOBALS['DUPX_INIT'].'/assets/inc.css.php');
        require_once($GLOBALS['DUPX_INIT'].'/assets/inc.libs.js.php');
        require_once($GLOBALS['DUPX_INIT'].'/assets/inc.js.php');
        ?>
    </head>
    <body id="body-<?php echo $paramView; ?>" class="<?php echo $bodyClasses; ?>" >

        <div id="content">
            <!-- HEADER TEMPLATE: Common header on all steps -->
            <table cellspacing="0" class="header-wizard">
                <tr>
                    <td style="width:100%;">
                        <div class="dupx-branding-header">
                            <?php if (isset($GLOBALS['DUPX_AC']->brand) && isset($GLOBALS['DUPX_AC']->brand->logo) && !empty($GLOBALS['DUPX_AC']->brand->logo)) : ?>
                                <?php echo $GLOBALS['DUPX_AC']->brand->logo; ?>
                            <?php else: ?>
                                <i class="fa fa-bolt fa-sm"></i> Duplicator Pro <?php echo ($paramView === 'help') ? 'help' : ''; ?>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td class="wiz-dupx-version">
                        <?php if ($paramView !== 'help') { ?>
                            <a href="javascript:void(0)" onclick="DUPX.openServerDetails()">version:<?php echo $GLOBALS['DUPX_AC']->version_dup; ?></a>&nbsp;
                            <?php DUPX_View_Funcs::helpLockLink(); ?>
                            <div style="padding: 6px 0">
                                <?php DUPX_View_Funcs::helpLink($paramView); ?>
                            </div>
                        <?php } ?>
                    </td>
                </tr>
            </table>
            <?php
            /*             * ************************* */
            /*             * * NOTICE MANAGER TESTS ** */
//DUPX_NOTICE_MANAGER::testNextStepFullMessageData();
//DUPX_NOTICE_MANAGER::testNextStepMessaesLevels();
//DUPX_NOTICE_MANAGER::testFinalReporMessaesLevels();
//DUPX_NOTICE_MANAGER::testFinalReportFullMessages();
            /*             * ************************* */

            DUPX_NOTICE_MANAGER::getInstance()->nextStepLog();
// display and remove next step notices
            DUPX_NOTICE_MANAGER::getInstance()->displayStepMessages();
            ?>
            <!-- =========================================
            FORM DATA: User-Interface views -->
            <div id="content-inner">
                <?php
                if ($exceptionError === false) {
                    try {
                        ob_start();
                        switch ($paramView) {
                            case "secure" :
                                DUPX_Log::logTime('VIEW SECURE START', DUPX_Log::LV_DETAILED);
                                require_once($GLOBALS['DUPX_INIT'].'/views/view.init1.php');
                                break;
                            case "step1" :
                                DUPX_Log::logTime('VIEW STEP 1 START', DUPX_Log::LV_DETAILED);
                                require_once($GLOBALS['DUPX_INIT'].'/views/view.s1.base.php');
                                break;
                            case "step2" :
                                DUPX_Log::logTime('VIEW STEP 2 START', DUPX_Log::LV_DETAILED);
                                require_once($GLOBALS['DUPX_INIT'].'/views/view.s2.base.php');
                                break;
                            case "step3" :
                                DUPX_Log::logTime('VIEW STEP 3 START', DUPX_Log::LV_DETAILED);
                                require_once($GLOBALS['DUPX_INIT'].'/views/view.s3.php');
                                break;
                            case "step4" :
                                DUPX_Log::logTime('VIEW STEP 4 START', DUPX_Log::LV_DETAILED);
                                DUPX_U::maintenanceMode(false, $GLOBALS['DUPX_ROOT']);
                                require_once($GLOBALS['DUPX_INIT'].'/views/view.s4.php');
                                break;
                            case "help" :
                                DUPX_Log::logTime('VIEW HELP START', DUPX_Log::LV_DETAILED);
                                require_once($GLOBALS['DUPX_INIT'].'/views/view.help.php');
                                break;
                            default :
                                echo "Invalid View Requested";
                        }
                    }
                    catch (Exception $e) {
                        /** delete view broken output * */
                        ob_clean();
                        $exceptionError = $e;
                    }

                    /** flush view output * */
                    ob_end_flush();
                }

                if ($exceptionError !== false) {
                    DUPX_Log::info("--------------------------------------");
                    DUPX_Log::info('EXCEPTION: '.$exceptionError->getMessage());
                    DUPX_Log::info('TRACE:');
                    DUPX_Log::info($exceptionError->getTraceAsString());
                    DUPX_Log::info("--------------------------------------");
                    /**
                     *   $exceptionError call in view
                     */
                    require_once($GLOBALS['DUPX_INIT'].'/views/view.exception.php');
                }
                DUPX_Log::logTime('VIEW INNER END', DUPX_Log::LV_DETAILED);
                ?>
            </div>
        </div>

        <?php $paramsManager->getParamsHtmlInfo(); ?>

        <!-- SERVER INFO DIALOG -->
        <div id="dialog-server-details" title="Setup Information" style="display:none">
            <!-- DETAILS -->
            <div class="dlg-serv-info">
                <?php
                $ini_path       = php_ini_loaded_file();
                $ini_max_time   = ini_get('max_execution_time');
                $ini_memory     = ini_get('memory_limit');
                $ini_error_path = ini_get('error_log');
                ?>
                <div class="hdr">SERVER DETAILS</div>
                <label>Try CDN Request:</label> 		<?php echo ( DUPX_U::tryCDN("ajax.aspnetcdn.com", 443) && DUPX_U::tryCDN("ajax.googleapis.com", 443)) ? 'Yes' : 'No'; ?> <br/>
                <label>Web Server:</label>  			<?php echo DUPX_U::esc_html($_SERVER['SERVER_SOFTWARE']); ?><br/>
                <label>PHP Version:</label>  			<?php echo DUPX_U::esc_html(DUPX_Server::$php_version); ?><br/>
                <label>PHP INI Path:</label> 			<?php echo empty($ini_path) ? 'Unable to detect loaded php.ini file' : DUPX_U::esc_html($ini_path); ?>	<br/>
                <label>PHP SAPI:</label>  				<?php echo DUPX_U::esc_html(php_sapi_name()); ?><br/>
                <label>PHP ZIP Archive:</label> 		<?php echo class_exists('ZipArchive') ? 'Is Installed' : 'Not Installed'; ?> <br/>
                <label>PHP max_execution_time:</label>  <?php echo $ini_max_time === false ? 'unable to find' : DUPX_U::esc_html($ini_max_time); ?><br/>
                <label>PHP memory_limit:</label>  		<?php echo empty($ini_memory) ? 'unable to find' : DUPX_U::esc_html($ini_memory); ?><br/>
                <label>Error Log Path:</label>  		<?php echo empty($ini_error_path) ? 'unable to find' : DUPX_U::esc_html($ini_error_path); ?><br/>

                <br/>
                <div class="hdr">PACKAGE BUILD DETAILS</div>
                <label>Plugin Version:</label>  		<?php echo DUPX_U::esc_html($GLOBALS['DUPX_AC']->version_dup); ?><br/>
                <label>WordPress Version:</label>  		<?php echo DUPX_U::esc_html($GLOBALS['DUPX_AC']->version_wp); ?><br/>
                <label>PHP Version:</label>             <?php echo DUPX_U::esc_html($GLOBALS['DUPX_AC']->version_php); ?><br/>
                <label>Database Version:</label>        <?php echo DUPX_U::esc_html($GLOBALS['DUPX_AC']->version_db); ?><br/>
                <label>Operating System:</label>        <?php echo DUPX_U::esc_html($GLOBALS['DUPX_AC']->version_os); ?><br/>

            </div>
        </div>

        <script>
            DUPX.openServerDetails = function ()
            {
                $("#dialog-server-details").dialog({
                    resizable: false,
                    height: "auto",
                    width: 700,
                    modal: true,
                    position: {my: 'top', at: 'top+150'},
                    buttons: {"OK": function () {
                            $(this).dialog("close");
                        }}
                });
            }

            $(document).ready(function ()
            {
                //Disable href for toggle types
                $("a[data-type='toggle']").each(function () {
                    $(this).attr('href', 'javascript:void(0)');
                });

            });
        </script>

        <?php if ($GLOBALS['DUPX_DEBUG']) : ?>
            <form id="form-debug" method="post" action="?debug=1">
                <?php
                // @todo why ??????????  <input id="debug-view" type="hidden" name="view" /> 
                // @todo remove this ... don't work for security reasons'
                ?>
                <br/><hr size="1" />
                DEBUG MODE ON: <a href="//<?php echo $GLOBALS['_CURRENT_URL_PATH'] ?>/api/router.php" target="api">[API]</a> &nbsp;
                <br/><br/>
                <a href="javascript:void(0)"  onclick="$('#debug-vars').toggle()"><b>PAGE VARIABLES</b></a>
                <pre id="debug-vars"><?php print_r($GLOBALS); ?></pre>
            </form>

            <script>
                DUPX.debugNavigate = function (view)
                {
                    //TODO: Write app that captures all ajax requets and logs them to custom console.
                }
            </script>
        <?php endif; ?>

        <!-- Used for integrity check do not remove:
        DUPLICATOR_PRO_INSTALLER_EOF -->
    </body>
</html>
<?php
DUPX_Log::logTime('VIEW END', DUPX_Log::LV_DETAILED);
