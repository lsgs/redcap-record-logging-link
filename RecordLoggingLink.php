<?php
/**
 * REDCap External Module: Record Logging Link
 * Adds a link to the Logging page with record filter to the Record Actions dropdown on the Record Home page.
 * @author Luke Stevens, Murdoch Children's Research Institute
 */
namespace MCRI\RecordLoggingLink;

use ExternalModules\AbstractExternalModule;

class RecordLoggingLink extends AbstractExternalModule
{
    public function redcap_every_page_top($project_id) {
        global $user_rights;
        if (!(PAGE==='DataEntry/record_home.php' && isset($_GET['id']))) return;
        try {
            $user = $this->getUser();
        } catch (\Exception $e) {
            return; // e.g. not logged in
        }
        if (!($user_rights['data_logging'] || $user->isSuperUser())) return;
        $record = \htmlspecialchars($_GET['id'], ENT_QUOTES);
        ?>
        <script type="text/javascript">
            $(document).ready(function(){
                $('#recordActionDropdown').append(
                    '<li class="ui-menu-item"><a target="_blank" href="'+app_path_webroot+'Logging/index.php?pid=<?=$project_id?>&record=<?=$record?>" style="display:block;" tabindex="-1" role="menuitem" class="ui-menu-item-wrapper"><span style="vertical-align:middle;color:#000066;"><i class="fas fa-receipt mr-1"></i><?=\RCView::tt('app_07')?><i class="fas fa-external-link-alt ml-1"></i></span></a></li>'
                )
            });
        </script>
        <?php
    }
}
?>
