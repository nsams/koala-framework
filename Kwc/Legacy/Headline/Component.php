<?php
class Kwc_Legacy_Headline_Component extends Kwc_Abstract
{
    public static function getSettings()
    {
        $ret = array_merge(parent::getSettings(), array(
            'componentName' => trlKwfStatic('Headline'),
            'componentIcon' => 'text_padding_top',
            'ownModel'      => 'Kwf_Component_FieldModel',
            'cssClass'      => 'kwfup-webStandard',
            'extConfig'     => 'Kwf_Component_Abstract_ExtConfig_Form'
        ));
        $ret['throwHasContentChangedOnRowColumnsUpdate'] = array('headline1');
        return $ret;
    }

    public function getTemplateVars()
    {
        $ret = parent::getTemplateVars();
        $ret['headline1'] = $this->_getRow()->headline1;
        return $ret;
    }

    public function hasContent()
    {
        if (trim($this->_getRow()->headline1) != "" ) {
            return true;
        }
        return false;
    }
}
