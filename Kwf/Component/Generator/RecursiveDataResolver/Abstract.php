<?php
class Kwf_Component_Generator_RecursiveDataResolver_Abstract
{
    protected function _fetchRows(array $generators, Kwf_Model_Select $select)
    {
        $models = array();
        foreach ($generators as $g) {
            $m = $g->getModel();
            if (!in_array($m, $models, true)) {
                $models[] = $m;
            }
        }

        $rows = array();
        foreach ($models as $m) {
            $s = clone $select;
            $this->_formatSelect($m, $select);
            foreach ($m->getRows($s) as $row) {
                $rows[] = $row;
            }
        }
        return $rows;
    }

    protected function _formatSelect(Kwf_Model_Interface $model, Kwf_Model_Select $select)
    {
        $showInvisible = Kwf_Component_Data_Root::getShowInvisible();
        if (!$select->getPart(Kwf_Component_Select::IGNORE_VISIBLE)
            && $model->hasColumn('visible')
            && !$showInvisible
        ) {
            $select->whereEquals('visible', true);
        }
    }
}
