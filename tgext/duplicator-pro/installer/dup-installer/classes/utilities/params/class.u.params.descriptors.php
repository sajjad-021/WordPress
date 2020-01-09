<?php
/**
 * Installer params manager
 *
 * Standard: PSR-2
 * @link http://www.php-fig.org/psr/psr-2 Full Documentation
 *
 * @package SC\DUPX\U
 *
 */
defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/**
 * class where all parameters are initialized. Used by the param manager
 */
final class DUPX_Paramas_Descriptors
{

    /**
     * 
     * @param DUPX_Param_item[] $params
     */
    public static function initGenericParams(&$params)
    {
        $archive_config = DUPX_ArchiveConfig::getInstance();

        $params[DUPX_Paramas_Manager::PARAM_DEBUG] = new DUPX_Param_item(
            DUPX_Paramas_Manager::PARAM_DEBUG,
            DUPX_Param_item_form::TYPE_BOOL,
            array(
            'persistence' => true,
            'default'     => false
            )
        );

        $params[DUPX_Paramas_Manager::PARAM_DEBUG_PARAMS] = new DUPX_Param_item(
            DUPX_Paramas_Manager::PARAM_DEBUG_PARAMS,
            DUPX_Param_item_form::TYPE_BOOL,
            array(
            'persistence' => true,
            'default'     => false
            )
        );

        $params[DUPX_Paramas_Manager::PARAM_CTRL_ACTION] = new DUPX_Param_item_form(
            DUPX_Paramas_Manager::PARAM_CTRL_ACTION,
            DUPX_Param_item_form::TYPE_STRING,
            DUPX_Param_item_form::FORM_TYPE_HIDDEN,
            array(
            'persistence'  => false,
            'default'      => '',
            'acceptValues' => array(
                'ctrl-step0',
                'ctrl-step1',
                'ctrl-step2',
                'ctrl-step3'
            ))
        );

        $params[DUPX_Paramas_Manager::PARAM_VIEW] = new DUPX_Param_item_form(
            DUPX_Paramas_Manager::PARAM_VIEW,
            DUPX_Param_item_form::TYPE_STRING,
            DUPX_Param_item_form::FORM_TYPE_HIDDEN,
            array(
            'persistence'  => false,
            'default'      => 'secure',
            'acceptValues' => array(
                'secure',
                'step1',
                'step2',
                'step3',
                'step4',
                'help'
            ))
        );

        $params[DUPX_Paramas_Manager::PARAM_ROUTER_ACTION] = new DUPX_Param_item(
            DUPX_Paramas_Manager::PARAM_ROUTER_ACTION,
            DUPX_Param_item_form::TYPE_STRING,
            array(
            'persistence'  => false,
            'default'      => 'router',
            'acceptValues' => array(
                'router'
            ))
        );

        $params[DUPX_Paramas_Manager::PARAM_SECURE_TRY] = new DUPX_Param_item_form(
            DUPX_Paramas_Manager::PARAM_SECURE_TRY,
            DUPX_Param_item::TYPE_BOOL,
            DUPX_Param_item_form::FORM_TYPE_HIDDEN,
            array(
            'default' => false
            )
        );

        $params[DUPX_Paramas_Manager::PARAM_SECURE_PASS] = new DUPX_Param_item_form_pass(
            DUPX_Paramas_Manager::PARAM_SECURE_PASS,
            DUPX_Param_item_form_pass::TYPE_STRING,
            DUPX_Param_item_form_pass::FORM_TYPE_PWD_TOGGLE,
            array(
            'persistence'      => false,
            'default'          => null,
            'sanitizeCallback' => array('DupProSnapLibUtil', 'sanitize_non_stamp_chars_and_newline')
            ),
            array(
            'label' => 'Enter Password'
            )
        );

        $subSiteOptions  = self::getSubSiteIdsOptions();
        $muInstOptions   = self::getMultisiteInstallerTypeOptions();
        $standaloneLabel = 'Convert subsite to standalone'.(empty($muInstOptions['subNote']) ? '' : ' *');

        $params[DUPX_Paramas_Manager::PARAM_SUBSITE_ID] = new DUPX_Param_item_form(
            DUPX_Paramas_Manager::PARAM_SUBSITE_ID,
            DUPX_Param_item_form::TYPE_INT,
            DUPX_Param_item_form::FORM_TYPE_SELECT,
            array(
            'default'      => -1,
            'acceptValues' => $subSiteOptions['acceptValues']
            ),
            array(
            'status' => function() {
                if (DUPX_Paramas_Manager::getInstance()->getValue(DUPX_Paramas_Manager::PARAM_MULTISITE_INST_TYPE) != 1) {
                    return DUPX_Param_item_form::STATUS_DISABLED;
                } else {
                    return DUPX_Param_item_form::STATUS_ENABLED;
                }
            },
            'label'          => 'Subsite:',
            'wrapperClasses' => $muInstOptions['default'] == 0 ? array('no-display') : array(),
            'options'        => $subSiteOptions['options'],
            )
        );

        $params[DUPX_Paramas_Manager::PARAM_MULTISITE_INST_TYPE] = new DUPX_Param_item_form(
            DUPX_Paramas_Manager::PARAM_MULTISITE_INST_TYPE,
            DUPX_Param_item_form::TYPE_INT,
            DUPX_Param_item_form::FORM_TYPE_RADIO,
            array(
            'default'      => $muInstOptions['default'],
            'acceptValues' => $muInstOptions['acceptValues']
            ),
            array(
            'status'         => (!DUPX_Conf_Utils::showMultisite() || $muInstOptions['acceptValues'][0] == -1) ? DUPX_Param_item_form::STATUS_SKIP : DUPX_Param_item_form::STATUS_ENABLED,
            'label'          => 'Install Type:',
            'wrapperClasses' => array('group-block'),
            'options'        => array(
                new DUPX_Param_item_form_option(0, 'Restore multisite network',
                                                !$archive_config->mu_is_filtered ? DUPX_Param_item_form_option::OPT_ENABLED : DUPX_Param_item_form_option::OPT_DISABLED,
                                                array(
                    'onchange' => "jQuery('#".$params[DUPX_Paramas_Manager::PARAM_SUBSITE_ID]->getFormItemId()."').prop('disabled', true);"
                    ."jQuery('#".$params[DUPX_Paramas_Manager::PARAM_SUBSITE_ID]->getFormWrapperId()."').addClass('no-display');"
                    )),
                new DUPX_Param_item_form_option(1, $standaloneLabel,
                                                DUPX_Conf_Utils::multisitePlusEnabled() ? DUPX_Param_item_form_option::OPT_ENABLED : DUPX_Param_item_form_option::OPT_DISABLED,
                                                array(
                    'onchange' => "jQuery('#".$params[DUPX_Paramas_Manager::PARAM_SUBSITE_ID]->getFormItemId()."').prop('disabled', false);"
                    ."jQuery('#".$params[DUPX_Paramas_Manager::PARAM_SUBSITE_ID]->getFormWrapperId()."').removeClass('no-display');"
                    ))
            ),
            'subNote'        => $muInstOptions['subNote'])
        );

        $engineOptions                                      = self::getArchiveEngineOptions();
        $params[DUPX_Paramas_Manager::PARAM_ARCHIVE_ENGINE] = new DUPX_Param_item_form(
            DUPX_Paramas_Manager::PARAM_ARCHIVE_ENGINE,
            DUPX_Param_item_form::TYPE_STRING,
            DUPX_Param_item_form::FORM_TYPE_SELECT,
            array(
            'default'      => $engineOptions['default'],
            'acceptValues' => $engineOptions['acceptValues']
            ),
            array(
            'label'   => 'Extraction:',
            'options' => $engineOptions['options'],
            'size'    => 0,
            'subNote' => $engineOptions['subNote'],
            'attr'    => array(
                'onchange' => 'DUPX.onSafeModeSwitch();'
            ))
        );

        $params[DUPX_Paramas_Manager::PARAM_FILE_PERMS_VALUE] = new DUPX_Param_item_form(
            DUPX_Paramas_Manager::PARAM_FILE_PERMS_VALUE,
            DUPX_Param_item_form::TYPE_STRING,
            DUPX_Param_item_form::FORM_TYPE_NUMBER,
            array(
            'default'          => '644',
            'sanitizeCallback' => array('DupProSnapLibUtil', 'sanitize_non_stamp_chars_newline_and_trim'),
            'validateRegex'    => '/^[ugorwx,\s\+\-0-7]+$/' // octal + ugo rwx,
            ),
            array(
            'status'         => DupProSnapLibOSU::isWindows() ? DUPX_Param_item_form::STATUS_SKIP : DUPX_Param_item_form::STATUS_ENABLED,
            'wrapperClasses' => array('display-inline-block')
            )
        );

        $params[DUPX_Paramas_Manager::PARAM_SET_FILE_PERMS] = new DUPX_Param_item_form(
            DUPX_Paramas_Manager::PARAM_SET_FILE_PERMS,
            DUPX_Param_item_form::TYPE_BOOL,
            DUPX_Param_item_form::FORM_TYPE_CHECKBOX,
            array(
            'default' => !DupProSnapLibOSU::isWindows()
            ),
            array(
            'status'         => DupProSnapLibOSU::isWindows() ? DUPX_Param_item_form::STATUS_SKIP : DUPX_Param_item_form::STATUS_ENABLED,
            'label'          => 'File permissions:',
            'checkboxLabel'  => 'All files',
            'wrapperClasses' => array('display-inline-block'),
            'attr'           => array(
                'onclick' => "jQuery('#".$params[DUPX_Paramas_Manager::PARAM_FILE_PERMS_VALUE]->getFormItemId()."').prop('disabled', !jQuery(this).is(':checked'));"
            )
            )
        );

        $params[DUPX_Paramas_Manager::PARAM_DIR_PERMS_VALUE] = new DUPX_Param_item_form(
            DUPX_Paramas_Manager::PARAM_DIR_PERMS_VALUE,
            DUPX_Param_item_form::TYPE_STRING,
            DUPX_Param_item_form::FORM_TYPE_NUMBER,
            array(
            'default'          => '755',
            'sanitizeCallback' => array('DupProSnapLibUtil', 'sanitize_non_stamp_chars_newline_and_trim'),
            'validateRegex'    => '/^[ugorwx,\s\+\-0-7]+$/' // octal + ugo rwx
            ),
            array(
            'status'         => DupProSnapLibOSU::isWindows() ? DUPX_Param_item_form::STATUS_SKIP : DUPX_Param_item_form::STATUS_ENABLED,
            'wrapperClasses' => array('display-inline-block')
            )
        );

        $params[DUPX_Paramas_Manager::PARAM_SET_DIR_PERMS] = new DUPX_Param_item_form(
            DUPX_Paramas_Manager::PARAM_SET_DIR_PERMS,
            DUPX_Param_item_form::TYPE_BOOL,
            DUPX_Param_item_form::FORM_TYPE_CHECKBOX,
            array(
            'default' => !DupProSnapLibOSU::isWindows()
            ),
            array(
            'status'         => DupProSnapLibOSU::isWindows() ? DUPX_Param_item_form::STATUS_SKIP : DUPX_Param_item_form::STATUS_ENABLED,
            'label'          => 'Dir permissions:',
            'checkboxLabel'  => 'All Directories',
            'wrapperClasses' => array('display-inline-block'),
            'attr'           => array(
                'onclick' => "jQuery('#".$params[DUPX_Paramas_Manager::PARAM_DIR_PERMS_VALUE]->getFormItemId()."').prop('disabled', !jQuery(this).is(':checked'));"
            )
            )
        );

        $params[DUPX_Paramas_Manager::PARAM_SAFE_MODE] = new DUPX_Param_item_form(
            DUPX_Paramas_Manager::PARAM_SAFE_MODE,
            DUPX_Param_item_form::TYPE_INT,
            DUPX_Param_item_form::FORM_TYPE_SELECT,
            array(
            'default'      => 0,
            'acceptValues' => array(0, 1, 2)),
            array(
            'label'   => 'Safe Mode:',
            'options' => array(
                new DUPX_Param_item_form_option(0, 'Light'),
                new DUPX_Param_item_form_option(1, 'Basic'),
                new DUPX_Param_item_form_option(2, 'Advanced')
            ),
            'attr'    => array(
                'onchange' => 'DUPX.onSafeModeSwitch();'
            ))
        );

        $params[DUPX_Paramas_Manager::PARAM_CLIENT_KICKOFF] = new DUPX_Param_item_form(
            DUPX_Paramas_Manager::PARAM_CLIENT_KICKOFF,
            DUPX_Param_item_form::TYPE_BOOL,
            DUPX_Param_item_form::FORM_TYPE_CHECKBOX,
            array(
            'default' => true
            ),
            array(
            'label'         => 'Client-Kickoff:',
            'checkboxLabel' => 'Browser drives the archive engine.'
            )
        );

        $params[DUPX_Paramas_Manager::PARAM_FILE_TIME] = new DUPX_Param_item_form(
            DUPX_Paramas_Manager::PARAM_FILE_TIME,
            DUPX_Param_item_form::TYPE_STRING,
            DUPX_Param_item_form::FORM_TYPE_RADIO,
            array(
            'default'      => 'current',
            'acceptValues' => array(
                'current',
                'original'
            )),
            array(
            'label'   => 'File Times:',
            'options' => array(
                new DUPX_Param_item_form_option('current', 'Current', DUPX_Param_item_form_option::OPT_ENABLED, array('title' => 'Set the files current date time to now')),
                new DUPX_Param_item_form_option('original', 'Original', DUPX_Param_item_form_option::OPT_ENABLED, array('title' => 'Keep the files date time the same'))
            ))
        );

        $params[DUPX_Paramas_Manager::PARAM_LOGGING] = new DUPX_Param_item_form(
            DUPX_Paramas_Manager::PARAM_LOGGING,
            DUPX_Param_item_form::TYPE_INT,
            DUPX_Param_item_form::FORM_TYPE_RADIO,
            array(
            'default'      => DUPX_Log::LV_DEFAULT,
            'acceptValues' => array(
                DUPX_Log::LV_DEFAULT,
                DUPX_Log::LV_DETAILED,
                DUPX_Log::LV_DEBUG,
                DUPX_Log::LV_HARD_DEBUG,
            )),
            array(
            'label'   => 'Logging:',
            'options' => array(
                new DUPX_Param_item_form_option(DUPX_Log::LV_DEFAULT, 'Light'),
                new DUPX_Param_item_form_option(DUPX_Log::LV_DETAILED, 'Detailed'),
                new DUPX_Param_item_form_option(DUPX_Log::LV_DEBUG, 'Debug'),
                new DUPX_Param_item_form_option(DUPX_Log::LV_HARD_DEBUG, 'Hard debug')
            ))
        );

        $params[DUPX_Paramas_Manager::PARAM_REMOVE_RENDUNDANT] = new DUPX_Param_item_form(
            DUPX_Paramas_Manager::PARAM_REMOVE_RENDUNDANT,
            DUPX_Param_item_form::TYPE_BOOL,
            DUPX_Param_item_form::FORM_TYPE_CHECKBOX,
            array(
            'default' => false
            ),
            array(
            'label'         => 'Inactive Plugins and Themes:',
            'checkboxLabel' => 'Migrate only active themes and plugins.',
            'wrapperId'     => 'remove-redundant-row',
            'subNote'       => DUPX_Conf_Utils::showMultisite() ? 'When checked for a subsite to standalone migration, only active users will be retained also.' : null
            )
        );

        $params[DUPX_Paramas_Manager::PARAM_ACCEPT_TERM_COND] = new DUPX_Param_item_form(
            DUPX_Paramas_Manager::PARAM_ACCEPT_TERM_COND,
            DUPX_Param_item_form::TYPE_BOOL,
            DUPX_Param_item_form::FORM_TYPE_CHECKBOX,
            array(
            'default' => false
            ),
            array(
            'label'         => '',
            'checkboxLabel' => 'I have read and accept all <a href="#" onclick="DUPX.viewTerms()" >terms &amp; notices</a>',
            'subNote'       => '* required to continue',
            'attr'          => array(
                'onclick' => 'DUPX.acceptWarning();'
            )
            )
        );

        $params[DUPX_Paramas_Manager::PARAM_WP_CONFIG] = new DUPX_Param_item_form(
            DUPX_Paramas_Manager::PARAM_WP_CONFIG,
            DUPX_Param_item_form::TYPE_STRING,
            DUPX_Param_item_form::FORM_TYPE_RADIO,
            array(
            'default'      => 'modify',
            'acceptValues' => array(
                'modify',
                'nothing',
                'new'
            )),
            array(
            'label'   => 'wp-config:',
            'options' => array(
                new DUPX_Param_item_form_option('modify', 'Modify current'),
                new DUPX_Param_item_form_option('nothing', 'Do nothing'),
                new DUPX_Param_item_form_option('new', 'Create new from wp-config sample', DUPX_Param_item_form_option::OPT_HIDDEN), //  not ye implemented
            ))
        );

        $params[DUPX_Paramas_Manager::PARAM_HTACCESS_CONFIG] = new DUPX_Param_item_form(
            DUPX_Paramas_Manager::PARAM_HTACCESS_CONFIG,
            DUPX_Param_item_form::TYPE_STRING,
            DUPX_Param_item_form::FORM_TYPE_RADIO,
            array(
            'default'      => 'new',
            'acceptValues' => array(
                'new',
                'original',
                'nothing'
            )),
            array(
            'label'   => 'htaccess:',
            'options' => array(
                new DUPX_Param_item_form_option('new', 'Create new'),
                new DUPX_Param_item_form_option('original', 'Retain original'),
                new DUPX_Param_item_form_option('nothing', 'Do nothing')
            ))
        );

        $params[DUPX_Paramas_Manager::PARAM_OTHER_CONFIG] = new DUPX_Param_item_form(
            DUPX_Paramas_Manager::PARAM_OTHER_CONFIG,
            DUPX_Param_item_form::TYPE_STRING,
            DUPX_Param_item_form::FORM_TYPE_RADIO,
            array(
            'default'      => 'new',
            'acceptValues' => array(
                'new',
                'original',
                'nothing'
            )),
            array(
            'label'   => 'Other config (web.config, user.ini):',
            'options' => array(
                new DUPX_Param_item_form_option('new', 'Reset'),
                new DUPX_Param_item_form_option('original', 'Retain original'),
                new DUPX_Param_item_form_option('nothing', 'Do nothing', DUPX_Param_item_form_option::OPT_HIDDEN)
            ))
        );
    }

    /**
     * 
     * @param DUPX_Param_item[] $params
     */
    public static function initDatabaseParams(&$params)
    {

        $params[DUPX_Paramas_Manager::PARAM_DB_TEST_OK] = new DUPX_Param_item(
            DUPX_Paramas_Manager::PARAM_DB_TEST_OK,
            DUPX_Param_item::TYPE_BOOL,
            array(
            'default' => false
            )
        );

        $params[DUPX_Paramas_Manager::PARAM_DB_VIEW_MODE] = new DUPX_Param_item_form(
            DUPX_Paramas_Manager::PARAM_DB_VIEW_MODE,
            DUPX_Param_item_form::TYPE_STRING,
            DUPX_Param_item_form::FORM_TYPE_HIDDEN,
            array(
            'persistence'  => true,
            'default'      => 'basic',
            'acceptValues' => array(
                'basic',
                'cpnl'
            ))
        );

        $params[DUPX_Paramas_Manager::PARAM_DB_ACTION] = new DUPX_Param_item_form(
            DUPX_Paramas_Manager::PARAM_DB_ACTION,
            DUPX_Param_item_form::TYPE_STRING,
            DUPX_Param_item_form::FORM_TYPE_SELECT,
            array(
            'default'      => 'empty',
            'acceptValues' => array(
                'create',
                'empty',
                'rename',
                'manual')
            ),
            array(
            'label'   => 'Action:',
            'options' => array(
                new DUPX_Param_item_form_option('create',
                                                'Create New Database',
                                                function () {
                        if (!($state = $GLOBALS['DUPX_STATE'])) {
                            throw new Exception('State objectnot inizialized');
                        }
                        if ($state->mode == DUPX_InstallerMode::StandardInstall) {
                            return DUPX_Param_item_form_option::OPT_ENABLED;
                        } else {
                            return DUPX_Param_item_form_option::OPT_DISABLED;
                        }
                    }),
                new DUPX_Param_item_form_option('empty', 'Connect and Remove All Data'),
                new DUPX_Param_item_form_option('rename', 'Connect and Backup Any Existing Data'),
                new DUPX_Param_item_form_option('manual', 'Manual SQL Execution (Advanced)')
            )
            )
        );

        $params[DUPX_Paramas_Manager::PARAM_DB_HOST] = new DUPX_Param_item_form(
            DUPX_Paramas_Manager::PARAM_DB_HOST,
            DUPX_Param_item_form_pass::TYPE_STRING,
            DUPX_Param_item_form_pass::FORM_TYPE_TEXT,
            array(
            'persistence'      => true,
            'default'          => null,
            'sanitizeCallback' => array('DupProSnapLibUtil', 'sanitize_non_stamp_chars_and_newline')
            ),
            array(
            'label' => 'Host:',
            'attr'  => array(
                'required'    => 'required',
                'placeholder' => 'localhost'
            )
            )
        );

        $params[DUPX_Paramas_Manager::PARAM_DB_NAME] = new DUPX_Param_item_form(
            DUPX_Paramas_Manager::PARAM_DB_NAME,
            DUPX_Param_item_form_pass::TYPE_STRING,
            DUPX_Param_item_form_pass::FORM_TYPE_TEXT,
            array(
            'persistence'      => true,
            'default'          => null,
            'sanitizeCallback' => array('DupProSnapLibUtil', 'sanitize_non_stamp_chars_and_newline')
            ),
            array(
            'label'   => 'Database:',
            'attr'    => array(
                'required'    => 'required',
                'placeholder' => 'new or existing database name'
            ),
            'subNote' => <<<NOTE
<span class="s2-warning-emptydb">
    Warning: The selected 'Action' above will remove <u>all data</u> from this database!
</span>
<span class="s2-warning-renamedb">
    Notice: The selected 'Action' will rename <u>all existing tables</u> from the database name above with a prefix {$GLOBALS['DB_RENAME_PREFIX']}
    The prefix is only applied to existing tables and not the new tables that will be installed.
</span>
<span class="s2-warning-manualdb">
    Notice: The 'Manual SQL execution' action will prevent the SQL script in the archive from running. The database above should already be
    pre-populated with data which will be updated in the next step. No data in the database will be modified until after Step 3 runs.
</span>
NOTE
            )
        );

        $params[DUPX_Paramas_Manager::PARAM_DB_USER] = new DUPX_Param_item_form(
            DUPX_Paramas_Manager::PARAM_DB_USER,
            DUPX_Param_item_form_pass::TYPE_STRING,
            DUPX_Param_item_form_pass::FORM_TYPE_TEXT,
            array(
            'persistence'      => true,
            'default'          => null,
            'sanitizeCallback' => array('DupProSnapLibUtil', 'sanitize_non_stamp_chars_and_newline')
            ),
            array(
            'label' => 'User:',
            'attr'  => array(
                'required'    => 'required',
                'placeholder' => 'valid database username'
            )
            )
        );

        $params[DUPX_Paramas_Manager::PARAM_DB_PASS] = new DUPX_Param_item_form_pass(
            DUPX_Paramas_Manager::PARAM_DB_PASS,
            DUPX_Param_item_form_pass::TYPE_STRING,
            DUPX_Param_item_form_pass::FORM_TYPE_PWD_TOGGLE,
            array(
            'persistence'      => true,
            'default'          => null,
            'sanitizeCallback' => array('DupProSnapLibUtil', 'sanitize_non_stamp_chars_and_newline')
            ),
            array(
            'label' => 'Password:',
            'attr'  => array(
                'placeholder' => 'valid database user password'
            )
            )
        );

        $params[DUPX_Paramas_Manager::PARAM_DB_CHARSET] = new DUPX_Param_item_form(
            DUPX_Paramas_Manager::PARAM_DB_CHARSET,
            DUPX_Param_item_form::TYPE_STRING,
            DUPX_Param_item_form::FORM_TYPE_TEXT,
            array(
            'default'          => $GLOBALS['DBCHARSET_DEFAULT'],
            'sanitizeCallback' => array('DupProSnapLibUtil', 'sanitize_non_stamp_chars_newline_and_trim'),
            'validateRegex'    => DUPX_Param_item_form::VALIDATE_REGEX_AZ_NUMBER_SEP
            ),
            array(
            'label' => 'Charset:'
            )
        );

        $params[DUPX_Paramas_Manager::PARAM_DB_COLLATE] = new DUPX_Param_item_form(
            DUPX_Paramas_Manager::PARAM_DB_COLLATE,
            DUPX_Param_item_form::TYPE_STRING,
            DUPX_Param_item_form::FORM_TYPE_TEXT,
            array(
            'default'          => $GLOBALS['DBCOLLATE_DEFAULT'],
            'sanitizeCallback' => array('DupProSnapLibUtil', 'sanitize_non_stamp_chars_newline_and_trim'),
            'validateRegex'    => DUPX_Param_item_form::VALIDATE_REGEX_AZ_NUMBER_SEP
            ),
            array(
            'label' => 'Collation:'
            )
        );

        $params[DUPX_Paramas_Manager::PARAM_DB_COLLATE_FB] = new DUPX_Param_item_form(
            DUPX_Paramas_Manager::PARAM_DB_COLLATE_FB,
            DUPX_Param_item_form::TYPE_BOOL,
            DUPX_Param_item_form::FORM_TYPE_CHECKBOX,
            array(
            'default' => false
            ),
            array(
            'label'         => 'Legacy:',
            'checkboxLabel' => 'Enable legacy collation fallback support for unknown collations types.'
            )
        );

        $params[DUPX_Paramas_Manager::PARAM_DB_CHUNK] = new DUPX_Param_item_form(
            DUPX_Paramas_Manager::PARAM_DB_CHUNK,
            DUPX_Param_item_form::TYPE_BOOL,
            DUPX_Param_item_form::FORM_TYPE_CHECKBOX,
            array(
            'default' => true
            ),
            array(
            'label'         => 'Chunking:',
            'checkboxLabel' => 'Enable multi-threaded requests to chunk SQL file.'
            )
        );

        $params[DUPX_Paramas_Manager::PARAM_DB_SPACING] = new DUPX_Param_item_form(
            DUPX_Paramas_Manager::PARAM_DB_SPACING,
            DUPX_Param_item_form::TYPE_BOOL,
            DUPX_Param_item_form::FORM_TYPE_CHECKBOX,
            array(
            'default' => false
            ),
            array(
            'label'         => 'Spacing:',
            'checkboxLabel' => 'Enable non-breaking space characters fix.'
            )
        );

        $params[DUPX_Paramas_Manager::PARAM_DB_VIEW_CEATION] = new DUPX_Param_item_form(
            DUPX_Paramas_Manager::PARAM_DB_VIEW_CEATION,
            DUPX_Param_item_form::TYPE_BOOL,
            DUPX_Param_item_form::FORM_TYPE_CHECKBOX,
            array(
            'default' => true
            ),
            array(
            'label'         => 'Views:',
            'checkboxLabel' => 'Enable View Creation.'
            )
        );

        $params[DUPX_Paramas_Manager::PARAM_DB_PROC_CREATION] = new DUPX_Param_item_form(
            DUPX_Paramas_Manager::PARAM_DB_PROC_CREATION,
            DUPX_Param_item_form::TYPE_BOOL,
            DUPX_Param_item_form::FORM_TYPE_CHECKBOX,
            array(
            'default' => true
            ),
            array(
            'label'         => 'Store procedures:',
            'checkboxLabel' => 'Enable Stored Procedure Creation.'
            )
        );

        $params[DUPX_Paramas_Manager::PARAM_DB_MYSQL_MODE_OPTS] = new DUPX_Param_item_form(
            DUPX_Paramas_Manager::PARAM_DB_MYSQL_MODE_OPTS,
            DUPX_Param_item_form::TYPE_STRING,
            DUPX_Param_item_form::FORM_TYPE_TEXT,
            array(
            'default'          => '',
            'validateRegex'    => '/^[A-Za-z0-9_\-,]*$/', // db options with , and can be empty 
            'sanitizeCallback' => function ($value) {
                $value = DupProSnapLibUtil::sanitize_non_stamp_chars_newline_and_trim($value);
                return str_replace(' ', '', $value);
            },
            ),
                                   array(
            'label'          => ' ',
            'wrapperClasses' => 'no-display',
            'subNote'        => 'Separate additional '.DUPX_View_Funcs::helpLink('step2', 'sql modes', false).' with commas &amp; no spaces.<br>'
            .'Example: <i>NO_ENGINE_SUBSTITUTION,NO_ZERO_IN_DATE,...</i>.</small>'
            )
        );

        $params[DUPX_Paramas_Manager::PARAM_DB_MYSQL_MODE] = new DUPX_Param_item_form(
            DUPX_Paramas_Manager::PARAM_DB_MYSQL_MODE,
            DUPX_Param_item_form::TYPE_STRING,
            DUPX_Param_item_form::FORM_TYPE_RADIO,
            array(
            'default'      => 'DEFAULT',
            'acceptValues' => array(
                'DEFAULT',
                'DISABLE',
                'CUSTOM'
            )
            ),
            array(
            'label'   => 'Mode:',
            'options' => array(
                new DUPX_Param_item_form_option('DEFAULT', 'Default', DUPX_Param_item_form_option::OPT_ENABLED, array(
                    'onchange' => "if ($(this).is(':checked')) { "
                    ."jQuery('#".$params[DUPX_Paramas_Manager::PARAM_DB_MYSQL_MODE_OPTS]->getFormWrapperId()."').addClass('no-display');"
                    ."}"
                    )),
                new DUPX_Param_item_form_option('DISABLE', 'Disable', DUPX_Param_item_form_option::OPT_ENABLED, array(
                    'onchange' => "if ($(this).is(':checked')) { "
                    ."jQuery('#".$params[DUPX_Paramas_Manager::PARAM_DB_MYSQL_MODE_OPTS]->getFormWrapperId()."').addClass('no-display');"
                    ."}"
                    )),
                new DUPX_Param_item_form_option('CUSTOM', 'Custom', DUPX_Param_item_form_option::OPT_ENABLED, array(
                    'onchange' => "if ($(this).is(':checked')) { "
                    ."jQuery('#".$params[DUPX_Paramas_Manager::PARAM_DB_MYSQL_MODE_OPTS]->getFormWrapperId()."').removeClass('no-display');"
                    ."}")),
            ))
        );



        /**
         * <td>Prefix:</td>
          <td>
          <input type="checkbox" name="cpnl_ignore_prefix"  id="cpnl_ignore_prefix" value="1" onclick="DUPX.cpnlPrefixIgnore()" />
          <label for="cpnl_ignore_prefix">Ignore cPanel Prefix</label>
          </td>
         */
    }

    /**
     * 
     * @param DUPX_Param_item[] $params
     */
    public static function initCpanelParams(&$params)
    {
        $params[DUPX_Paramas_Manager::PARAM_CPNL_HOST] = new DUPX_Param_item_form(
            DUPX_Paramas_Manager::PARAM_CPNL_HOST,
            DUPX_Param_item_form_pass::TYPE_STRING,
            DUPX_Param_item_form_pass::FORM_TYPE_TEXT,
            array(
            'default'          => $GLOBALS['DUPX_AC']->cpnl_host,
            'sanitizeCallback' => array('DupProSnapLibUtil', 'sanitize_non_stamp_chars_and_newline')
            ),
            array(
            'label'            => 'Host:',
            'attr'             => array(
                'required'    => 'true',
                'placeholder' => 'cPanel url'
            ),
            'subNote'          => '<span id="cpnl-host-warn">'
            .'Caution: The cPanel host name and URL in the browser address bar do not match, '
            .'in rare cases this may be intentional.'
            .'Please be sure this is the correct server to avoid data loss.</span>',
            'postfixElement'   => 'button',
            'postfixElemLabel' => 'get',
            'postfixBtnAction' => 'DUPX.getcPanelURL(this);'
            )
        );

        $params[DUPX_Paramas_Manager::PARAM_CPNL_USER] = new DUPX_Param_item_form(
            DUPX_Paramas_Manager::PARAM_CPNL_USER,
            DUPX_Param_item_form_pass::TYPE_STRING,
            DUPX_Param_item_form_pass::FORM_TYPE_TEXT,
            array(
            'default'          => $GLOBALS['DUPX_AC']->cpnl_user,
            'sanitizeCallback' => array('DupProSnapLibUtil', 'sanitize_non_stamp_chars_and_newline')
            ),
            array(
            'label' => 'Username:',
            'attr'  => array(
                'required'             => 'required',
                'data-parsley-pattern' => '/^[\w.-~]+$/',
                'placeholder'          => 'cPanel username'
            )
            )
        );

        $params[DUPX_Paramas_Manager::PARAM_CPNL_PASS] = new DUPX_Param_item_form_pass(
            DUPX_Paramas_Manager::PARAM_CPNL_PASS,
            DUPX_Param_item_form_pass::TYPE_STRING,
            DUPX_Param_item_form_pass::FORM_TYPE_PWD_TOGGLE,
            array(
            'default'          => $GLOBALS['DUPX_AC']->cpnl_pass,
            'sanitizeCallback' => array('DupProSnapLibUtil', 'sanitize_non_stamp_chars_and_newline')
            ),
            array(
            'label' => 'Password:',
            'attr'  => array(
                'required'    => 'true',
                'placeholder' => 'cPanel password'
            )
            )
        );

        $params[DUPX_Paramas_Manager::PARAM_CPNL_DB_ACTION] = $params[DUPX_Paramas_Manager::PARAM_DB_ACTION]->getCopyWithNewName(
            DUPX_Paramas_Manager::PARAM_CPNL_DB_ACTION,
            array(),
            array(
                'status' => DUPX_Param_item_form::STATUS_DISABLED
        ));
        // force create database enable for cpanel
        $params[DUPX_Paramas_Manager::PARAM_CPNL_DB_ACTION]->setOptionStatus(0, DUPX_Param_item_form_option::OPT_ENABLED);

        $params[DUPX_Paramas_Manager::PARAM_CPNL_PREFIX] = new DUPX_Param_item_form(
            DUPX_Paramas_Manager::PARAM_CPNL_PREFIX,
            DUPX_Param_item_form::TYPE_STRING,
            DUPX_Param_item_form::FORM_TYPE_HIDDEN,
            array(
            'default'          => '',
            'sanitizeCallback' => array('DupProSnapLibUtil', 'sanitize_non_stamp_chars_and_newline')
            )
        );

        $params[DUPX_Paramas_Manager::PARAM_CPNL_DB_HOST] = $params[DUPX_Paramas_Manager::PARAM_DB_HOST]->getCopyWithNewName(
            DUPX_Paramas_Manager::PARAM_CPNL_DB_HOST,
            array(
                'default' => $GLOBALS['DUPX_AC']->cpnl_dbhost
            ),
            array(
                'status' => DUPX_Param_item_form::STATUS_DISABLED,
                'attr'   => array(
                    'required' => 'true'
                )
        ));

        $params[DUPX_Paramas_Manager::PARAM_CPNL_DB_NAME_SEL] = new DUPX_Param_item_form(
            DUPX_Paramas_Manager::PARAM_CPNL_DB_NAME_SEL,
            DUPX_Param_item_form::TYPE_STRING,
            DUPX_Param_item_form::FORM_TYPE_SELECT,
            array(
            'default'          => null,
            'sanitizeCallback' => array('DupProSnapLibUtil', 'sanitize_non_stamp_chars_and_newline')
            ),
            array(
            'label'   => 'Database:',
            'status'  => DUPX_Param_item_form::STATUS_DISABLED,
            'attr'    => array(
                'required'             => 'true',
                'data-parsley-pattern' => '^((?!-- Select Database --).)*$'
            ),
            'subNote' => '<span class="s2-warning-emptydb">'
            .'Warning: The selected "Action" above will remove <u>all data</u> from this database!'
            .'</span>'
            )
        );

        $params[DUPX_Paramas_Manager::PARAM_CPNL_DB_NAME_TXT] = $params[DUPX_Paramas_Manager::PARAM_DB_NAME]->getCopyWithNewName(
            DUPX_Paramas_Manager::PARAM_CPNL_DB_NAME_TXT,
            array(
                'default' => $GLOBALS['DUPX_AC']->cpnl_dbname
            ),
            array(
                'label'           => ' ',
                'status'          => DUPX_Param_item_form::STATUS_DISABLED,
                'attr'            => array(
                    'required'                      => 'true',
                    'data-parsley-pattern'          => '/^[\w.-~]+$/',
                    'data-parsley-errors-container' => '#cpnl-dbname-txt-error'
                ),
                'subNote'         => '<span id="cpnl-dbname-txt-error"></span>',
                'prefixElement'   => 'label',
                'prefixElemLabel' => '',
                'prefixElemId'    => 'cpnl-prefix-dbname'
        ));

        $params[DUPX_Paramas_Manager::PARAM_CPNL_DB_USER_SEL] = new DUPX_Param_item_form(
            DUPX_Paramas_Manager::PARAM_CPNL_DB_USER_SEL,
            DUPX_Param_item_form::TYPE_STRING,
            DUPX_Param_item_form::FORM_TYPE_SELECT,
            array(
            'default'          => null,
            'sanitizeCallback' => array('DupProSnapLibUtil', 'sanitize_non_stamp_chars_and_newline')
            ),
            array(
            'label'  => 'User:',
            'status' => DUPX_Param_item_form::STATUS_DISABLED,
            'attr'   => array(
                'required'             => 'true',
                'data-parsley-pattern' => '^((?!-- Select User --).)*$'
            )
            )
        );

        $params[DUPX_Paramas_Manager::PARAM_CPNL_DB_USER_TXT] = $params[DUPX_Paramas_Manager::PARAM_DB_USER]->getCopyWithNewName(
            DUPX_Paramas_Manager::PARAM_CPNL_DB_USER_TXT,
            array(
                'default' => $GLOBALS['DUPX_AC']->cpnl_dbuser
            ),
            array(
                'label'           => ' ',
                'status'          => DUPX_Param_item_form::STATUS_DISABLED,
                'attr'            => array(
                    'required'                      => 'true',
                    'data-parsley-pattern'          => '/^[a-zA-Z0-9-_]+$/',
                    'data-parsley-errors-container' => '#cpnl-dbuser-txt-error',
                    'data-parsley-cpnluser'         => "16"
                ),
                'subNote'         => '<span id="cpnl-dbuser-txt-error"></span>',
                'prefixElement'   => 'label',
                'prefixElemLabel' => '',
                'prefixElemId'    => 'cpnl-prefix-dbuser',
        ));

        $params[DUPX_Paramas_Manager::PARAM_CPNL_DB_USER_CHK] = new DUPX_Param_item_form(
            DUPX_Paramas_Manager::PARAM_CPNL_DB_USER_CHK,
            DUPX_Param_item_form::TYPE_BOOL,
            DUPX_Param_item_form::FORM_TYPE_CHECKBOX,
            array(
            'default' => false
            ),
            array(
            'label'         => ' ',
            'checkboxLabel' => 'Create New Database User'
            )
        );

        $params[DUPX_Paramas_Manager::PARAM_CPNL_DB_PASS] = $params[DUPX_Paramas_Manager::PARAM_DB_PASS]->getCopyWithNewName(
            DUPX_Paramas_Manager::PARAM_CPNL_DB_PASS,
            array(),
            array(
                'status' => DUPX_Param_item_form::STATUS_DISABLED,
                'attr'   => array(
                    'required' => 'true'
                )
        ));
    }

    /**
     * 
     * @param DUPX_Param_item[] $params
     */
    public static function initScanParams(&$params)
    {
        $archive_config = DUPX_ArchiveConfig::getInstance();

        $params[DUPX_Paramas_Manager::PARAM_URL_NEW] = new DUPX_Param_item_form(
            DUPX_Paramas_Manager::PARAM_URL_NEW,
            DUPX_Param_item_form_pass::TYPE_STRING,
            DUPX_Param_item_form_pass::FORM_TYPE_TEXT,
            array(// ITEM ATTRIBUTES
            'default'          => $GLOBALS['DUPX_ROOT_URL'],
            'sanitizeCallback' => array('DupProSnapLibUtil', 'sanitize_non_stamp_chars_and_newline'),
            'validateCallback' => function ($value) {
                return strlen($value) > 5;
            }
            ), array(// FORM ATTRIBUTES
            'label'            => 'URL:',
            'postfixElement'   => 'button',
            'postfixElemLabel' => 'get',
            'postfixBtnAction' => 'DUPX.getNewUrlByDomObj(this);'
            )
        );

        $params[DUPX_Paramas_Manager::PARAM_PATH_NEW] = new DUPX_Param_item_form(
            DUPX_Paramas_Manager::PARAM_PATH_NEW,
            DUPX_Param_item_form_pass::TYPE_STRING,
            DUPX_Param_item_form_pass::FORM_TYPE_TEXT,
            array(// ITEM ATTRIBUTES
            'default'          => $GLOBALS['DUPX_ROOT'].'/',
            'sanitizeCallback' => function ($value) {
                $value = DupProSnapLibUtil::sanitize_non_stamp_chars_newline_and_trim($value);
                return DupProSnapLibIou::safePathTrailingslashit($value);
            },
            'validateCallback'                            => function ($value) {
                return strlen($value) > 3;
            }
            ), array(// FORM ATTRIBUTES
            'label' => 'Path:'
            )
        );

        $params[DUPX_Paramas_Manager::PARAM_BLOGNAME] = new DUPX_Param_item_form(
            DUPX_Paramas_Manager::PARAM_BLOGNAME,
            DUPX_Param_item_form_pass::TYPE_STRING,
            DUPX_Param_item_form_pass::FORM_TYPE_TEXT,
            array(
            'default'          => '',
            'sanitizeCallback' => function ($value) {
                $value = DupProSnapLibUtil::sanitize_non_stamp_chars_and_newline($value);
                return empty($value) ? 'No Blog Title Set' : $value;
            }
            ), array(
            'label' => 'Title:'
            )
        );

        $params[DUPX_Paramas_Manager::PARAM_REPLACE_MODE] = new DUPX_Param_item_form(
            DUPX_Paramas_Manager::PARAM_REPLACE_MODE,
            DUPX_Param_item_form::TYPE_STRING,
            DUPX_Param_item_form::FORM_TYPE_RADIO,
            array(
            'default'      => 'legacy',
            'acceptValues' => array(
                'legacy',
                'mapping'
            )),
            array(
            'label'   => 'Replace Mode:',
            'options' => array(
                new DUPX_Param_item_form_option('legacy', 'Standard', DUPX_Param_item_form_option::OPT_ENABLED, array('title' => 'Set the files current date time to now')),
                new DUPX_Param_item_form_option('mapping', 'Mapping', DUPX_Param_item_form_option::OPT_ENABLED, array('title' => 'Keep the files date time the same'))
            ))
        );

        $params[DUPX_Paramas_Manager::PARAM_REPLACE_ENGINE] = new DUPX_Param_item_form(
            DUPX_Paramas_Manager::PARAM_REPLACE_ENGINE,
            DUPX_Param_item_form::TYPE_STRING,
            DUPX_Param_item_form::FORM_TYPE_SELECT,
            array(
            'default'      => DUPX_S3_Funcs::MODE_NORMAL,
            'acceptValues' => array(
                DUPX_S3_Funcs::MODE_NORMAL,
                DUPX_S3_Funcs::MODE_CHUNK
            )),
            array(
            'label'   => 'Engine Mode:',
            'options' => array(
                new DUPX_Param_item_form_option(DUPX_S3_Funcs::MODE_NORMAL, 'Normal'),
                new DUPX_Param_item_form_option(DUPX_S3_Funcs::MODE_CHUNK, 'Chunking mode'),
                // Prepared but not yet implemented
                new DUPX_Param_item_form_option(DUPX_S3_Funcs::MODE_SKIP, 'Skip replace database', DUPX_Param_item_form_option::OPT_HIDDEN)
            ))
        );

        $params[DUPX_Paramas_Manager::PARAM_EMPTY_SCHEDULE_STORAGE] = new DUPX_Param_item_form(
            DUPX_Paramas_Manager::PARAM_EMPTY_SCHEDULE_STORAGE,
            DUPX_Param_item_form::TYPE_BOOL,
            DUPX_Param_item_form::FORM_TYPE_CHECKBOX,
            array(
            'default' => true
            ),
            array(
            'label'         => 'Cleanup:',
            'checkboxLabel' => 'Remove schedules and storage endpoints'
            )
        );

        $params[DUPX_Paramas_Manager::PARAM_SITE_URL] = new DUPX_Param_item_form(
            DUPX_Paramas_Manager::PARAM_SITE_URL,
            DUPX_Param_item_form_pass::TYPE_STRING,
            DUPX_Param_item_form_pass::FORM_TYPE_TEXT,
            array(// ITEM ATTRIBUTES
            'default'          => $GLOBALS['DUPX_ROOT_URL'],
            'sanitizeCallback' => array('DupProSnapLibUtil', 'sanitize_non_stamp_chars_and_newline'),
            'validateCallback' => function ($value) {
                return strlen($value) > 5;
            }
            ), array(// FORM ATTRIBUTES
            'label'            => 'URL:',
            'postfixElement'   => 'button',
            'postfixElemLabel' => 'get',
            'postfixBtnAction' => 'DUPX.getNewUrlByDomObj(this);'
            )
        );

        $params[DUPX_Paramas_Manager::PARAM_URL_OLD] = new DUPX_Param_item_form(
            DUPX_Paramas_Manager::PARAM_URL_OLD,
            DUPX_Param_item_form_pass::TYPE_STRING,
            DUPX_Param_item_form_pass::FORM_TYPE_TEXT,
            array(// ITEM ATTRIBUTES
            'default'          => $archive_config->url_old,
            'sanitizeCallback' => array('DupProSnapLibUtil', 'sanitize_non_stamp_chars_and_newline'),
            'validateCallback' => function ($value) {
                return strlen($value) > 5;
            }
            ), array(// FORM ATTRIBUTES
            'status'           => DUPX_Param_item_form::STATUS_READONLY,
            'label'            => 'Old URL:',
            'postfixElement'   => 'button',
            'postfixElemLabel' => 'edit',
            'postfixBtnAction' => 'DUPX.editOldURL(this);'
            )
        );

        $params[DUPX_Paramas_Manager::PARAM_PATH_OLD] = new DUPX_Param_item_form(
            DUPX_Paramas_Manager::PARAM_PATH_OLD,
            DUPX_Param_item_form_pass::TYPE_STRING,
            DUPX_Param_item_form_pass::FORM_TYPE_TEXT,
            array(// ITEM ATTRIBUTES
            'default'          => $archive_config->wpInfo->configs->defines->DUPLICATOR_PRO_WPROOTPATH->value,
            'sanitizeCallback' => function ($value) {
                $value = DupProSnapLibUtil::sanitize_non_stamp_chars_newline_and_trim($value);
                return DupProSnapLibIou::safePathTrailingslashit($value);
            },
            'validateCallback'                            => function ($value) {
                return strlen($value) > 3;
            }
            ), array(// FORM ATTRIBUTES
            'status'           => DUPX_Param_item_form::STATUS_READONLY,
            'label'            => 'Old Path:',
            'postfixElement'   => 'button',
            'postfixElemLabel' => 'edit',
            'postfixBtnAction' => 'DUPX.editOldPath(this);'
            )
        );

        $params[DUPX_Paramas_Manager::PARAM_PLUGINS] = new DUPX_Param_item_form_plugins(
            DUPX_Paramas_Manager::PARAM_PLUGINS,
            DUPX_Param_item_form_plugins::TYPE_ARRAY_STRING,
            DUPX_Param_item_form_plugins::FORM_TYPE_PLUGINS_SELECT,
            array(
            'default'          => array(),
            'sanitizeCallback' => array('DupProSnapLibUtil', 'sanitize_non_stamp_chars_and_newline'),
            )
        );

        $params[DUPX_Paramas_Manager::PARAM_IGNORE_PLUGINS] = new DUPX_Param_item(
            DUPX_Paramas_Manager::PARAM_IGNORE_PLUGINS,
            DUPX_Param_item_form_plugins::TYPE_ARRAY_STRING,
            array(
            'default'          => array(),
            'sanitizeCallback' => array('DupProSnapLibUtil', 'sanitize_non_stamp_chars_and_newline'),
            )
        );

        $params[DUPX_Paramas_Manager::PARAM_FORCE_DIABLE_PLUGINS] = new DUPX_Param_item(
            DUPX_Paramas_Manager::PARAM_FORCE_DIABLE_PLUGINS,
            DUPX_Param_item_form_plugins::TYPE_ARRAY_STRING,
            array(
            'default'          => array(),
            'sanitizeCallback' => array('DupProSnapLibUtil', 'sanitize_non_stamp_chars_and_newline'),
            )
        );
    }

    private static function getSubSiteIdsOptions()
    {
        $archive_config = DUPX_ArchiveConfig::getInstance();
        $options        = array();
        $acceptValues   = array();
        foreach ($archive_config->subsites as $subsite) {
            $options[]      = new DUPX_Param_item_form_option($subsite->id, $subsite->blogname.' ('.$subsite->name.')');
            $acceptValues[] = $subsite->id;
        }
        return array(
            'options'      => $options,
            'acceptValues' => $acceptValues,
        );
    }

    private static function getMultisiteInstallerTypeOptions()
    {
        $archive_config = DUPX_ArchiveConfig::getInstance();
        $acceptValues   = array();
        if (!$archive_config->mu_is_filtered) {
            $acceptValues[] = 0;
        }
        if (DUPX_Conf_Utils::multisitePlusEnabled()) {
            $acceptValues[] = 1;
        }
        if (!empty($acceptValues)) {
            $default = $acceptValues[0];
        } else {
            $acceptValues[] = -1;
            $default        = -1;
        }

        if (($license = $archive_config->getLicenseType()) !== DUPX_LicenseType::BusinessGold) {
            $subNote = '* Requires Business or Gold license. This installer was created with ';
            switch ($archive_config->getLicenseType()) {
                case DUPX_LicenseType::Unlicensed:
                    $subNote .= "an Unlicensed Duplicator Pro.";
                    break;
                case DUPX_LicenseType::Personal:
                    $subNote .= "a Personal license.";
                    break;
                case DUPX_LicenseType::Freelancer:
                    $subNote .= "a Freelancer license.";
                    break;
                default:
                    $subNote .= 'an unknown license type';
            }
        } else {
            $subNote = '';
        }

        return array(
            'default'      => $acceptValues[0],
            'acceptValues' => $acceptValues,
            'subNote'      => $subNote
        );
    }

    private static function getArchiveEngineOptions()
    {
        $archive_config = DUPX_ArchiveConfig::getInstance();

        $acceptValues = array();
        $subNote      = null;
        if (($manualEnable = DUPX_Conf_Utils::isConfArkPresent()) === true) {
            $acceptValues[] = DUP_PRO_Extraction::ENGINE_MANUAL;
        } else {
            $subNote = <<<SUBNOTEHTML
*Option enabled when archive has been pre-extracted
<a href="https://snapcreek.com/duplicator/docs/faqs-tech/#faq-installer-015-q" target="_blank">[more info]</a>               
SUBNOTEHTML;
        }
        if (($zipEnable = ($archive_config->isZipArchive() && DUPX_Conf_Utils::archiveExists() && DUPX_Conf_Utils::classZipArchiveEnable())) === true) {
            $acceptValues[] = DUP_PRO_Extraction::ENGINE_ZIP;
            $acceptValues[] = DUP_PRO_Extraction::ENGINE_ZIP_CHUNK;
        }
        if (($shellZipEnable = ($archive_config->isZipArchive() && DUPX_Conf_Utils::archiveExists() && DUPX_Conf_Utils::shellExecUnzipEnable())) === true) {
            $acceptValues[] = DUP_PRO_Extraction::ENGINE_ZIP_SHELL;
        }
        if (($dupEnable = (!$archive_config->isZipArchive() && DUPX_Conf_Utils::archiveExists())) === true) {
            $acceptValues[] = DUP_PRO_Extraction::ENGINE_DUP;
        }

        $options   = array();
        $options[] = new DUPX_Param_item_form_option(DUP_PRO_Extraction::ENGINE_MANUAL,
                                                     'Manual Archive Extraction',
                                                     $manualEnable ? DUPX_Param_item_form_option::OPT_ENABLED : DUPX_Param_item_form_option::OPT_DISABLED);

        if ($archive_config->isZipArchive()) {
            //ZIP-ARCHIVE
            $options[] = new DUPX_Param_item_form_option(DUP_PRO_Extraction::ENGINE_ZIP,
                                                         'PHP ZipArchive',
                                                         $zipEnable ? DUPX_Param_item_form_option::OPT_ENABLED : DUPX_Param_item_form_option::OPT_DISABLED);

            $options[] = new DUPX_Param_item_form_option(DUP_PRO_Extraction::ENGINE_ZIP_CHUNK,
                                                         'PHP ZipArchive Chunking',
                                                         $zipEnable ? DUPX_Param_item_form_option::OPT_ENABLED : DUPX_Param_item_form_option::OPT_DISABLED);
            //SHELL-EXEC UNZIP
            $options[] = new DUPX_Param_item_form_option(DUP_PRO_Extraction::ENGINE_ZIP_SHELL,
                                                         'Shell Exec Unzip',
                                                         $shellZipEnable ? DUPX_Param_item_form_option::OPT_ENABLED : DUPX_Param_item_form_option::OPT_DISABLED);
        } else {
            // DUPARCHIVE
            $options[] = new DUPX_Param_item_form_option(DUP_PRO_Extraction::ENGINE_DUP,
                                                         'DupArchive',
                                                         $dupEnable ? DUPX_Param_item_form_option::OPT_ENABLED : DUPX_Param_item_form_option::OPT_DISABLED);
        }

        if ($zipEnable) {
            $default = DUP_PRO_Extraction::ENGINE_ZIP_CHUNK;
        } else if ($shellZipEnable) {
            $default = DUP_PRO_Extraction::ENGINE_ZIP_SHELL;
        } else if ($dupEnable) {
            $default = DUP_PRO_Extraction::ENGINE_DUP;
        } else if ($manualEnable) {
            $default = DUP_PRO_Extraction::ENGINE_MANUAL;
        } else {
            $default = null;
        }

        return array(
            'options'      => $options,
            'acceptValues' => $acceptValues,
            'default'      => $default,
            'subNote'      => $subNote
        );
    }

    /**
     * 
     * @param DUPX_Param_item_form[] $params
     */
    public static function setManagedHostParams(&$params)
    {
        $customHost     = DUPX_Custom_Host_Manager::getInstance();
        $archive_config = DUPX_ArchiveConfig::getInstance();

        if (($managedSlug = $customHost->isManaged()) === false) {
            return;
        }

        $managedObj = $customHost->getHosting($managedSlug);

        $params[DUPX_Paramas_Manager::PARAM_WP_CONFIG]->setValue('nothing');
        $params[DUPX_Paramas_Manager::PARAM_WP_CONFIG]->setFormAttr('status', DUPX_Param_item_form::STATUS_INFO_ONLY);
        $params[DUPX_Paramas_Manager::PARAM_HTACCESS_CONFIG]->setValue('nothing');
        $params[DUPX_Paramas_Manager::PARAM_HTACCESS_CONFIG]->setFormAttr('status', DUPX_Param_item_form::STATUS_INFO_ONLY);
        $params[DUPX_Paramas_Manager::PARAM_OTHER_CONFIG]->setValue('nothing');
        $params[DUPX_Paramas_Manager::PARAM_OTHER_CONFIG]->setFormAttr('status', DUPX_Param_item_form::STATUS_INFO_ONLY);

        $ovr_dbhost = DUPX_WPConfig::getValueFromLocalWpConfig('DB_HOST');
        $ovr_dbname = DUPX_WPConfig::getValueFromLocalWpConfig('DB_NAME');
        $ovr_dbuser = DUPX_WPConfig::getValueFromLocalWpConfig('DB_USER');
        $ovr_dbpass = DUPX_WPConfig::getValueFromLocalWpConfig('DB_PASSWORD');


        if (empty($ovr_dbhost) || empty($ovr_dbname) || empty($ovr_dbuser) || empty($ovr_dbpass)) {
            throw new Exception('can\'t get database connection data');
        }


        /**
         * temp check before 3.8.6 and table prefix rename
         */
        $table_prefix = DUPX_WPConfig::getValueFromLocalWpConfig('table_prefix', 'variable');
        /*
          if ($table_prefix != $archive_config->wp_tableprefix) {
          throw new Exception('can\'t install managed hosting with database with different table prefix');
          }
         */

        $params[DUPX_Paramas_Manager::PARAM_DB_HOST]->setValue($ovr_dbhost);
        $params[DUPX_Paramas_Manager::PARAM_DB_HOST]->setFormAttr('status', DUPX_Param_item_form::STATUS_INFO_ONLY);
        $params[DUPX_Paramas_Manager::PARAM_DB_NAME]->setValue($ovr_dbname);
        $params[DUPX_Paramas_Manager::PARAM_DB_NAME]->setFormAttr('status', DUPX_Param_item_form::STATUS_INFO_ONLY);
        $params[DUPX_Paramas_Manager::PARAM_DB_USER]->setValue($ovr_dbuser);
        $params[DUPX_Paramas_Manager::PARAM_DB_USER]->setFormAttr('status', DUPX_Param_item_form::STATUS_INFO_ONLY);
        $params[DUPX_Paramas_Manager::PARAM_DB_PASS]->setValue($ovr_dbpass);
        $params[DUPX_Paramas_Manager::PARAM_DB_PASS]->setFormAttr('status', DUPX_Param_item_form::STATUS_INFO_ONLY);
        $params[DUPX_Paramas_Manager::PARAM_DB_TEST_OK]->setValue(true);

        $params[DUPX_Paramas_Manager::PARAM_URL_NEW]->setFormAttr('status', DUPX_Param_item_form::STATUS_INFO_ONLY);
        $params[DUPX_Paramas_Manager::PARAM_SITE_URL]->setFormAttr('status', DUPX_Param_item_form::STATUS_INFO_ONLY);
        $params[DUPX_Paramas_Manager::PARAM_PATH_NEW]->setFormAttr('status', DUPX_Param_item_form::STATUS_INFO_ONLY);

        $managedObj->setCustomParams();
    }
}