<?php
class Kwc_FulltextSearch_Search_Directory_Component extends Kwc_Directories_Item_Directory_Component implements Kwf_Util_Maintenance_JobProviderInterface
{
    public static function getSettings($param)
    {
        $ret = parent::getSettings($param);
        $ret['componentName'] = trlKwfStatic('Fulltext Search');
        $ret['generators']['detail']['class'] = 'Kwc_FulltextSearch_Search_Directory_Generator';
        $ret['generators']['detail']['component'] = 'Kwc_FulltextSearch_Search_Detail_Component';
        $ret['generators']['child']['component']['view'] = 'Kwc_FulltextSearch_Search_ViewAjax_Component';
        $ret['childModel'] = 'Kwc_FulltextSearch_Search_Directory_Model';
        $ret['flags']['usesFulltext'] = true;
        $ret['updateTags'] = array('fulltext');
        $ret['extConfig'] = 'Kwf_Component_Abstract_ExtConfig_None';
        return $ret;
    }

    public static function getMaintenanceJobs()
    {
        return array(
            'Kwc_FulltextSearch_Search_Directory_MaintenanceJobs_CheckContents',
            'Kwc_FulltextSearch_Search_Directory_MaintenanceJobs_UpdateChanged',
        );
    }
}
