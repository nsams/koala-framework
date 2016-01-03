<?php
/**
 * @package Model
 * @internal
 */
class Kwf_Model_Mongo_RowsSubModel extends Kwf_Model_Data_Abstract
    implements Kwf_Model_RowsSubModel_Interface
{
    protected $_rowClass = 'Kwf_Model_Mongo_RowsSubModel_Row';
    protected $_rowsetClass = 'Kwf_Model_Mongo_RowsSubModel_Rowset';
    protected $_primaryKey = 'intern_id';
    protected $_fieldName;

    /**
     * @var Kwf_Model_Mongo
     */
    protected $_parentModel;

    public function __construct(array $config = array())
    {
        if (isset($config['fieldName'])) {
            $this->_fieldName = $config['fieldName'];
        }
        if (isset($config['parentModel'])) {
            $this->_parentModel = $config['parentModel'];
        }
        parent::__construct($config);
    }

    protected function _init()
    {
        parent::_init();
        if (isset($this->_parentModel) && is_string($this->_parentModel)) {
            $this->_parentModel = Kwf_Model_Abstract::getInstance($this->_parentModel);
        }
        if (isset($this->_parentModel)) {
            $this->setParentModel($this->_parentModel);
        }
    }

    public function setParentModel($m)
    {
        $m = Kwf_Model_Abstract::getInstance($m);
        $this->_parentModel = $m;
        while (is_instance_of($m, 'Kwf_Model_Proxy')) {
            $m = $m->getProxyModel();
        }
        if (!$m instanceof Kwf_Model_Mongo) {
            throw new Kwf_Exception("parentModel is not a Mongo");
        }
    }

    private function _getParentMongoModel()
    {
        $m = $this->_parentModel;
        while ($m instanceof Kwf_Model_Proxy) $m = $m->getProxyModel();
        return $m;
    }

    public function createRow(array $data=array())
    {
        throw new Kwf_Exception('getRows is not possible for Kwf_Model_Mongo_RowsSubModel');
    }

    public function getRows($where=null, $order=null, $limit=null, $start=null)
    {
        throw new Kwf_Exception('getRows is not possible for Kwf_Model_Mongo_RowsSubModel');
    }

    public function getRowsByParentRow(Kwf_Model_Row_Interface $parentRow, $select = array())
    {
        $this->_data[$parentRow->getInternalId()] = array();

        $v = $parentRow->{$this->_fieldName};
        if ($v) {
            if (!is_array($v)) throw new Kwf_Exception("value is not an array but a ".gettype($v));
            //TODO: _data nicht duplizieren, sondern in getRowByDataKey erst machen (performance)
            $i = 0;
            foreach (array_keys($v) as $k) {
                $v[$k]['intern_id'] = ++$i; //TODO ist das eindeutig genug (ist nur eindeutig PRO parentRow)
                                            // aber nachdem man hierher eh nur per getRowsByParentRow kommt müsste es passen
            }
            $this->_data[$parentRow->getInternalId()] = $v;
        } else {
            $this->_data[$parentRow->getInternalId()] = array();
        }

        if (!is_object($select)) {
            $select = $this->select($select);
        }
        return new $this->_rowsetClass(array(
            'model' => $this,
            'dataKeys' => $this->_selectDataKeys($select, $this->_data[$parentRow->getInternalId()]),
            'parentRow' => $parentRow
        ));
    }

    public function createRowByParentRow(Kwf_Model_Row_Interface $parentRow, array $data = array())
    {
        return $this->_createRow($data, array('parentRow' => $parentRow));
    }

    public function getRowByDataKeyAndParentRow($key, $parentRow)
    {
        if (!isset($this->_rows[$parentRow->getInternalId()][$key])) {
            $this->_rows[$parentRow->getInternalId()][$key] = new $this->_rowClass(array(
                'data' => $this->_data[$parentRow->getInternalId()][$key],
                'model' => $this,
                'parentRow' => $parentRow
            ));
        }
        return $this->_rows[$parentRow->getInternalId()][$key];
    }

    private function _updateParentRow($parentRow)
    {
        $v = $this->_data[$parentRow->getInternalId()];
        foreach ($v as $k=>$i) {
            unset($v[$k]['intern_id']);
        }
        $parentRow->{$this->_fieldName} = $v;
    }

    public function update(Kwf_Model_Mongo_RowsSubModel_Row $row, $rowData)
    {
        $iId = $row->getSubModelParentRow()->getInternalId();
        foreach ($this->_rows[$iId] as $k=>$i) {
            if ($row === $i) {
                $this->_data[$iId][$k] = $rowData;
                $this->_updateParentRow($row->getSubModelParentRow());
                //return $rowData[$this->getPrimaryKey()];
                return;
            }
        }
        throw new Kwf_Exception("Can't find entry");
    }

    public function insert(Kwf_Model_Mongo_RowsSubModel_Row $row, $rowData)
    {
        $iId = $row->getSubModelParentRow()->getInternalId();
        if (!isset($this->_data[$iId])) {
            //setzt _data (TODO: effizienter machen?)
            $this->getRowsByParentRow($row->getSubModelParentRow());
        }
        $this->_data[$iId][] = $rowData;
        $this->_rows[$iId][count($this->_data[$iId])-1] = $row;
        $this->_updateParentRow($row->getSubModelParentRow());
        //return $rowData[$this->getPrimaryKey()];
    }

    public function delete(Kwf_Model_Mongo_RowsSubModel_Row $row)
    {
        foreach ($this->_rows[$row->getSubModelParentRow()->getInternalId()] as $k=>$i) {
            if ($row === $i) {
                unset($this->_data[$row->getSubModelParentRow()->getInternalId()][$k]);
                unset($this->_rows[$row->getSubModelParentRow()->getInternalId()][$k]);
                $this->_updateParentRow($row->getSubModelParentRow());
                return;
            }
        }
        throw new Kwf_Exception("Can't find entry");
    }


    public function getUniqueIdentifier() {
        throw new Kwf_Exception("no unique identifier set");
    }




    public function dependentModelRowUpdated(Kwf_Model_Row_Abstract $row, $action)
    {
        parent::dependentModelRowUpdated($row, $action);
        $models = array($this);
        $models = array_merge($models, $this->_proxyContainerModels);
        foreach ($models as $model) {
            foreach ($model->_exprs as $column=>$expr) {
                if ($expr instanceof Kwf_Model_Select_Expr_Parent) {
                    if ($model->getReference($expr->getParent()) === Kwf_Model_RowsSubModel_Interface::SUBMODEL_PARENT) {
                        //nothing to do in that case
                    } else if ($model->getReferencedModel($expr->getParent()) === $row->getModel()) {

                        $ref = $model->getReference($expr->getParent());
                        foreach ($row->getModel()->getDependentModels() as $depName=>$m) {
                            if (!$m instanceof Kwf_Model_Abstract) $m = Kwf_Model_Abstract::getInstance($m);
                            if ($m === $model) {
                                $res = $this->_getParentMongoModel()->getCollection()->update(
                                    array($this->_fieldName.'.'.$ref['column'] => $row->{$row->getModel()->getPrimaryKey()}),
                                    array('$set' => array($this->_fieldName.'.$.'.$column => $row->{$expr->getField()})),
                                    array('multiple' => true, 'safe' => true)
                                );
                                if (!$res || !$res['ok']) {
                                    throw new Kwf_Exception("update failed");
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    public function childModelRowUpdated(Kwf_Model_Row_Abstract $row, $action)
    {
        parent::childModelRowUpdated($row, $action);
        $models = array($this);
        $models = array_merge($models, $this->_proxyContainerModels);
        foreach ($models as $model) {
            foreach ($model->_exprs as $column=>$expr) {
                if ($expr instanceof Kwf_Model_Select_Expr_Child) {
                    if ($model->getDependentModel($expr->getChild()) === $row->getModel()) {
                        $rule = $row->getModel()->getReferenceRuleByModelClass(get_class($model));
                        $parentRow = $row->getParentRow($rule);
                        $parentRow->$column = $model->getExprValue($parentRow, $expr);
                        $parentRow->save();
                    }
                }
            }
        }
    }

}
