<?php
class Kwc_Box_Favicon_Component extends Kwc_Basic_Image_Component
{
    public static function getSettings($param)
    {
        $ret = parent::getSettings($param);
        $ret['componentName'] = trlKwfStatic('Favicon');
        $ret['dimensions'] = array(
            array('width'=>0, 'height'=>0, 'cover' => true)
        );
        return $ret;
    }

    public function getTemplateVars(Kwf_Component_Renderer_Abstract $renderer = null)
    {
        $ret = parent::getTemplateVars($renderer);
        $ret['imageUrl'] = $this->getImageUrl();
        return $ret;
    }
}
