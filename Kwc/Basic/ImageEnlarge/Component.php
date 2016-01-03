<?php
class Kwc_Basic_ImageEnlarge_Component extends Kwc_Basic_Image_Component
{
    public static function getSettings($param)
    {
        $ret = parent::getSettings($param);
        $ret['componentName'] = trlKwfStatic('Image Enlarge');
        $ret['componentIcon'] = 'imageEnlarge';
        $ret['generators']['child']['component']['linkTag'] = 'Kwc_Basic_ImageEnlarge_EnlargeTag_Component';
        $ret['generators']['child']['addUrlPart'] = false;
        $ret['assetsAdmin']['files'][] = 'kwf/Kwc/Basic/ImageEnlarge/ImageUploadField.js';
        $ret['cssClass'] = Kwf_Config::getValue('kwc.imageEnlarge.cssClass'); //default is showHoverIcon
        return $ret;
    }
}
