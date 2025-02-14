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
    const LINK_MARKUP = '<li class="ui-menu-item"><a target="_blank" href="||HREF||"  style="display:block;" tabindex="-1" role="menuitem" class="ui-menu-item-wrapper"><span style="vertical-align:middle;color:#000066;">||LABEL||<i class="fas fa-external-link-alt fs10 ml-2"></i></span></a></li>';
    const HREFMARK = '||HREF||';
    const LABELMARK = '||LABEL||';
    
    public function redcap_every_page_top($project_id) {
        global $user_rights,$Proj;
        if (!(PAGE==='DataEntry/record_home.php' && isset($_GET['id']))) return;
        try {
            $user = $this->getUser();
        } catch (\Exception $e) {
            return; // e.g. not logged in
        }
        $record = $this->escape($_GET['id']);
        $arm = $this->escape($_GET['arm']);
        $links = array();

        if (\REDCap::versionCompare(REDCAP_VERSION,'15.0.2','<=') && ($user_rights['data_logging'] || $user->isSuperUser())) {
            $links[] = array(
                'href' => APP_PATH_WEBROOT.'Logging/index.php?pid='.$project_id.'&record='.$record,
                'label' => '<i class="fas fa-receipt   mr-1"></i>'.\RCView::tt('app_07')
            );
        }
        
        if (\REDCap::versionCompare(REDCAP_VERSION,'15.0.2','<=') && ($user_rights['alerts'] || $user->isSuperUser())) {
            $links[] = array(
                'href' => APP_PATH_WEBROOT.'index.php?pid='.$project_id.'&route=AlertsController:setup&log=1&filterRecord='.$record,
                'label' => '<i class="fas fa-table mr-1"></i>'.\RCView::tt('alerts_20')
            );
        }

        if (\REDCap::versionCompare(REDCAP_VERSION,'15.0.2','<=') && $Proj->project['surveys_enabled'] && ($user_rights['participants'] || $user->isSuperUser())) {
            $links[] = array(
                'href' => APP_PATH_WEBROOT.'Surveys/invite_participants.php?pid='.$project_id.'&email_log=1&filterRecord='.$record,
                'label' => '<i class="fas fa-mail-bulk mr-1"></i>'.\RCView::tt('survey_350')
            );
        }

        if ($Proj->project['scheduling'] && ($user_rights['calendar'] || $user->isSuperUser())) {
            // Does the record have a schedule already?
            $sql = "select count(*) as event_count
                    from redcap_events_calendar ec
                    inner join redcap_events_metadata em on ec.event_id=em.event_id
                    inner join redcap_events_arms ea on em.arm_id=ea.arm_id and ec.project_id=ea.project_id
                    where ec.project_id = ? and record = ? and arm_num = ?";
            $result = $this->query($sql, [$project_id, $record, $arm])->fetch_assoc();

            if ($result['event_count'] == 0) {
                //scheduling_03 = "Create Schedule"
                $links[] = array(
                    'href' => APP_PATH_WEBROOT.'Calendar/scheduling.php?pid='.$project_id,
                    'label' => '<i class="fas fa-mail-bulk mr-1"></i>'.\RCView::tt('scheduling_03')
                );
            } else {
                //scheduling_04 = "View or Edit Schedule"
                $links[] = array(
                    'href' => APP_PATH_WEBROOT.'Calendar/scheduling.php?pid='.$project_id.'&record='.$record.'&arm='.$arm,
                    'label' => '<i class="fas fa-mail-bulk mr-1"></i>'.\RCView::tt('scheduling_04')
                );
            }
        }

        if ($user_rights['reports'] || $user->isSuperUser()) {
            // report_builder_44 = "View Report" report_builder_80 = "All data"
            $links[] = array(
                'href' => APP_PATH_WEBROOT.'DataExport/index.php?pid='.$project_id.'&report_id=ALL&pagenum=1&lf1='.$record,
                'label' => '<i class="fas fa-mail-bulk mr-1"></i>'.\RCView::tt('report_builder_44').\RCView::tt('colon').' '.\RCView::tt('report_builder_80')
            );
        }

        if (count($links)===0) return;

        echo '<script type="text/javascript">';
        echo '  /* '.$this->PREFIX.' '.$this->VERSION.': links to logging pages */';
        echo '  $(document).ready(function(){';
        foreach ($links as $link) {
            $linkMarkup = str_replace(static::HREFMARK, $link['href'], str_replace(static::LABELMARK, $link['label'], static::LINK_MARKUP));
            echo "    $('#recordActionDropdown').append('$linkMarkup');";
        }
        echo '  });';
        echo '</script>';
    }
}