<?php
class Kwc_Advanced_YoutubeWithPreviewImage_Component extends Kwc_Advanced_Youtube_Component
{
    public static function getSettings($param)
    {
        $ret = parent::getSettings($param);
        $ret['cssClass'] = 'webStandard';
        $ret['componentName'] = trlKwfStatic('Youtube with Teaser Image');
        $ret['generators']['child']['component']['previewImage'] = 'Kwc_Advanced_YoutubeWithPreviewImage_Image_Component';
        return $ret;
    }
}
