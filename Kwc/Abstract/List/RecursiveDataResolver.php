<?php
class Kwc_Abstract_List_RecursiveDataResolver extends Kwf_Component_Generator_RecursiveDataResolver_Abstract
{
    public function getRecursive(array $generators, Kwf_Component_Select $select)
    {
        Kwf_Benchmark::count('RecursiveDataResolver::getRecursive', get_class($this));
        if ($p = $select->getPart(Kwf_Component_Select::WHERE_CHILD_OF)) {
            $ors = array(
                new Kwf_Model_Select_Expr_StartsWith('component_id', $p->dbId.'-'),
                new Kwf_Model_Select_Expr_Equal('component_id', $p->dbId),
            );

            /*
            foreach (Kwf_Component_Generator_Abstract::getPossibleIndirectDbIdShortcuts($this->_class, $p->componentClass) as $dbIdShortcut) {
                $ors[] = new Kwf_Model_Select_Expr_StartsWith('component_id', $dbIdShortcut);
            }
            */

            $select->where(new Kwf_Model_Select_Expr_Or($ors));

            //TODO magic needed here
            $select->where(new Kwf_Model_Select_Expr_Not(new Kwf_Model_Select_Expr_Like('component_id', $p->dbId.'-banner%')));
            $select->where(new Kwf_Model_Select_Expr_Not(new Kwf_Model_Select_Expr_Like('component_id', $p->dbId.'-paragraphs%')));
            $select->where(new Kwf_Model_Select_Expr_Not(new Kwf_Model_Select_Expr_Like('component_id', $p->dbId.'-stageTeaser%')));
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
        if ($select->hasPart(Kwf_Component_Select::WHERE_FLAGS)) {
            throw new Kwf_Exception_NotYetImplemented();
        }
        if ($select->hasPart(Kwf_Component_Select::WHERE_COMPONENT_CLASSES)) {
            //TODO?
        }

        $ret = array();
        $s = array();
        if ($select->getPart(Kwf_Component_Select::IGNORE_VISIBLE)) {
            $s['ignoreVisible'] = true;
        }
        $rows = $this->_fetchRows($generators, $select);
        foreach ($rows as $row) {
            $parentDatas = Kwf_Component_Data_Root::getInstance()
                ->getComponentsByDbId($row->component_id, $s);
            foreach ($parentDatas as $pd) {
                foreach ($generators as $g) {
                    if ($g->getClass() == $pd->componentClass) {
                        $s2 = $s;
                        $s2['id'] = $g->getIdSeparator().$row->id;
                        $d = $g->getChildDatas($pd, $s2);
                        if (count($d) != 1) {
                            throw new Kwf_Exception("Generator didn't return single data for id");
                        }
                        $ret[] = $d[0];
                        continue 2;
                    }
                }
            }
        }
        return $ret;
    }

    protected function _formatSelect(Kwf_Model_Interface $model, Kwf_Model_Select $select)
    {
        parent::_formatSelect($model, $select);
        $showInvisible = Kwf_Component_Data_Root::getShowInvisible();
        if (!$select->getPart(Kwf_Component_Select::IGNORE_VISIBLE)
            && $model->hasColumn('cache_visible_parents')
            && !$showInvisible
        ) {
            $select->whereEquals('cache_visible_parents', true);
        }
    }
}
