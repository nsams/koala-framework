<?php
class Kwc_Root_Category_RecursiveDataResolver extends Kwf_Component_Generator_RecursiveDataResolver_Abstract
{
    public function getRecursive(array $generators, Kwf_Component_Select $select)
    {
        Kwf_Benchmark::count('RecursiveDataResolver::getRecursive', get_class($this));

        $model = null;
        $useMobileBreakpoints = null;
        $components = array();
        foreach ($generators as $g) {
            if ($model && $g->getModel() != $model) {
                throw new Kwf_Exception("All page generators must use the same model");
            }
            $model = $g->getModel();
            $components += $g->getSetting('component'); //TODO merge correctly: handle different components correctly!!!!!
            $useMobileBreakpoints = $g->getUseMobileBreakpoints();
        }

        $modelCache = new Kwc_Root_Category_ModelCache($model, $components, $useMobileBreakpoints);

        $pageIds = $modelCache->getPageIds(null, $select);
        $s = array();
        if ($select->getPart(Kwf_Component_Select::IGNORE_VISIBLE)) {
            $s['ignoreVisible'] = true;
        }

        $ret = array();
        foreach ($pageIds as $pageId) {
            $page = $modelCache->getPageData($pageId);

            if (!$page) continue; //can happen for floating page (without valid parent)
            if ($select->hasPart(Kwf_Component_Select::WHERE_SHOW_IN_MENU)) {
                $menu = $select->getPart(Kwf_Component_Select::WHERE_SHOW_IN_MENU);
                if ($menu == $page['hide']) continue;
            }
            if ($select->getPart(Kwf_Component_Select::IGNORE_VISIBLE)) {
            } else if (!Kwf_Component_Data_Root::getShowInvisible()) {
                if (!$page['parent_visible']) continue;
            }

            $ids = array_reverse($page['parent_ids']);
            $ids[] = $page['id'];

            $id = array_shift($ids);
            $d = Kwf_Component_Data_Root::getInstance()
                ->getComponentById($id, $s);
            $gen = Kwf_Component_Generator_Abstract::getInstances($d, array('pageGenerator'=>true));
            if (count($gen) != 1) throw new Kwf_Exception("Must get exactly one page generator");
            $gen = $gen[0];
            foreach ($ids as $id) {
                $childDatas = $gen->getChildData($d, $s+array('id'=>$id));
                if (count($childDatas) != 1) throw new Kwf_Exception("Must get exactly one data");
                $d = $childDatas[0];
            }
            $ret[] = $d;

            if ($select->hasPart(Kwf_Model_Select::LIMIT_COUNT)) {
                if (count($ret) >= $select->getPart(Kwf_Model_Select::LIMIT_COUNT)) break;
            }
        }
        return $ret;
    }
}
