<?php
class Kwc_Lightbox_Content_TestComponent_Component extends Kwc_Abstract
{
    public static function getSettings($param)
    {
        $ret = parent::getSettings($param);
        $ret['contentSender'] = 'Kwf_Component_Abstract_ContentSender_Lightbox';
        $ret['assets']['dep'][] = 'KwfLightbox';
        return $ret;
    }
}
