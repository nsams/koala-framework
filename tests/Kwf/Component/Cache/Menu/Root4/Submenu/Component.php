<?php
class Kwf_Component_Cache_Menu_Root4_Submenu_Component extends Kwc_Menu_Component
{
    public static function getSettings($param)
    {
        $ret = parent::getSettings($param);
        $ret['level'] = 2;
        return $ret;
    }
}
