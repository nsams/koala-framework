<?php
class Kwc_Blog_Category_Detail_Component extends Kwc_Directories_Category_Detail_Component
{
    public static function getSettings($param)
    {
        $ret = parent::getSettings($param);
        $ret['generators']['child']['component']['list'] = 'Kwc_Blog_Category_Detail_List_Component';
        $ret['flags']['hasComponentLinkModifiers'] = false;
        return $ret;
    }
}
