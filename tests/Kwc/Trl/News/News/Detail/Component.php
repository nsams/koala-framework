<?php
class Kwc_Trl_News_News_Detail_Component extends Kwc_News_Detail_Abstract_Component
{
    public static function getSettings($param)
    {
        $ret = parent::getSettings($param);
        $ret['generators']['child']['component']['content'] = 'Kwc_Basic_None_Component';
        return $ret;
    }
}
