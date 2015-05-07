<?php
class Kwc_Root_Category_ModelCache
{
    private $_pageDataCache = array();

    protected $_model;
    protected $_components;
    protected $_useMobileBreakpoints;

    public function __construct($model, $components, $useMobileBreakpoints)
    {
        $this->_model = $model;
        $this->_components = $components;
        $this->_useMobileBreakpoints = $useMobileBreakpoints;
    }

    public function getPageData($id)
    {
        if (!array_key_exists($id, $this->_pageDataCache)) {

            if (!(int)$id) {
                throw new Kwf_Exception("Invalid Id: '$id'");
            }
            $id = (int)$id;

            $cacheId = 'pd-'.$id;
            $ret = Kwf_Cache_Simple::fetch($cacheId);
            if ($ret === false) {
                Kwf_Benchmark::count('GenPage::loadPageData');
                $cols = array('id', 'pos', 'is_home', 'name', 'filename', 'visible', 'component', 'hide', 'custom_filename', 'parent_id', 'parent_subroot_id');
                if ($this->_useMobileBreakpoints) $cols[] = 'device_visible';
                $ret = $this->_model->fetchColumnsByPrimaryId($cols, $id);
                if ($ret) {
                    if ($ret['is_home']) $ret['visible'] = 1;
                    $ret['parent_visible'] = $ret['visible'];
                    $i = $ret['parent_id'];
                    $ret['parent_ids'] = array($i);
                    while (is_numeric($i)) {
                        $pd = $this->getPageData($i);
                        if ($pd) {
                            $ret['parent_ids'][] = $pd['parent_id'];
                            if (count($ret['parent_ids']) > 20) {
                                throw new Kwf_Exception('probably endless recursion with parents');
                            }
                            $ret['parent_visible'] = $ret['parent_visible'] && $pd['visible'];
                            $i = $pd['parent_id'];
                        } else {
                            //page seems to be floating (without parent)
                            $ret = null;
                            break;
                        }
                    }
                } else {
                    $ret = null;
                }
                Kwf_Cache_Simple::add($cacheId, $ret);
            }
            $this->_pageDataCache[$id] = $ret;
        }
        return $this->_pageDataCache[$id];
    }

    public function getChildPageIds($parentId)
    {
        $cacheId = 'pcIds-'.$parentId;
        $ret = Kwf_Cache_Simple::fetch($cacheId);
        if ($ret === false) {
            Kwf_Benchmark::count('GenPage::query',  'childIds('.$parentId.')');

            $select = new Kwf_Model_Select();
            if (is_numeric($parentId)) {
                $select->whereEquals('parent_id', $parentId);
            } else {
                $select->where(new Kwf_Model_Select_Expr_Like('parent_id', $parentId.'%'));
            }
            $select->order('pos');
            $rows = $this->_model->export(Kwf_Model_Interface::FORMAT_ARRAY, $select, array('columns'=>array('id')));
            $ret = array();
            foreach ($rows as $row) {
                $ret[] = $row['id'];
            }
            Kwf_Cache_Simple::add($cacheId, $ret);
        }
        return $ret;
    }

    public function getPageIds($parentData, $select)
    {
        if (!$parentData && ($p = $select->getPart(Kwf_Component_Select::WHERE_CHILD_OF))) {
            if ($p->getPage()) $p = $p->getPage();
            $parentData = $p;
        }
        $pageIds = array();

        if ($parentData && !$select->hasPart(Kwf_Component_Select::WHERE_ID)) {
            // diese Abfragen sind implizit recursive=true
            $parentId = $parentData->dbId;

            if ($select->getPart(Kwf_Component_Select::WHERE_HOME)) {

                $s = new Kwf_Model_Select();
                $s->whereEquals('is_home', true);
                $s->whereEquals('parent_subroot_id', $parentData->getSubroot()->dbId); //performance to look only in subroot - correct filterting done below
                Kwf_Benchmark::count('GenPage::query', 'home');
                $rows = $this->_model->export(Kwf_Model_Interface::FORMAT_ARRAY, $s, array('columns'=>array('id')));
                $homePages = array();
                foreach ($rows as $row) {
                    $homePages[] = $row['id'];
                }

                foreach ($homePages as $pageId) {
                    $pd = $this->getPageData($pageId);
                    if (substr($pd['parent_id'], 0, strlen($parentId)) == $parentId) {
                        $pageIds[] = $pageId;
                        continue;
                    }
                    foreach ($pd['parent_ids'] as $pageParentId) {
                        if ($pageParentId == $parentId) {
                            $pageIds[] = $pageId;
                            break;
                        }
                    }
                }

            } else if ($select->hasPart(Kwf_Component_Select::WHERE_FILENAME)) {
                $filename = $select->getPart(Kwf_Component_Select::WHERE_FILENAME);
                $cacheId = 'pcFnIds-'.$parentId.'-'.$filename;
                $pageIds = Kwf_Cache_Simple::fetch($cacheId);
                if ($pageIds === false) {
                    $s = new Kwf_Model_Select();
                    $s->whereEquals('filename', $filename);
                    if (is_numeric($parentId)) {
                        $s->whereEquals('parent_id', $parentId);
                    } else {
                        $s->where(new Kwf_Model_Select_Expr_Like('parent_id', $parentId.'%'));
                    }
                    Kwf_Benchmark::count('GenPage::query', 'filename');
                    $rows = $this->_model->export(Kwf_Model_Interface::FORMAT_ARRAY, $s, array('columns'=>array('id')));
                    $pageIds = array();
                    foreach ($rows as $row) {
                        $pageIds[] = $row['id'];
                    }
                    Kwf_Cache_Simple::add($cacheId, $pageIds);
                }
            } else if ($select->hasPart(Kwf_Component_Select::WHERE_COMPONENT_CLASSES)) {
                $selectClasses = $select->getPart(Kwf_Component_Select::WHERE_COMPONENT_CLASSES);
                $keys = array();
                foreach ($selectClasses as $selectClass) {
                    $key = array_search($selectClass, $this->_components);
                    if ($key) $keys[] = $key;
                }

                $s = new Kwf_Model_Select();
                $s->whereEquals('component', array_unique($keys));
                if (is_numeric($parentId)) {
                    $s->whereEquals('parent_id', $parentId);
                } else {
                    $s->where(new Kwf_Model_Select_Expr_Like('parent_id', $parentId.'%'));
                }
                Kwf_Benchmark::count('GenPage::query', 'component');
                $rows = $this->_model->export(Kwf_Model_Interface::FORMAT_ARRAY, $s, array('columns'=>array('id')));
                foreach ($rows as $row) {
                    $pageIds[] = $row['id'];
                }

            } else {

                $pageIds = $this->getChildPageIds($parentId);

            }

        } else {

            $pagesSelect = new Kwf_Model_Select();

            if ($id = $select->getPart(Kwf_Component_Select::WHERE_ID)) {

                if (!(int)$id) {
                    return array();
                }
                //query only by id, no db query required
                $pageIds = array($id);

                if ($sr = $select->getPart(Kwf_Component_Select::WHERE_SUBROOT)) {
                    $pd = $this->getPageData($id);
                    if ($pd['parent_subroot_id'] != $sr[0]->dbId) {
                        $pageIds = array();
                    }
                }

                if ($pageIds && $select->hasPart(Kwf_Component_Select::WHERE_COMPONENT_CLASSES)) {
                    $selectClasses = $select->getPart(Kwf_Component_Select::WHERE_COMPONENT_CLASSES);
                    $keys = array();
                    foreach ($selectClasses as $selectClass) {
                        $key = array_search($selectClass, $this->_components);
                        if ($key && !in_array($key, $keys)) $keys[] = $key;
                    }
                    $pd = $this->getPageData($id);
                    if (!in_array($pd['component'], $keys)) {
                        $pageIds = array();
                    }
                }

                if ($pageIds && $select->getPart(Kwf_Component_Select::WHERE_HOME)) {
                    $pd = $this->getPageData($id);
                    if (!$pd['is_home']) {
                        $pageIds = array();
                    }
                }

            } else {
                $benchmarkType = '';
                if ($select->hasPart(Kwf_Component_Select::WHERE_SUBROOT)) {

                    $subroot = $select->getPart(Kwf_Component_Select::WHERE_SUBROOT);
                    $subroot = $subroot[0];
                    $pagesSelect->whereEquals('parent_subroot_id', $subroot->dbId);
                    $benchmarkType .= 'subroot ';
                }

                if ($select->getPart(Kwf_Component_Select::WHERE_HOME)) {
                    $pagesSelect->whereEquals('is_home', true);
                    $benchmarkType .= 'home ';
                }
                if ($id = $select->getPart(Kwf_Component_Select::WHERE_ID)) {
                    $pagesSelect->whereEquals('id', $id);
                    $benchmarkType .= 'id ';
                }
                if ($select->hasPart(Kwf_Component_Select::WHERE_COMPONENT_CLASSES)) {
                    $selectClasses = $select->getPart(Kwf_Component_Select::WHERE_COMPONENT_CLASSES);
                    $keys = array();
                    foreach ($selectClasses as $selectClass) {
                        $key = array_search($selectClass, $this->_components);
                        if ($key && !in_array($key, $keys)) $keys[] = $key;
                    }
                    $pagesSelect->whereEquals('component', $keys);
                    $benchmarkType .= 'component ';
                }
                Kwf_Benchmark::count('GenPage::query', "noparent(".trim($benchmarkType).")");
                $rows = $this->_model->export(Kwf_Model_Interface::FORMAT_ARRAY, $pagesSelect, array('columns'=>array('id')));
                $pageIds = array();
                foreach ($rows as $row) {
                    $pageIds[] = $row['id'];
                }
            }

            if ($parentData) {
                $parentId = $parentData->dbId;
                foreach ($pageIds as $k=>$pageId) {
                    $match = false;
                    $pd = $this->getPageData($pageId);
                    if (!$pd) continue;
                    if (substr($pd['parent_id'], 0, strlen($parentId)) == $parentId) {
                        $match = true;
                    }
                    if (!$match) {
                        foreach ($pd['parent_ids'] as $pageParentId) {
                            if ($pageParentId == $parentId) {
                                $match = true;
                                break;
                            }
                        }
                    }
                    if (!$match) {
                        unset($pageIds[$k]);
                    }
                }
            }
        }

        return $pageIds;
    }
}
