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
class DUPX_View_S3
{

    public static function newSettings()
    {
        $paramsManager  = DUPX_Paramas_Manager::getInstance();
        $archive_config = DUPX_ArchiveConfig::getInstance();
        ?>
        <div class="hdr-sub1 toggle-hdr close" data-type="toggle" data-target="#s3-new-settings">
            <a href="javascript:void(0)"><i class="fa fa-minus-square"></i>New Settings</a>
        </div>
        <div id="s3-new-settings" class="hdr-sub1-area">
            <div class="dupx-opts s3-opts">
                <?php
                $paramsManager->getHtmlFormParam(DUPX_Paramas_Manager::PARAM_URL_NEW);
                $paramsManager->getHtmlFormParam(DUPX_Paramas_Manager::PARAM_PATH_NEW);
                $paramsManager->getHtmlFormParam(DUPX_Paramas_Manager::PARAM_BLOGNAME, $archive_config->getBlognameFromSelectedSubsiteId());
                if ($archive_config->isNetworkInstall()) {
                    $paramsManager->getHtmlFormParam(DUPX_Paramas_Manager::PARAM_REPLACE_MODE);
                }
                ?>
            </div>
        </div>
        <?php
    }

    public static function mappingMode()
    {
        $archive_config = DUPX_ArchiveConfig::getInstance();
        $paramsManager  = DUPX_Paramas_Manager::getInstance();
        if (!$archive_config->isNetworkInstall()) {
            return;
        }
        $is_subdomain = ($archive_config->mu_mode === 1);

        $subsites = $GLOBALS['DUPX_AC']->subsites;
        if (!$is_subdomain) {
            $subsites = DUPX_U::urlForSubdirectoryMode($subsites, $GLOBALS['DUPX_AC']->url_old);
        }
        $subsites = DUPX_U::appendProtocol($subsites);
        $main_url = $subsites[0]->name;

        DUPX_U::urlForSubdirectoryMode($subsites, $GLOBALS['DUPX_AC']->url_old);
        ?>
        <div id="subsite-map-container" class="<?php echo $paramsManager->getValue(DUPX_Paramas_Manager::PARAM_REPLACE_MODE) == 'mapping' ? '' : 'no-display'; ?>">
            <div class="hdr-sub1 toggle-hdr close" data-type="toggle" data-target="#s3-subsite-mapping">
                <a href="javascript:void(0)"><i class="fa fa-minus-square"></i>Subsite Mapping</a>
            </div>
            <div id="s3-subsite-mapping" class="hdr-sub1-area">
                <table class="s3-opts">
                    <tr>
                        <td>URLs:</td>
                        <td>
                            <?php
                            foreach ($subsites as $subsite) {
                                $isMainSite = ($subsite->id == $GLOBALS['DUPX_AC']->main_site_id);
                                $title      = ($isMainSite ? 'Main' : 'Sub').' site: '.$subsite->blogname;
                                ?>
                                <div class="site-item <?php echo $isMainSite ? 'main_site' : 'sub_site'; ?>" title="<?php echo DUPX_U::esc_attr($title); ?>" >
                                    <span class="from-input-wrapper" >
                                        <input type="text"
                                               name="mu_search[<?php echo intval($subsite->id); ?>]"
                                               id="url_old_<?php echo intval($subsite->id); ?>"
                                               value="<?php echo $subsite->name ?>"
                                               readonly="readonly"
                                               class="mu_search readonly"
                                               />
                                    </span><span class="to-label-wrapper">
                                        to
                                    </span><span class="to-input-wrapper" >
                                        <?php
                                        $url_new    = DUPX_U::getDefaultURL($subsite->name, $main_url, $is_subdomain);
                                        ?>
                                        <input name="mu_replace[<?php echo intval($subsite->id); ?>]"
                                               id="url_new_<?php echo intval($subsite->id); ?>"
                                               value="<?php echo DUPX_U::esc_attr($url_new); ?>"
                                               class="mu_replace <?php echo $isMainSite ? ' sync_url_new' : ''; ?>"
                                               />
                                    </span>
                                </div>
                            <?php } ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        <?php
    }

    public static function customSearchAndReaplce()
    {
        ?>
        <!-- =========================
        SEARCH AND REPLACE -->
        <div class="hdr-sub1 toggle-hdr open" data-type="toggle" data-target="#s3-custom-replace">
            <a href="javascript:void(0)"><i class="fa fa-plus-square"></i>Replace</a>
        </div>

        <div id="s3-custom-replace" class="hdr-sub1-area no-display" >
            <div class="help-target">
                <?php DUPX_View_Funcs::helpIconLink('step3'); ?>
            </div>

            <table class="s3-opts" id="search-replace-table">
                <tr valign="top" id="search-0">
                    <td>Search:</td>
                    <td><input class="w95" type="text" name="search[]" style="margin-right:5px"></td>
                </tr>
                <tr valign="top" id="replace-0"><td>Replace:</td><td><input class="w95" type="text" name="replace[]"></td></tr>
            </table>
            <button type="button" onclick="DUPX.addSearchReplace();return false;" style="font-size:12px;display: block; margin: 10px 0 0 0; " class="default-btn">Add More</button>
        </div>
        <?php
    }

    public static function options()
    {
        $paramsManager = DUPX_Paramas_Manager::getInstance();
        $skipWpConfig  = ($paramsManager->getValue(DUPX_Paramas_Manager::PARAM_WP_CONFIG) == 'nothing');
        ?>
        <!-- ==========================
        OPTIONS -->
        <div class="hdr-sub1 toggle-hdr open" data-type="toggle" data-target="#s3-adv-opts">
            <a href="javascript:void(0)"><i class="fa fa-plus-square"></i>Options</a>
        </div>
        <!-- START TABS -->
        <div id="s3-adv-opts" class="hdr-sub1-area tabs-area no-display">
            <div id="tabs">
                <ul>
                    <li><a href="#tabs-admin-account">Admin Account</a></li>
                    <li><a href="#tabs-scan-options">Scan Options</a></li>
                    <li><a href="#tabs-plugins">Plugins</a></li>
                    <?php if (!$skipWpConfig) { ?>
                        <li><a href="#tabs-wp-config-file">WP-Config File</a></li>
                    <?php } ?>
                </ul>

                <!-- =====================
                ADMIN TAB -->
                <div id="tabs-admin-account">
                    <?php self::tabNewAdmin(); ?>
                </div>

                <!-- =====================
                SCAN TAB -->
                <div id="tabs-scan-options">
                    <?php self::tabScanOptions(); ?>
                </div>

                <!-- =====================
                PLUGINS  TAB -->
                <div id="tabs-plugins">
                    <?php self::tabPluginsContent(); ?>
                </div>
                <?php if (!$skipWpConfig) { ?>
                    <!-- =====================
                    WP-CONFIG TAB -->
                    <div id="tabs-wp-config-file">
                        <?php self::tabWpConfig(); ?>
                    </div>
                <?php } ?>
            </div>
            <?php
        }

        public static function tabNewAdmin()
        {
            $archive_config = DUPX_ArchiveConfig::getInstance();
            ?>
            <div class="help-target">
                <?php DUPX_View_Funcs::helpIconLink('step3'); ?>
            </div>

            <!-- NEW ADMIN ACCOUNT -->
            <div class="hdr-sub3">New Admin Account</div>
            <div style="text-align: center">
                <i style="color:gray;font-size: 11px">This feature is optional.  If the username already exists the account will NOT be created or updated.</i>
                <?php
                if ($archive_config->isNetworkInstall()) {
                    echo '<br><i style="color:gray;font-size: 11px">You will create Network Administrator account</i>';
                }
                ?>
            </div>

            <table class="s3-opts" style="margin-top:7px">
                <tr>
                    <td>Username:</td>
                    <td><input type="text" name="wp_username" id="wp_username" value="" title="4 characters minimum" placeholder="(4 or more characters)" /></td>
                </tr>
                <tr>
                    <td>Password:</td>
                    <td>
                        <?php
                        DUPX_U_Html::inputPasswordToggle('wp_password', 'wp_password', array(),
                                                         array(
                                'placeholder' => '(6 or more characters',
                                'title'       => '6 characters minimum'
                        ));
                        ?>
                    </td>
                </tr>
                <tr>
                    <td>Mail:</td>
                    <td><input type="text" name="wp_mail" id="wp_mail" value="" title=""  placeholder="" /></td>
                </tr>
                <tr>
                    <td>Nickname:</td>
                    <td><input type="text" name="wp_nickname" id="wp_nickname" value="" title="if username is empty"  placeholder="(if username is empty)" /></td>
                </tr>
                <tr>
                    <td>First name:</td>
                    <td><input type="text" name="wp_first_name" id="wp_first_name" value="" title="optional"  placeholder="(optional)" /></td>
                </tr>
                <tr>
                    <td>Last name:</td>
                    <td><input type="text" name="wp_last_name" id="wp_last_name" value="" title="optional"  placeholder="(optional)" /></td>
                </tr>
            </table>
            <?php
        }

        public static function tabScanOptions()
        {
            $archive_config = DUPX_ArchiveConfig::getInstance();
            $paramsManager  = DUPX_Paramas_Manager::getInstance();

            $dbhost = $paramsManager->getValue(DUPX_Paramas_Manager::PARAM_DB_HOST);
            $dbname = $paramsManager->getValue(DUPX_Paramas_Manager::PARAM_DB_NAME);
            $dbuser = $paramsManager->getValue(DUPX_Paramas_Manager::PARAM_DB_USER);
            $dbpass = $paramsManager->getValue(DUPX_Paramas_Manager::PARAM_DB_PASS);

            $dbh                      = DUPX_DB::connect($dbhost, $dbuser, $dbpass, $dbname);
            $all_tables               = DUPX_DB::getTables($dbh);
            $subsiteId                = $paramsManager->getValue(DUPX_Paramas_Manager::PARAM_SUBSITE_ID);
            ?>
            <div class="help-target">
                <?php DUPX_View_Funcs::helpIconLink('step3'); ?>
            </div>
            <div class="hdr-sub3">Database Scan Options</div>
            <div  class="dupx-opts s3-opts">
                <?php
                $paramsManager->getHtmlFormParam(DUPX_Paramas_Manager::PARAM_REPLACE_ENGINE);
                $paramsManager->getHtmlFormParam(DUPX_Paramas_Manager::PARAM_EMPTY_SCHEDULE_STORAGE);
                $paramsManager->getHtmlFormParam(DUPX_Paramas_Manager::PARAM_SITE_URL);
                $paramsManager->getHtmlFormParam(DUPX_Paramas_Manager::PARAM_URL_OLD);
                $paramsManager->getHtmlFormParam(DUPX_Paramas_Manager::PARAM_PATH_OLD);
                ?>
                <div class="param-wrapper" >
                    <b>Scan Tables:</b>
                    <div class="s3-allnonelinks">
                        <a href="javascript:void(0)" onclick="$('#tables option').prop('selected', true);">[All]</a>
                        <a href="javascript:void(0)" onclick="$('#tables option').prop('selected', false);">[None]</a>
                    </div><br style="clear:both" />
                    <select id="tables" name="tables[]" multiple="multiple" size="10">
                        <?php
                        $need_to_check_scan_table = false;
                        if ($GLOBALS['DUPX_AC']->mu_generation > 0 && $GLOBALS['DUPX_AC']->mu_mode > 0 && $subsiteId > 0) {
                            $subsite_table_prefix = $GLOBALS['DUPX_AC']->wp_tableprefix.$subsiteId.'_';
                            $prefix_len           = strlen($GLOBALS['DUPX_AC']->wp_tableprefix);

                            $need_to_check_scan_table = true;
                        }
                        foreach ($all_tables as $table) {
                            if ($need_to_check_scan_table) {
                                $table_len                 = strlen($table);
                                $table_without_base_prefix = substr($table, $prefix_len, $table_len);
                                $table_subsite_id          = intval($table_without_base_prefix);
                                if (($subsiteId != $GLOBALS['DUPX_AC']->main_site_id && 0 === stripos($table, $subsite_table_prefix)) ||
                                    0 === $table_subsite_id) {
                                    echo '<option selected="selected" value="'.DUPX_U::esc_attr($table).'">'.DUPX_U::esc_html($table).'</option>';
                                }
                            } else {
                                echo '<option selected="selected" value="'.DUPX_U::esc_attr($table).'">'.DUPX_U::esc_html($table).'</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>
            <input type="checkbox" name="search_replace_email_domain" id="search_replace_email_domain" value="1" /> <label for="search_replace_email_domain">Update email domains</label><br/>
            <input type="checkbox" name="fullsearch" id="fullsearch" value="1" /> <label for="fullsearch">Use Database Full Search Mode</label><br/>
            <input type="checkbox" name="postguid" id="postguid" value="1" /> <label for="postguid">Keep Post GUID Unchanged</label><br/>
            <label>
                <B>Max size check for serialize objects:</b>
                <input type="number" 
                       name="<?php echo DUPX_CTRL::NAME_MAX_SERIALIZE_STRLEN_IN_M; ?>"
                       value="<?php echo DUPX_Constants::DEFAULT_MAX_STRLEN_SERIALIZED_CHECK_IN_M; ?>"
                       min="0" max="99" step="1" size="2"
                       style="width: 40px;width: 50px; text-align: center;" /> MB
            </label>
            <?php
            if ($archive_config->isNetworkInstall()) {
                $checked = (count($archive_config->subsites) <= MAX_SITES_TO_DEFAULT_ENABLE_CORSS_SEARCH) ? 'checked="checked"' : '';
                ?>
                <input type="checkbox" name="cross_search" id="cross_search" value="1" <?php echo $checked; ?> />
                <label for="cross_search" style="font-weight: normal">Cross-search between the sites of the network.</label><br/>
                <?php
            }
        }

        public static function tabPluginsContent()
        {
            $paramsManager = DUPX_Paramas_Manager::getInstance();
            ?>
            <div class="help-target">
                <?php DUPX_View_Funcs::helpIconLink('step3'); ?>
            </div>
            <?php
            $paramsManager->getHtmlFormParam(DUPX_Paramas_Manager::PARAM_PLUGINS);
        }

        public static function tabWpConfig()
        {
            ?>
            <div class="help-target">
                <?php DUPX_View_Funcs::helpIconLink('step3'); ?>
            </div><br/>
            <table class="dupx-opts dupx-advopts">
                <tr><td colspan="2"><div class="hdr-sub3">Posts/Pages</div></td></tr>
                <tr>
                    <td>Editor:</td>
                    <td>
                        <?php
                        $disallow_file_edit_val      = $GLOBALS['DUPX_AC']->getWpConfigDefineValue('DISALLOW_FILE_EDIT', false);
                        ?>
                        <input type="checkbox" id="disallow_file_edit" name="disallow_file_edit" <?php DupProSnapLibUIU::echoChecked($disallow_file_edit_val); ?> value="1">
                        <label for="disallow_file_edit"><?php echo DUPX_U::esc_attr('Disable the Plugin/Theme Editor'); ?></label>
                    </td>
                </tr>
                <tr>
                    <td>AutoSave Interval:</td>
                    <td>
                        <?php
                        $autosave_interval_val       = $GLOBALS['DUPX_AC']->getWpConfigDefineValue('AUTOSAVE_INTERVAL', '');
                        ?>
                        <input type="number" name="autosave_interval" id="autosave_interval" value="<?php echo DUPX_U::esc_attr($autosave_interval_val); ?>" />
                        <br>
                        <small class="info">Auto-save interval in seconds (default:60) </small>
                    </td>
                </tr>
                <tr>
                    <?php
                    $wp_post_revisions_const_val = $GLOBALS['DUPX_AC']->getWpConfigDefineValue('DISALLOW_FILE_EDIT', null);
                    if (is_null($wp_post_revisions_const_val)) {
                        $wp_post_revisions_val       = '';
                        $wp_post_revisions_const_val = false;
                    } else {
                        if ($wp_post_revisions_const_val) {
                            $wp_post_revisions_val = 'true';
                        } else {
                            $wp_post_revisions_val = 'false';
                        }
                    }
                    ?>
                    <td>Post Revisions:</td>
                    <td>
                        <select name="wp_post_revisions" id="wp_post_revisions">
                            <option value="">Choose...</option>
                            <option value="true" <?php echo 'true' == $wp_post_revisions_val ? 'selected ' : ''; ?>>Yes</option>
                            <option value="false" <?php echo 'false' == $wp_post_revisions_val ? 'selected ' : ''; ?>>No</option>
                        </select>
                    </td>
                </tr>
                <tr><td colspan="2"><div class="hdr-sub3"><br/>Security</div></td></tr>
                <tr>
                    <td>SSL:</td>
                    <?php
                    $force_ssl_admin_val = $GLOBALS['DUPX_AC']->getWpConfigDefineValue('FORCE_SSL_ADMIN', false);
                    ?>
                    <td>
                        <input type="checkbox" name="ssl_admin" id="ssl_admin" <?php DupProSnapLibUIU::echoChecked($force_ssl_admin_val); ?> /> <label for="ssl_admin">Enforce on Admin</label>
                    </td>
                </tr>
                <?php
                if ($GLOBALS['DUPX_AC']->getLicenseType() >= DUPX_LicenseType::Freelancer) :
                    ?>
                    <tr>
                        <td>Auth Keys:</td>
                        <td>
                            <input type="checkbox" name="auth_keys_and_salts" id="auth_keys_and_salts" />
                            <label for="auth_keys_and_salts">Generate New Unique Authentication Keys and Salts</label>
                        </td>
                    </tr>
                <?php else : ?>
                    <tr>
                        <td>Auth Keys:</td>
                        <td>
                            <i>Available only in Freelancer and above</i>
                        </td>
                    </tr>
                <?php endif; ?>
                <tr>
                    <td>Core Auto-Updates:</td>
                    <td>
                        <?php
                        $wp_auto_update_core_val = $GLOBALS['DUPX_AC']->getWpConfigDefineValue('WP_AUTO_UPDATE_CORE', false);
                        ?>
                        <select name="wp_auto_update_core" id="wp_auto_update_core">
                            <option value="">Choose...</option>
                            <option value="false" <?php echo 'false' == $wp_auto_update_core_val ? 'selected ' : ''; ?>>Disable all core updates</option>
                            <option value="true" <?php echo 'true' == $wp_auto_update_core_val ? 'selected ' : ''; ?>>Enable all core updates</option>
                            <option value="minor" <?php echo 'minor' == $wp_auto_update_core_val ? 'selected ' : ''; ?>>Enable only core minor updates - Default</option>
                        </select>
                    </td>
                </tr>					
                <tr><td colspan="2"><div class="hdr-sub3"><br/>System/General</div></td></tr>
                <tr>
                    <td>Cache:</td>
                    <td>
                        <?php
                        $wp_cache_val            = $GLOBALS['DUPX_AC']->getWpConfigDefineValue('WP_CACHE', false);
                        ?>
                        <input type="checkbox" name="cache_wp" id="cache_wp" <?php DupProSnapLibUIU::echoChecked($wp_cache_val); ?> /> <label for="cache_wp">Keep Enabled</label>
                        <br>
                        <?php
                        $wpcachehome_val         = $GLOBALS['DUPX_AC']->getWpConfigDefineValue('WPCACHEHOME', '');
                        ?>
                        <input type="checkbox" name="cache_path" id="cache_path" <?php DupProSnapLibUIU::echoChecked($wpcachehome_val); ?> /> <label for="cache_path">Keep Home Path</label>
                    </td>
                </tr>
                <tr id="wp_post_revisions_no_cont">
                    <?php
                    $wp_post_revisions_no    = '';
                    if (isset($wp_post_revisions_const_val) && intval($wp_post_revisions_const_val) > 0) {
                        $wp_post_revisions_no = intval($wp_post_revisions_const_val);
                    }
                    ?>
                    <td>Limit Revisions	<br />	Number:</td>
                    <td><input type="number" name="wp_post_revisions_no" id="wp_post_revisions_no" value="<?php echo DUPX_U::esc_attr($wp_post_revisions_no); ?>" /></td>
                </tr>
                <tr>
                    <td>WP Debug:</td>
                    <td>
                        <?php
                        $wp_debug_val         = $GLOBALS['DUPX_AC']->getWpConfigDefineValue('WP_DEBUG', false);
                        ?>
                        <input type="checkbox" id="wp-debug" name="wp_debug" <?php DupProSnapLibUIU::echoChecked($wp_debug_val); ?> value="1">
                        <label for="wp-debug">Display errors and warnings</label>
                    </td>
                </tr>
                <tr>
                    <td>WP Debug Log:</td>
                    <td>
                        <?php
                        $wp_debug_log_val     = $GLOBALS['DUPX_AC']->getWpConfigDefineValue('WP_DEBUG_LOG', false);
                        ?>
                        <input type="checkbox" id="wp-debug-log" name="wp_debug_log" <?php DupProSnapLibUIU::echoChecked($wp_debug_log_val); ?> value="1">
                        <label for="wp-debug-log">Log errors and warnings</label>
                    </td>
                </tr>
                <tr>
                    <td>WP Debug Display:</td>
                    <td>
                        <?php
                        $wp_debug_display_val = $GLOBALS['DUPX_AC']->getWpConfigDefineValue('WP_DEBUG_DISPLAY', false);
                        ?>
                        <input type="checkbox" id="wp_debug_display" name="wp_debug_display" <?php DupProSnapLibUIU::echoChecked($wp_debug_display_val); ?> value="1">
                        <label for="wp_debug_display">Display errors and warnings</label>
                    </td>
                </tr>
                <tr>
                    <td>Script Debug:</td>
                    <td>
                        <?php
                        $script_debug_val     = $GLOBALS['DUPX_AC']->getWpConfigDefineValue('SCRIPT_DEBUG', false);
                        ?>
                        <input type="checkbox" id="script_debug" name="script_debug" <?php DupProSnapLibUIU::echoChecked($script_debug_val); ?> value="1">
                        <label for="script_debug">JavaScript or CSS errors</label>
                    </td>
                </tr>
                <tr>
                    <td>Save Queries:</td>
                    <td>
                        <?php
                        $savequeries_val      = $GLOBALS['DUPX_AC']->getWpConfigDefineValue('SAVEQUERIES', false);
                        ?>
                        <input type="checkbox" id="savequeries" name="savequeries" <?php DupProSnapLibUIU::echoChecked($savequeries_val); ?> value="1">
                        <label for="savequeries"><?php echo DUPX_U::esc_attr('Save database queries in an array ($wpdb->queries)'); ?></label>
                    </td>
                </tr>
                <tr>
                    <td>Cookie Domain:</td>
                    <?php
                    $cookie_domain_val    = $GLOBALS['DUPX_AC']->getWpConfigDefineValue('COOKIE_DOMAIN', null);
                    if (is_null($cookie_domain_val)) {
                        $cookie_domain_val = '';
                    } else {
                        if (0 === strpos($cookie_domain_val, '$')) {
                            $cookie_domain_val = '';
                        } elseif (!empty($cookie_domain_val)) {
                            $parsedUrlOld = parse_url($GLOBALS['DUPX_AC']->url_old);
                            $oldDomain    = $parsedUrlOld['host'];
                            // for ngrok url and Local by Flywheel Live URL
                            if (isset($_SERVER['HTTP_X_ORIGINAL_HOST'])) {
                                $host = $_SERVER['HTTP_X_ORIGINAL_HOST'];
                            } else {
                                $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME']; //WAS SERVER_NAME and caused problems on some boxes
                            }
                            $newDomain         = $host;
                            $cookie_domain_val = str_ireplace($oldDomain, $newDomain, $cookie_domain_val);
                        }
                    }
                    ?>
                    <td>
                        <input type="text" name="cookie_domain" id="cookie_domain" value="<?php echo DUPX_U::esc_attr($cookie_domain_val); ?>" />
                        <br>
                        <small class="info">
                            Set <a href="http://www.askapache.com/htaccess/apache-speed-subdomains.html" target="_blank">different domain</a> for cookies.subdomain.example.com
                        </small>
                    </td>
                </tr>
                <tr>
                    <td>Memory Limit:</td>
                    <td>
                        <?php
                        $wp_memory_limit_val     = $GLOBALS['DUPX_AC']->getWpConfigDefineValue('WP_MEMORY_LIMIT', false);
                        ?>
                        <input type="text" name="wp_memory_limit" id="wp_memory_limit" value="<?php echo DUPX_U::esc_attr($wp_memory_limit_val); ?>" />
                        <br>
                        <small class="info">PHP memory limit (default:30M; multi-site default:64M)</small>
                    </td>
                </tr>
                <tr>
                    <td>Max Memory Limit:</td>
                    <td>
                        <?php
                        $wp_max_memory_limit_val = $GLOBALS['DUPX_AC']->getWpConfigDefineValue('WP_MAX_MEMORY_LIMIT', false);
                        ?>
                        <input type="text" name="wp_max_memory_limit" id="wp_max_memory_limit" value="<?php echo DUPX_U::esc_attr($wp_max_memory_limit_val); ?>" />
                        <br>
                        <small class="info"> Maximum memory limit (default:256M)</small>
                    </td>
                </tr>

            </table>
            <?php
        }
    }    