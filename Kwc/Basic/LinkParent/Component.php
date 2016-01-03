<?php
class Kwc_Basic_LinkParent_Component extends Kwc_Basic_Link_Component
{
    public static function getSettings($param)
    {
        $ret = parent::getSettings($param);
        $ret['componentName'] = trlKwfStatic('Link to parent page');
        $ret['generators']['child']['component']['linkTag'] =
            'Kwc_Basic_LinkTag_ParentPage_Component';
        $ret['ownModel'] = 'Kwc_Basic_LinkParent_Model';
        $ret['cssClass'] = 'webStandard';
        return $ret;
    }
}
