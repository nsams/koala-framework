<?php
class Kwc_Trl_Menu_Menu_Component extends Kwc_Menu_Component
{
    public static function getSettings()
    {
        $ret = parent::getSettings();
        $ret['generators']['subMenu']['component'] = 'Kwc_Trl_Menu_Menu_SubMenu_Component';
        unset($ret['dataModel']);
        $ret['level'] = 'main';
        return $ret;
    }
}
