<?php
defined("DUPXABSPATH") or die("");
/** IDE HELPERS */
/* @var $GLOBALS['DUPX_AC'] DUPX_ArchiveConfig */
/* @var $state DUPX_InstallerState */

$state = $GLOBALS['DUPX_STATE'];

if ($state->mode == DUPX_InstallerMode::OverwriteInstall) {
    $ovr_dbhost = DUPX_WPConfig::getValueFromLocalWpConfig('DB_HOST');
    $ovr_dbname = DUPX_WPConfig::getValueFromLocalWpConfig('DB_NAME');
    $ovr_dbuser = DUPX_WPConfig::getValueFromLocalWpConfig('DB_USER');
    $ovr_dbpass = DUPX_WPConfig::getValueFromLocalWpConfig('DB_PASSWORD');
} else {
    $ovr_dbhost = '';
    $ovr_dbname = '';
    $ovr_dbuser = '';
    $ovr_dbpass = '';
}

DUPX_View_S2::basicPanel();
?>
<script>
    /**
     *  Bacic Action Change  */
    var dbActionInputId = <?php echo DupProSnapJsonU::wp_json_encode($paramsManager->getFormItemId(DUPX_Paramas_Manager::PARAM_DB_ACTION)); ?>;
    var dbHostInputId = <?php echo DupProSnapJsonU::wp_json_encode($paramsManager->getFormItemId(DUPX_Paramas_Manager::PARAM_DB_HOST)); ?>;
    var dbNameInputId = <?php echo DupProSnapJsonU::wp_json_encode($paramsManager->getFormItemId(DUPX_Paramas_Manager::PARAM_DB_NAME)); ?>;
    var dbUserInputId = <?php echo DupProSnapJsonU::wp_json_encode($paramsManager->getFormItemId(DUPX_Paramas_Manager::PARAM_DB_USER)); ?>;
    var dbPassInputId = <?php echo DupProSnapJsonU::wp_json_encode($paramsManager->getFormItemId(DUPX_Paramas_Manager::PARAM_DB_PASS)); ?>;


    DUPX.basicDBActionChange = function ()
    {
        var action = $('#' + dbActionInputId).val();
        $('.s2-basic-pane .s2-warning-manualdb').hide();
        $('.s2-basic-pane .s2-warning-emptydb').hide();
        $('.s2-basic-pane .s2-warning-renamedb').hide();
        switch (action)
        {
            case 'create'  :
                break;
            case 'empty'   :
                $('.s2-basic-pane .s2-warning-emptydb').show(300);
                break;
            case 'rename'  :
                $('.s2-basic-pane .s2-warning-renamedb').show(300);
                break;
            case 'manual'  :
                $('.s2-basic-pane .s2-warning-manualdb').show(300);
                break;
        }
    };

//DOCUMENT INIT
    $(document).ready(function ()
    {
        $("#" + dbActionInputId).on("change", DUPX.basicDBActionChange);
        DUPX.basicDBActionChange();

        DUPX.checkOverwriteParameters = function (dbhost, dbname, dbuser, dbpass)
        {
            $("#" + dbHostInputId).val(<?php echo DupProSnapJsonU::wp_json_encode($ovr_dbhost); ?>).prop('readonly', true);
            $("#" + dbNameInputId).val(<?php echo DupProSnapJsonU::wp_json_encode($ovr_dbname); ?>).prop('readonly', true);
            $("#" + dbUserInputId).val(<?php echo DupProSnapJsonU::wp_json_encode($ovr_dbuser); ?>).prop('readonly', true);
            $("#" + dbPassInputId).val(<?php echo DupProSnapJsonU::wp_json_encode($ovr_dbpass); ?>).prop('readonly', true);
            $("#s2-db-basic-setup").show();
        };

        DUPX.fillInPlaceHolders = function ()
        {
            $("#" + dbHostInputId).attr('placeholder', <?php echo DupProSnapJsonU::wp_json_encode($ovr_dbhost); ?>).prop('readonly', false);
            $("#" + dbNameInputId).attr('placeholder', <?php echo DupProSnapJsonU::wp_json_encode($ovr_dbname); ?>).prop('readonly', false);
            $("#" + dbUserInputId).attr('placeholder', <?php echo DupProSnapJsonU::wp_json_encode($ovr_dbuser); ?>).prop('readonly', false);
            $("#" + dbPassInputId).attr('placeholder', <?php echo DupProSnapJsonU::wp_json_encode($ovr_dbpass); ?>).prop('readonly', false);
        };

        DUPX.resetParameters = function ()
        {
            $("#" + dbHostInputId).val('').attr('placeholder', '').prop('readonly', false);
            $("#" + dbNameInputId).val('').attr('placeholder', '').prop('readonly', false);
            $("#" + dbUserInputId).val('').attr('placeholder', '').prop('readonly', false);
            $("#" + dbPassInputId).val('').attr('placeholder', '').prop('readonly', false);
        };

<?php if ($state->mode == DUPX_InstallerMode::OverwriteInstall) : ?>
        DUPX.fillInPlaceHolders();
<?php endif; ?>
    });
</script>
