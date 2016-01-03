<?php
class Kwf_Component_Cache_FullPage_Test1_Component extends Kwc_Abstract
{
    public static function getSettings($param)
    {
        $ret = parent::getSettings($param);
        return $ret;
    }


    public function getTemplateVars(Kwf_Component_Renderer_Abstract $renderer = null)
    {
        $ret = parent::getTemplateVars($renderer);
        $ret['test2'] = $this->getData()->parent->getChildComponent('_test2');
        $ret['test3'] = $this->getData()->parent->getChildComponent('_test3');
        return $ret;
    }
}
