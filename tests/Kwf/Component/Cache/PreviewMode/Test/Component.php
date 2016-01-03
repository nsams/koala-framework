<?php
class Kwf_Component_Cache_PreviewMode_Test_Component extends Kwc_Abstract_Composite_Component
{
    public static function getSettings($param)
    {
        $ret = parent::getSettings($param);
        return $ret;
    }

    public function getTemplateVars(Kwf_Component_Renderer_Abstract $renderer = null)
    {
        $ret = parent::getTemplateVars($renderer);
        $ret['test'] = Kwf_Component_Data_Root::getShowInvisible() ? 'foo' : 'bar';
        return $ret;
    }
}
