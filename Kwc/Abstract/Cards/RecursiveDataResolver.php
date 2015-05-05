<?php
class Kwc_Abstract_Cards_RecursiveDataResolver extends Kwf_Component_Generator_RecursiveDataResolver_Abstract
{
    public function getRecursive(array $generators, Kwf_Component_Select $select)
    {
        Kwf_Benchmark::count('RecursiveDataResolver::getRecursive', get_class($this));
        if ($p = $select->getPart(Kwf_Component_Select::WHERE_CHILD_OF)) {
            $ors = array(
                new Kwf_Model_Select_Expr_StartsWith('component_id', $p->dbId.'-'),
                new Kwf_Model_Select_Expr_Equal('component_id', $p->dbId),
            );

            foreach ($generators as $g) {
                foreach (Kwf_Component_Generator_Abstract::getPossibleIndirectDbIdShortcuts($g->getClass(), $p->componentClass) as $dbIdShortcut) {
                    $ors[] = new Kwf_Model_Select_Expr_StartsWith('component_id', $dbIdShortcut);
                }
            }
            $select->where(new Kwf_Model_Select_Expr_Or($ors));
        }

        if ($select->hasPart(Kwf_Model_Select::WHERE_ID)) {
            throw new Kwf_Exception_NotYetImplemented();
        }
        if ($select->hasPart(Kwf_Component_Select::WHERE_HOME)) {
            throw new Kwf_Exception_NotYetImplemented();
        }
        if ($select->hasPart(Kwf_Component_Select::WHERE_FILENAME)) {
            throw new Kwf_Exception_NotYetImplemented();
        }
        if ($select->hasPart(Kwf_Component_Select::WHERE_SHOW_IN_MENU)) {
            throw new Kwf_Exception_NotYetImplemented();
        }
        if ($select->hasPart(Kwf_Component_Select::WHERE_COMPONENT_KEY)) {
            throw new Kwf_Exception_NotYetImplemented();
        }

        $components = array();
        if ($select->hasPart(Kwf_Component_Select::WHERE_FLAGS)) {
            foreach ($generators as $generator) {
                foreach ($generator->getChildComponentClasses($select) as $key=>$c) {
                    if (!in_array($key, $components)) $components[] = $key;
                }
            }
            if (!$components) return array();
        }

        if ($select->hasPart(Kwf_Component_Select::WHERE_COMPONENT_CLASSES)) {
            $selectClasses = $select->getPart(Kwf_Component_Select::WHERE_COMPONENT_CLASSES);
            if (!$selectClasses) return array();
            foreach ($generators as $generator) {
                $childClasses = $generator->getChildComponentClasses();

                foreach ($selectClasses as $selectClass) {
                    $components = array_merge($components, array_keys($childClasses, $selectClass));
                }

            }
        }
        $components = array_unique($components);
        $select->whereEquals('component', $components);
        $ret = array();
        $s = array();
        if ($select->getPart(Kwf_Component_Select::IGNORE_VISIBLE)) {
            $s['ignoreVisible'] = true;
        }
        $rows = $this->_fetchRows($generators, $select);
        foreach ($rows as $row) {
            foreach (Kwf_Component_Data_Root::getInstance()->getComponentsByDbId($row->component_id, $s) as $c) {
                $ret[] = $c->getChildComponent('-child');
            }
        }
        return $ret;
    }
}
