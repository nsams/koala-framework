<?php
class Kwc_IncludeCode_Page1_Component extends Kwc_Abstract
{
    public static function getSettings($param)
    {
        $ret = parent::getSettings($param);
        $ret['flags']['hasHeaderIncludeCode'] = true;
        return $ret;
    }

    public function getIncludeCode()
    {
        return '<meta name="test" content="foo" />';
    }
}
