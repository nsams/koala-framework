<?php
class Kwc_Root_Category_Generator extends Kwf_Component_Generator_Abstract
{
    protected $_componentClass = 'row';
    protected $_idSeparator = false;
    protected $_loadTableFromComponent = false;
    protected $_inherits = true;

    protected $_useMobileBreakpoints = NULL;

    private $_basesCache = array();
    protected $_eventsClass = 'Kwc_Root_Category_GeneratorEvents';

    protected function _init()
    {
        parent::_init();
        if (is_null($this->_useMobileBreakpoints)) {
            $this->_useMobileBreakpoints = Kwf_Config::getValue('kwc.mobileBreakpoints');
        }
    }

    private function _getModelCache()
    {
        if (!isset($this->_modelCache)) {
            $this->_modelCache = new Kwc_Root_Category_ModelCache($this->_getModel(), $this->_settings['component'], $this->_useMobileBreakpoints);
        }
        return $this->_modelCache;
    }

    /**
     * Returns all recursive children of a page
     */
    public function getRecursivePageChildIds($parentId)
    {
        $select = new Kwf_Model_Select();
        $ret = $this->_getModelCache()->getChildPageIds($parentId);
        foreach ($ret as $i) {
            $ret = array_merge($ret, $this->getRecursivePageChildIds($i));
        }
        return $ret;
    }

    //called by GeneratorEvents when model changes
    public function pageDataChanged()
    {
        $this->_pageDataCache = array();
    }

    protected function _formatSelectFilename(Kwf_Component_Select $select)
    {
        return $select;
    }

    protected function _formatSelectHome(Kwf_Component_Select $select)
    {
        return $select;
    }

    public function getChildIds($parentData, $select = array())
    {
        throw new Kwf_Exception('Not supported yet');
    }

    public function getChildData($parentData, $select = array())
    {
        Kwf_Benchmark::count('GenPage::getChildData', $this->getClass().' '.get_class($this));
        if (!$parentData) {
            Kwf_Benchmark::count('GenPage::getChildData no parentData', $this->getClass().' '.get_class($this));
        }

        $select = $this->_formatSelect($parentData, $select);
        if (is_null($select)) return array();
        $pageIds = $this->_getModelCache()->getPageIds($parentData, $select);

        $ret = array();
        foreach ($pageIds as $pageId) {
            $page = $this->_getModelCache()->getPageData($pageId);
            if (!$page) continue; //can happen for floating page (without valid parent)
            if ($select->hasPart(Kwf_Component_Select::WHERE_SHOW_IN_MENU)) {
                $menu = $select->getPart(Kwf_Component_Select::WHERE_SHOW_IN_MENU);
                if ($menu == $page['hide']) continue;
            }
            if ($select->getPart(Kwf_Component_Select::IGNORE_VISIBLE)) {
            } else if (!Kwf_Component_Data_Root::getShowInvisible()) {
                if (!$page['parent_visible']) continue;
            }
            $d = $this->_createData($parentData, $pageId, $select);
            if ($d) $ret[] = $d;

            if ($select->hasPart(Kwf_Model_Select::LIMIT_COUNT)) {
                if (count($ret) >= $select->getPart(Kwf_Model_Select::LIMIT_COUNT)) break;
            }
        }
        return $ret;
    }

    protected function _createData($parentData, $id, $select)
    {
        $page = $this->_getModelCache()->getPageData($id);

        if (!$parentData || ($parentData->componentClass == $this->_class && $page['parent_id'])) {
            $parentData = $page['parent_id'];
        }

        foreach ($page['parent_ids'] as $i) {
            if (!is_numeric($i)) {
                $c = array();
                if ($select->hasPart(Kwf_Component_Select::IGNORE_VISIBLE)) {
                    $c['ignoreVisible'] = $select->getPart(Kwf_Component_Select::IGNORE_VISIBLE);
                }
                $pData = Kwf_Component_Data_Root::getInstance()
                                    ->getComponentById($i, $c);
                if ($pData->componentClass != $this->_class) {
                    return null;
                }
            }
        }

        return parent::_createData($parentData, $id, $select);
    }

    protected function _getComponentIdFromRow($parentData, $id)
    {
        return $id;
    }

    protected function _formatConfig($parentData, $id)
    {
        $data = array();
        $page = $this->_getModelCache()->getPageData($id);
        $data['filename'] = $page['filename'];
        $data['rel'] = '';
        $data['name'] = $page['name'];
        $data['isPage'] = true;
        $data['isPseudoPage'] = true;
        $data['componentId'] = $this->_getComponentIdFromRow($parentData, $id);
        $data['componentClass'] = $this->_getChildComponentClass($page['component'], $parentData);
        $data['row'] = (object)$page;
        if (!is_object($parentData)) {
            $data['_lazyParent'] = $parentData;
        } else {
            $data['parent'] = $parentData;
        }
        $data['isHome'] = $page['is_home'];
        if (!$page['visible']) {
            $data['invisible'] = true;
        }
        return $data;
    }
    protected function _getIdFromRow($id)
    {
        return $id;
    }

    protected function _getDataClass($config, $id)
    {
        $page = $this->_getModelCache()->getPageData($id);
        if ($page['is_home']) {
            return 'Kwf_Component_Data_Home';
        } else {
            return parent::_getDataClass($config, $id);
        }
    }

    public function getGeneratorFlags()
    {
        $ret = parent::getGeneratorFlags();
        $ret['showInPageTreeAdmin'] = true;
        $ret['showInLinkInternAdmin'] = true;
        $ret['pseudoPage'] = true;
        $ret['page'] = true;
        $ret['table'] = true;
        $ret['pageGenerator'] = true;
        if (!isset($this->_settings['hasHome']) || $this->_settings['hasHome']) {
            $ret['hasHome'] = true;
        }
        return $ret;
    }


    public function getPagesControllerConfig($component, $generatorClass = null)
    {
        $ret = parent::getPagesControllerConfig($component, $generatorClass);

        $ret['actions']['delete'] = true;
        $ret['actions']['copy'] = true;
        $ret['actions']['visible'] = true;
        if ($this->getGeneratorFlag('hasHome')) {
            $ret['actions']['makeHome'] = true;
        }

        // Bei Pages muss nach oben gesucht werden, weil Klasse von Generator
        // mit Komponentklasse übereinstimmen muss
        $c = $component;
        while ($c && $c->componentClass != $this->getClass()) {
            $c = $c->parent;
        }
        if ($c) { //TODO warum tritt das auf?
            $ret['editControllerComponentId'] = $c->componentId;
        }

        $ret['icon'] = 'page';

        if ($component->isHome) {
            $ret['iconEffects'][] = 'home';
        } else if (!$component->visible) {
            $ret['iconEffects'][] = 'invisible';
        }
        $ret['allowDrag'] = true;
        //allowDrop wird in PagesController gesetzt da *darunter* eine page möglich ist

        return $ret;
    }

    public function getStaticCacheVarsForMenu()
    {
        $ret = array();
        $ret[] = array(
            'model' => $this->getModel()
        );
        return $ret;
    }


    public function getDuplicateProgressSteps($source)
    {
        $ret = 1;
        $ret += Kwc_Admin::getInstance($source->componentClass)->getDuplicateProgressSteps($source);
        foreach ($this->_getModelCache()->getChildPageIds($source->id) as $i) {
            $data = $this->getChildData(null, array('id'=>$i, 'ignoreVisible'=>true));
            $data = array_shift($data);
            $ret += $this->getDuplicateProgressSteps($data);
        }
        return $ret;
    }

    public function duplicateChild($source, $parentTarget, Zend_ProgressBar $progressBar = null)
    {
        if ($source->generator !== $this) {
            throw new Kwf_Exception("you must call this only with the correct source");
        }
        if (!Kwf_Component_Generator_Abstract::getInstances($parentTarget, array('whereGeneratorClass'=>get_class($this)))) {
            throw new Kwf_Exception("you must call this only with the correct target");
        }

        $sourceId = $source->id;
        $parentSourceId = $source->parent->componentId;
        $parentTargetId = $parentTarget->componentId;
        unset($source);
        unset($parentTarget);
        $targetId = $this->_duplicatePageRecursive($parentSourceId, $parentTargetId, $sourceId, $progressBar);
        return Kwf_Component_Data_Root::getInstance()
            ->getComponentById($targetId, array('ignoreVisible'=>true));
    }

    private function _duplicatePageRecursive($parentSourceId, $parentTargetId, $childId, Zend_ProgressBar $progressBar = null)
    {
        $pd = $this->_getModelCache()->getPageData($childId);
        if ($progressBar) $progressBar->next(1, trlKwf("Pasting {0}", $pd['name']));

        $data = array();
        $data['parent_id'] = Kwf_Component_Data_Root::getInstance()
            ->getComponentById($parentTargetId, array('ignoreVisible'=>true))
            ->dbId;
        $sourceRow = $this->getModel()->getRow($childId);
        if ($sourceRow->is_home) {
            //copy is_home only if target has no home yet
            $t = Kwf_Component_Data_Root::getInstance()->getComponentById($parentTargetId, array('ignoreVisible'=>true));
            while ($t && !Kwc_Abstract::getFlag($t->componentClass, 'hasHome')) {
                $t = $t->parent;
            }
            if (!$t || $t->getChildPage(array('home' => true, 'ignoreVisible'=>true), array())) {
                $data['is_home'] = false;
            }
        }
        $newRow = $sourceRow->duplicate($data);

        //clear cache in here as while duplicating the modelobserver might be disabled
        Kwf_Cache_Simple::delete('pcIds-'.$newRow->parent_id);

        //ids are numeric, we don't have to use parentSource/parentTarget
        $source = Kwf_Component_Data_Root::getInstance()->getComponentById($childId, array('ignoreVisible'=>true));
        $target = Kwf_Component_Data_Root::getInstance()->getComponentById($newRow->id, array('ignoreVisible'=>true));
        if (!$target) {
            throw new Kwf_Exception("didn't find just duplicated component '$newRow->id' below '{$parentTarget->componentId}'");
        }

        Kwc_Admin::getInstance($source->componentClass)->duplicate($source, $target, $progressBar);

        $sourceId = $source->componentId;
        $targetId = $target->componentId;
        unset($source);
        unset($target);
        unset($sourceRow);
        unset($newRow);

        /*
        echo round(memory_get_usage()/1024/1024, 2)."MB";
        echo " gen: ".Kwf_Component_Generator_Abstract::$objectsCount.', ';
        echo " data: ".Kwf_Component_Data::$objectsCount.', ';
        echo " row: ".Kwf_Model_Row_Abstract::$objectsCount.'';
        $s = microtime(true);
        */
        Kwf_Component_Data_Root::getInstance()->freeMemory();
        /*
        echo ' / '.round((microtime(true)-$s)*1000, 2).' ms ';
        echo ' / '.round(memory_get_usage()/1024/1024, 2)."MB";
        echo " gen: ".Kwf_Component_Generator_Abstract::$objectsCount.', ';
        echo " data: ".Kwf_Component_Data::$objectsCount.', ';
        echo " row: ".Kwf_Model_Row_Abstract::$objectsCount.'';
        //var_dump(Kwf_Model_Row_Abstract::$objectsByModel);
        //var_dump(Kwf_Component_Data::$objectsById);
        echo "\n";
        */

        foreach ($this->_getModelCache()->getChildPageIds($childId) as $i) {
            if ($i != $targetId) { //no endless recursion id page is pasted below itself
                $this->_duplicatePageRecursive($sourceId, $targetId, $i, $progressBar);
            }
        }

        return $targetId;
    }

    public function getNameColumn()
    {
        return 'name';
    }

    public function getFilenameColumn()
    {
        return 'filename';
    }

    public function getPagePropertiesForm($componentOrParent)
    {
        return new Kwc_Root_Category_GeneratorForm($componentOrParent, $this);
    }

    public function getUseMobileBreakpoints()
    {
        return $this->_useMobileBreakpoints;
    }

    public function getDeviceVisible(Kwf_Component_Data $data)
    {
        if ($this->_useMobileBreakpoints) {
            return $data->row->device_visible;
        } else {
            return parent::getDeviceVisible($data);
        }
    }

    public function getRecursiveDataResolver()
    {
        return 'Kwc_Root_Category_RecursiveDataResolver';
    }
}
