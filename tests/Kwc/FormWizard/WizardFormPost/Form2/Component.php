<?php
class Kwc_FormWizard_WizardFormPost_Form2_Component extends Kwc_Form_Component
{
    public static function getSettings($param)
    {
        $ret = parent::getSettings($param);
        $ret['useAjaxRequest'] = false;
        return $ret;
    }
}
