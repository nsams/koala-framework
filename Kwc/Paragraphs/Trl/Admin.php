<?php
class Kwc_Paragraphs_Trl_Admin extends Kwc_Chained_Abstract_Admin
{
    public function componentToString(Kwf_Component_Data $data)
    {
        return Kwf_Trl::getInstance()->trlStaticExecute(Kwc_Abstract::getSetting($data->componentClass, 'componentName'));
    }
}
