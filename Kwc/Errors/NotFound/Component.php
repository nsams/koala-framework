<?php
class Kwc_Errors_NotFound_Component extends Kwc_Abstract
{
    public static function getSettings($param)
    {
        $ret = parent::getSettings($param);
        $ret['viewCache'] = false;
        return $ret;
    }
    
    public function getTemplateVars(Kwf_Component_Renderer_Abstract $renderer = null)
    {
        $ret = parent::getTemplateVars($renderer);
        $ret['requestUri'] = $_SERVER['REQUEST_URI']; // TODO
        return $ret;
    }
}
