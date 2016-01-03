<?php
class Kwc_Directories_Category_View_Component
    extends Kwc_Directories_List_ViewPage_Component
{
    public static function getSettings($param)
    {
        $ret = parent::getSettings($param);
        $ret['placeholder']['linkPrefix'] = '';
        $ret['hideCategoriesWithoutEntries'] = false;
        // Setting hideCategoriesWithoutEntries on true doesn't work correctly
        // It's fixed in Lofer, but probably to slow to use it everywhere, @author Franz
        // better solution would be to fetch listCount by a subquery
        return $ret;
    }

    public function getViewCacheLifetime()
    {
        return 3600;
    }

    public function getPartialVars($partial, $nr, $info)
    {
        $ret = parent::getPartialVars($partial, $nr, $info);
        $ret['placeholder'] = $this->_getPlaceholder();
        $ret['hideCategoriesWithoutEntries'] = $this->_getSetting('hideCategoriesWithoutEntries');
        return $ret;
    }

    public function getItemCountCacheId($row)
    {
        // Row kann von hier (Model) oder von Admin (DB-Row) kommen
        $highestSubRoot = false;
        $c = $this->getData();
        while ($c) {
            $isSubroot = Kwf_Component_Abstract::getFlag($c->componentClass, 'subroot');
            if ($isSubroot) {
                $highestSubRoot = $c;
            }
            $c = $c->parent;
        }
        if (!$highestSubRoot) {
            $cacheClassId = '';
        } else {
            $cacheClassId = $highestSubRoot->componentId;
        }

        if (!$row instanceof Kwf_Model_Row_Interface) {
            throw new Kwf_Exception('Tables are not allowed anymore when using directories');
        }
        return preg_replace('/[^a-zA-Z0-9_]/', '_', $cacheClassId).'KwcDirectoriesCategoryTreeViewComponent_category'.get_class($row->getModel()).$row->id.'_itemCount';
    }

    public function getPagingCount($select = null)
    {
        if ($this->_getSetting('hideCategoriesWithoutEntries')) {
            if (!$select) $select = $this->_getSelect();
            if (!$select) return 0;

            $items = $this->_getItems($select);
            $ret = 0;
            foreach ($items as $item) {
                if ($item->listCount) $ret++;
            }
            if ($select->hasPart(Kwf_Model_Select::LIMIT_COUNT)) {
                $limitCount = $select->getPart(Kwf_Model_Select::LIMIT_COUNT);
                if ($ret > $limitCount) $ret = $limitCount;
            }
            return $ret;
        } else {
            return parent::getPagingCount($select);
        }
    }

    public function getItemIds($count = null, $offset = null)
    {
        if ($this->_getSetting('hideCategoriesWithoutEntries')) {
            $select = $this->_getSelect();
            if (!$select) return array();
            if ($count) $select->limit($count, $offset);
            $items = $this->_getItems($select);
            $ret = array();
            foreach ($items as $item) {
                if ($item->listCount) $ret[] = $item->row->id;
            }
            return $ret;
        } else {
            return parent::getItemIds($count, $offset);
        }
    }

    public static function getItemCountCache()
    {
        $frontendOptions = array('lifetime' => 3600, 'automatic_serialization' => true);
        $backendOptions = array('cache_dir' => 'cache/component/');
        return Kwf_Cache::factory('Core', 'File', $frontendOptions, $backendOptions);
    }

    protected function _getCountCategoryIds($item)
    {
        return array($item->row->id);
    }

    protected function _getItems($select = null)
    {
        $items = parent::_getItems($select);

        $cache = self::getItemCountCache();

        foreach ($items as &$item) {
            $cacheId = $this->getItemCountCacheId($item->row);

            if (($item->listCount = $cache->load($cacheId)) == false) {
                if (!isset($itemDirectory)) {
                    $itemDirectory = $this->getData()->parent->getComponent()->getItemDirectory();
                }
                if (!isset($connectData)) {
                    $tableName = Kwc_Abstract::getSetting(
                        $itemDirectory->componentClass, 'categoryToItemModelName'
                    );
                    $connectData = Kwc_Directories_Category_Detail_List_Component::getTableReferenceData(
                        $tableName, 'Item'
                    );
                }
                if (!isset($directoryComponent)) {
                    $directoryComponent = $this->getData()->parent->getComponent()
                        ->getItemDirectory()->parent->getComponent();
                }

                $categoryIds = $this->_getCountCategoryIds($item);

                $select = $directoryComponent->getSelect();
                if (!Kwc_Abstract::getSetting(get_class($directoryComponent), 'generatorJoins')) {
                    $select->join(
                        $connectData['tableName'],
                        "$connectData[refTableName].$connectData[refItemColumn] = $connectData[tableName].$connectData[itemColumn]",
                        array()
                    );
                }
                $select->where("$connectData[tableName].category_id IN(".implode(',', $categoryIds).")");

                $item->listCount = $directoryComponent->getData()->countChildComponents($select);

                $cache->save($item->listCount, $cacheId);
            }
        }

        return $items;
    }
}

