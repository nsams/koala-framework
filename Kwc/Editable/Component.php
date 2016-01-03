<?php
class Kwc_Editable_Component extends Kwc_Abstract
{
    public static function getSettings($param)
    {
        $ret = parent::getSettings($param);
        $ret['menuConfig'] = 'Kwc_Editable_MenuConfig';
        $ret['generators']['content'] = array(
            'class' => 'Kwf_Component_Generator_Static',
            'component' => 'Kwc_Paragraphs_Component'
        );
        $ret['assetsAdmin']['files'][] = 'kwf/Kwc/Editable/Panel.js';
        $ret['assetsAdmin']['dep'][] = 'ExtPanel';
        return $ret;
    }

    // Wird hier verwendet: Kwc_Editable_ComponentsModel
    public function getNameForEdit()
    {
        return Kwf_Trl::getInstance()->trlStaticExecute(Kwc_Abstract::getSetting($this->getData()->componentClass, 'componentName'));
    }

    public function getTemplateVars(Kwf_Component_Renderer_Abstract $renderer = null)
    {
        $ret = parent::getTemplateVars($renderer);
        $ret['content'] = $this->getData()->getChildComponent('-content');
        return $ret;
    }

    public function hasContent()
    {
        return $this->getData()->getChildComponent('-content')->hasContent();
    }
}
