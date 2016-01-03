<?php
/**
 * @package Model
 */
class Kwf_Model_FieldRows extends Kwf_Model_Data_Abstract
    implements Kwf_Model_RowsSubModel_Interface
{
    protected $_rowClass = 'Kwf_Model_FieldRows_Row';
    protected $_rowsetClass = 'Kwf_Model_FieldRows_Rowset';
    protected $_fieldName;

    public function __construct(array $config = array())
    {
        if (isset($config['fieldName'])) {
            $this->_fieldName = $config['fieldName'];
        }
        parent::__construct($config);
    }

    public function createRow(array $data=array())
    {
        throw new Kwf_Exception('getRows is not possible for Kwf_Model_FieldRows');
    }

    public function getRows($where=null, $order=null, $limit=null, $start=null)
    {
        throw new Kwf_Exception('getRows is not possible for Kwf_Model_FieldRows');
    }

    public function update(Kwf_Model_Row_Interface $row, $rowData)
    {
        $iId = $row->getSubModelParentRow()->getInternalId();
        foreach ($this->_rows[$iId] as $k=>$i) {
            if ($row === $i) {
                $this->_data[$iId][$k] = $rowData;
                $this->_updateParentRow($row->getSubModelParentRow());
                return $rowData[$this->getPrimaryKey()];
            }
        }
        throw new Kwf_Exception("Can't find entry");
    }

    public function insert(Kwf_Model_Row_Interface $row, $rowData)
    {
        $iId = $row->getSubModelParentRow()->getInternalId();
        if (!isset($rowData[$this->getPrimaryKey()])) {
            if (!isset($this->_autoId[$iId])) {
                //setzt _autoId und _data
                $this->getRowsByParentRow($row->getSubModelParentRow());
            }
            $this->_autoId[$iId]++;
            $rowData[$this->getPrimaryKey()] = $this->_autoId[$iId];
        }
        $this->_data[$iId][] = $rowData;
        $this->_rows[$iId][count($this->_data[$iId])-1] = $row;
        $this->_updateParentRow($row->getSubModelParentRow());
        return $rowData[$this->getPrimaryKey()];
    }

    public function delete(Kwf_Model_Row_Interface $row)
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
        $v = array(
            'data' => $this->_data[$parentRow->getInternalId()],
            'autoId' => $this->_autoId[$parentRow->getInternalId()]
        );
        $parentRow->{$this->_fieldName} = serialize($v);
    }

    public function getRowsByParentRow(Kwf_Model_Row_Interface $parentRow, $select = array())
    {
        $this->_data[$parentRow->getInternalId()] = array();

        $v = $parentRow->{$this->_fieldName};
        if (substr($v, 0, 13) == 'kwfSerialized') {
            $v = substr($v, 13);
        }
        $v = unserialize($v);
        if ($v) {
            $this->_autoId[$parentRow->getInternalId()] = $v['autoId'];
            foreach ($v['data'] as $i) {
                $this->_data[$parentRow->getInternalId()][] = $i;
            }
        } else {
            $this->_autoId[$parentRow->getInternalId()] = 0;
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

    public function getUniqueIdentifier() {
        throw new Kwf_Exception("no unique identifier set");
    }
}
