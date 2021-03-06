<?php
class Kwf_Component_Abstract_MenuConfig_SameClass extends Kwf_Component_Abstract_MenuConfig_Abstract
{
    protected function _getResourceText(Kwf_Component_Data $c)
    {
        if (Kwc_Abstract::hasSetting($this->_class, 'componentNameShort')) {
            $name = Kwc_Abstract::getSetting($this->_class, 'componentNameShort');
        } else {
            $name = Kwc_Abstract::getSetting($this->_class, 'componentName');
        }

        $t = $c->getTitle();
        if (!$t) $t = $name;
        if ($domain = $c->getParentByClass('Kwc_Root_DomainRoot_Domain_Component')) {
            $t .= " ($domain->name)";
        }
        return $t;
    }
    protected function _getParentResource(Kwf_Acl $acl)
    {
        return 'kwf_component_root';
    }

    public function addResources(Kwf_Acl $acl)
    {
        $components = Kwf_Component_Data_Root::getInstance()
                ->getComponentsBySameClass($this->_class, array('ignoreVisible'=>true));
        if (Kwc_Abstract::hasSetting($this->_class, 'componentNameShort')) {
            $name = Kwc_Abstract::getSetting($this->_class, 'componentNameShort');
        } else {
            $name = Kwc_Abstract::getSetting($this->_class, 'componentName');
        }
        $icon = Kwc_Abstract::getSetting($this->_class, 'componentIcon');
        if (count($components)) {
            $dropdownName = 'kwc_'.$this->_class;
            if (!$acl->has($dropdownName)) {
                $dropDown = new Kwf_Acl_Resource_MenuDropdown(
                        $dropdownName, array('text'=>$name, 'icon'=>$icon)
                    );
                $dropDown->setCollapseIfSingleChild(true);
                $acl->add($dropDown, $this->_getParentResource($acl));
            }
            foreach ($components as $c) {
                $t = $this->_getResourceText($c);
                $acl->add(
                    new Kwf_Acl_Resource_Component_MenuUrl(
                        $c, array('text'=>$t, 'icon'=>$icon)
                    ), $dropdownName
                );
            }
        }
    }

    public function getEventsClass()
    {
        return 'Kwf_Component_Abstract_MenuConfig_SameClass_Events';
    }
}
