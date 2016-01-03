<?php
class Kwf_Component_Cache_Visible_Root_Child_Component extends Kwc_Abstract
{
    public static function getSettings($param)
    {
        $ret = parent::getSettings($param);
        $ret['ownModel'] = 'Kwf_Component_Cache_Visible_Root_Child_Model';
        return $ret;
    }
}
