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
        global $user_rights,$lang;
        if (!(PAGE==='DataEntry/record_home.php' && isset($_GET['id']))) return;
        try {
            $user = $this->getUser();
        } catch (\Exception $e) {
            return; // e.g. not logged in
        }
        $record = $this->escape($_GET['id']);
        $links = array();
        if ($user_rights['data_logging'] || $user->isSuperUser()) $links[] = '<li class="ui-menu-item"><a target="_blank" href="'.APP_PATH_WEBROOT.'Logging/index.php?pid='.$project_id.'&record='.$record.'"                                  style="display:block;" tabindex="-1" role="menuitem" class="ui-menu-item-wrapper"><span style="vertical-align:middle;color:#000066;"><i class="fas fa-receipt   mr-1"></i>'.\RCView::tt('app_07'    ).'<i class="fas fa-external-link-alt ml-1"></i></span></a></li>';
        if ($user_rights['alerts']       || $user->isSuperUser()) $links[] = '<li class="ui-menu-item"><a target="_blank" href="'.APP_PATH_WEBROOT.'index.php?pid='.$project_id.'&route=AlertsController:setup&log=1&filterRecord='.$record.'" style="display:block;" tabindex="-1" role="menuitem" class="ui-menu-item-wrapper"><span style="vertical-align:middle;color:#000066;"><i class="fas fa-table     mr-1"></i>'.\RCView::tt('alerts_20' ).'<i class="fas fa-external-link-alt ml-1"></i></span></a></li>';
        if ($user_rights['participants'] || $user->isSuperUser()) $links[] = '<li class="ui-menu-item"><a target="_blank" href="'.APP_PATH_WEBROOT.'Surveys/invite_participants.php?pid='.$project_id.'&email_log=1&filterRecord='.$record.'"  style="display:block;" tabindex="-1" role="menuitem" class="ui-menu-item-wrapper"><span style="vertical-align:middle;color:#000066;"><i class="fas fa-mail-bulk mr-1"></i>'.\RCView::tt('survey_350').'<i class="fas fa-external-link-alt ml-1"></i></span></a></li>';
        echo '<script type="text/javascript">';
        echo '  /* '.$this->PREFIX.' '.$this->VERSION.': links to logging pages */';
        echo '  $(document).ready(function(){';
        foreach ($links as $link) {
            echo "    $('#recordActionDropdown').append('$link');";
        }
        echo '  });';
        echo '</script>';
    }
}