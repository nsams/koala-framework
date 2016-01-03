<?php
class Kwc_ImageResponsive_CreatesImgElement_Components_TextImage_Text_Component extends Kwc_Basic_Text_Component
{
    public static function getSettings($param)
    {
        $ret = parent::getSettings($param);
        $ret['ownModel'] = 'Kwc_ImageResponsive_CreatesImgElement_Components_TextImage_Text_Model';
        $ret['stylesModel'] = 'Kwc_ImageResponsive_CreatesImgElement_Components_TextImage_Text_TestStylesModel';
        $ret['generators']['child']['model'] = 'Kwc_ImageResponsive_CreatesImgElement_Components_TextImage_Text_ChildComponentsModel';
        $ret['generators']['child']['component'] = array(
            'image'         => null,
            'link'          => null,
            'download'      => null
        );
        return $ret;
    }

}
