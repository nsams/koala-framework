<?php
class Kwc_Form_Wizard_Component extends Kwc_Abstract_Composite_Component
{
    public static function getSettings($param)
    {
        $ret = parent::getSettings($param);
        //$ret['generators']['child']['component']['form1'] = 'Form2_Component';
        //$ret['generators']['child']['component']['form2'] = 'Form1_Component';
        $ret['viewCache'] = false;
        return $ret;
    }

    public function getTemplateVars(Kwf_Component_Renderer_Abstract $renderer = null)
    {
        $ret = parent::getTemplateVars($renderer);
        foreach ($ret['keys'] as $key) {
            if ($ret[$key]->getComponent()->isPosted()) {
                $ret['currentForm'] = $ret[$key];
            }
        }

        if (!isset($ret['currentForm'])) {
            $ret['currentForm'] = $ret[$ret['keys'][0]];
        }
        return $ret;
    }
}
