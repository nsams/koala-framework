<?php
class Kwc_Basic_Text_Image_Component extends Kwc_Basic_Image_Component
{
    public static function getSettings($param)
    {
        return array_merge(parent::getSettings($param),
            array('allowBlank' => false,
                  'dimension'  => array(),
            ));
    }
}
