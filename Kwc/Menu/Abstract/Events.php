<?php
class Kwc_Menu_Abstract_Events extends Kwc_Abstract_Events
{
    public function getListeners()
    {
        $ret = parent::getListeners();
        $ret[] = array(
            'class' => null,
            'event' => 'Kwf_Component_Event_Page_Added',
            'callback' => 'onPageAddOrRemove'
        );
        $ret[] = array(
            'class' => null,
            'event' => 'Kwf_Component_Event_Page_Removed',
            'callback' => 'onPageAddOrRemove'
        );
        return $ret;
    }

    public function onPageAddOrRemove(Kwf_Component_Event_Page_Abstract $event)
    {
        $menuLevel = Kwc_Abstract::getSetting($this->_class, 'level');
        foreach (Kwf_Component_Data_Root::getInstance()->getComponentsByDbId($event->dbId) as $data) {
            $level = 0;
            while ($data && !Kwc_Abstract::getFlag($data->componentClass, 'menuCategory')) {
                if ($data->isPage) $level++;
                $data = $data->parent;
            }
            $cat = Kwc_Abstract::getFlag($data->componentClass, 'menuCategory');
            if (is_int($menuLevel)) {
                if ($menuLevel == $level+1) {
                    $this->fireEvent(new Kwf_Component_Event_ComponentClass_ContentChanged(
                        $this->_class
                    ));
                }
            } else {
                if ($level == 1 && $cat == $menuLevel) {
                    $this->fireEvent(new Kwf_Component_Event_ComponentClass_ContentChanged(
                        $this->_class
                    ));
                }
            }
        }
    }
}