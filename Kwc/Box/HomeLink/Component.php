<?php
class Kwc_Box_HomeLink_Component extends Kwc_Abstract_Composite_Component
{
    public static function getSettings($param)
    {
        $ret = parent::getSettings($param);
        $ret['cssClass'] = 'webStandard';
        $ret['placeholder']['linkText'] = trlKwfStatic('Home');
        return $ret;
    }

    public function getTemplateVars(Kwf_Component_Renderer_Abstract $renderer = null)
    {
        $ret = parent::getTemplateVars($renderer);
        $ret['home'] = $this->getData()->getSubroot()->getChildPage(array('home' => true), array());
        return $ret;
    }
}
