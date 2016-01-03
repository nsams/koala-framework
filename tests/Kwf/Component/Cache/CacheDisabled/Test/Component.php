<?php
class Kwf_Component_Cache_CacheDisabled_Test_Component extends Kwc_Abstract_Composite_Component
{
    public static $test = 'foo';

    public static function getSettings($param)
    {
        $ret = parent::getSettings($param);
        $ret['viewCache'] = false;
        return $ret;
    }

    public function getTemplateVars(Kwf_Component_Renderer_Abstract $renderer = null)
    {
        $ret = parent::getTemplateVars($renderer);
        $ret['test'] = self::$test;
        return $ret;
    }
}
