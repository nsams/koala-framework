<?php
/**
 * Component that allows dynamic content without having to disable view cache.
 */
abstract class Kwc_Advanced_DynamicContent_Component extends Kwc_Abstract
{
    public static function getSettings($param)
    {
        $ret = parent::getSettings($param);
        $ret['plugins'][] = 'Kwc_Advanced_DynamicContent_Plugin';
        return $ret;
    }

    public function getTemplateVars(Kwf_Component_Renderer_Abstract $renderer = null)
    {
        $ret = parent::getTemplateVars($renderer);
        $language = $this->getData()->getLanguage();
        $ret['dynamicPlaceholder'] = '{dynamicContent '.$this->getData()->componentClass.' '. $language .'}';
        return $ret;
    }

    abstract public static function getDynamicContent($componentId, $componentClass);
}
