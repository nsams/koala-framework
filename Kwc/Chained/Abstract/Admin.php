<?php
class Kwc_Chained_Abstract_Admin extends Kwc_Admin
{
    public function componentToString(Kwf_Component_Data $data)
    {
        $admin = Kwc_Admin::getInstance(Kwc_Abstract::getSetting($this->_class, 'masterComponentClass'));
        return $admin->componentToString($data->chained);
    }
}