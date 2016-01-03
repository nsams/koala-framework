<?php
class Kwc_ListChildPages_Teaser_TeaserWithChild_Component extends Kwc_List_ChildPages_Teaser_Component
{
    public static function getSettings($param)
    {
        $ret = parent::getSettings($param);
        $ret['generators']['child']['component'] = 'Kwc_ListChildPages_Teaser_TeaserWithChild_Child_Component';
        $ret['childModel'] = 'Kwc_ListChildPages_Teaser_TestModel';
        return $ret;
    }
    public function getTemplateVars(Kwf_Component_Renderer_Abstract $renderer = null)
    {
        $ret = parent::getTemplateVars($renderer);
        return $ret;
    }
}
