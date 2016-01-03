<?php
class Kwc_Advanced_LightboxParagraphs_Component extends Kwc_Paragraphs_Component
{
    public static function getSettings($param)
    {
        $ret = parent::getSettings($param);
        $ret['componentName'] = trlKwfStatic('Paragraphs in Lightbox');
        $ret['contentSender'] = 'Kwf_Component_Abstract_ContentSender_Lightbox';

        $ret['contentWidth'] = 600;
        return $ret;
    }
}