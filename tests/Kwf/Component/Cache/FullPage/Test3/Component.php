<?php
class Kwf_Component_Cache_FullPage_Test3_Component extends Kwc_Abstract
{
    public static function getSettings($param)
    {
        $ret = parent::getSettings($param);
        $ret['ownModel'] = 'Kwf_Component_Cache_FullPage_Test3_Model';
        return $ret;
    }
}
