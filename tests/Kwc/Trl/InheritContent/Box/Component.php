<?php
class Kwc_Trl_InheritContent_Box_Component extends Kwc_Box_InheritContent_Component
{
    public static function getSettings($param)
    {
        $ret = parent::getSettings($param);
        $ret['generators']['child']['component'] = 'Kwc_Trl_InheritContent_Box_Child_Component';
        return $ret;
    }
}
