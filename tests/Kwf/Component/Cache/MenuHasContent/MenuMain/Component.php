<?php
class Kwf_Component_Cache_MenuHasContent_MenuMain_Component extends Kwc_Menu_Component
{
    public static function getSettings($param)
    {
        $ret = parent::getSettings($param);
        $ret['level'] = 'main';
        return $ret;
    }
}
