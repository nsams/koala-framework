<?php
class Kwc_Basic_None_Component extends Kwc_Abstract
{
    public static function getSettings($param)
    {
        $ret = parent::getSettings($param);
        $ret['componentName'] = trlKwfStatic('None');
        return $ret;
    }

    public function hasContent()
    {
        return false;
    }
}
