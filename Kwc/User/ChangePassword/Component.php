<?php
class Kwc_User_ChangePassword_Component extends Kwc_Abstract_Composite_Component
{
    public static function getSettings($param)
    {
        $ret = parent::getSettings($param);
        $ret['generators']['child']['component']['form'] = 'Kwc_User_ChangePassword_Form_Component';
        return $ret;
    }
}
