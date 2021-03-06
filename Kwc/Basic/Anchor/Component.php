<?php
class Kwc_Basic_Anchor_Component extends Kwc_Abstract_Composite_Component
{
    public static function getSettings()
    {
        $ret = parent::getSettings();
        $ret['componentIcon'] = 'anchor';
        $ret['componentName'] = trlKwfStatic('Anchor');
        $ret['componentCategory'] = 'layout';
        $ret['componentPriority'] = 70;
        $ret['ownModel'] = 'Kwf_Component_FieldModel';
        $ret['flags']['hasAnchors'] = true;
        return $ret;
    }

    public function getTemplateVars()
    {
        $ret = parent::getTemplateVars();
        $ret['name'] = $this->getRow()->anchor ? $this->getRow()->anchor : null;
        return $ret;
    }

    public function getAnchors()
    {
        return array($this->getData()->componentId => $this->getRow()->anchor);
    }
}
