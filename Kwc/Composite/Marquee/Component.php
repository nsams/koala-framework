<?php
class Kwc_Composite_Marquee_Component extends Kwc_Abstract_Composite_Component
{
    public static function getSettings($param)
    {
        $ret = parent::getSettings($param);
        $ret['componentName'] = trlKwfStatic('Marquee');
        $ret['assets']['dep'][] = 'KwfMarqueeElements';

        $ret['selector'] = '> div';

        return $ret;
    }

    public function getTemplateVars(Kwf_Component_Renderer_Abstract $renderer = null)
    {
        $ret = parent::getTemplateVars($renderer);
        $ret['settings'] = $this->_getSettings();
        return $ret;
    }

    protected function _getSettings()
    {
        $ret = array();
        $ret['selector'] = $this->_getSetting('selector');
        $ret['scrollDelay'] = 50;
        $ret['scrollAmount'] = 1;
        $ret['scrollDirection'] = 'up';
        return $ret;
    }
}
