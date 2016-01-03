<?php
class Kwf_Util_Model_Feed_Entries extends Kwf_Model_Abstract
    implements Kwf_Model_RowsSubModel_Interface
{
    protected $_rowsetClass = 'Kwf_Model_Rowset_ParentRow';
    protected $_rowClass = 'Kwf_Util_Model_Feed_Row_Entry';

    protected function _getOwnColumns()
    {
        return array(
            'id', 'title', 'link', 'description', 'date', 'author_name',
            'content_encoded',
            'media_image', 'media_image_width', 'media_image_height',
            'media_thumbnail', 'media_thumbnail_width', 'media_thumbnail_height',
        );
    }

    public function getPrimaryKey()
    {
        return false;
    }
    public function getRows($where=null, $order=null, $limit=null, $start=null)
    {
        throw new Kwf_Exception_NotYetImplemented();
    }

    public function getRowsByParentRow(Kwf_Model_Row_Interface $parentRow, $select = array())
    {
        $select = $this->select($select);
        if (!($parentRow instanceof Kwf_Util_Model_Feed_Row_Feed)) {
            throw new Kwf_Exception('Only possible with feed row');
        }
        return $parentRow->getEntries($select);
    }

    //"darf" nur von Kwf_Util_Model_Feed_Row_Feed aufgerufen werden!
    public function _getFeedEntries($parentRow, $xml, $select = array())
    {
        $select = $this->select($select);

        $pId = $parentRow->getInternalId();
        $this->_data[$pId] = array();

        if ($parentRow->format == Kwf_Util_Model_Feed_Row_Feed::FORMAT_RSS) {
            if (in_array('http://purl.org/rss/1.0/', $xml->getNamespaces(true))) {
                $xml->registerXPathNamespace('rss', 'http://purl.org/rss/1.0/');
                foreach ($xml->xpath('//rss:item') as $item) {
                    $this->_data[$pId][] = $item;
                    if (($l = $select->getPart(Kwf_Model_Select::LIMIT_COUNT))
                        && count($this->_data[$pId]) == $l)
                    {
                        break;
                    }
                }
            } else {
                foreach ($xml->channel->item as $item) {
                    $this->_data[$pId][] = $item;
                    if (($l = $select->getPart(Kwf_Model_Select::LIMIT_COUNT))
                        && count($this->_data[$pId]) == $l)
                    {
                        break;
                    }
                }
            }
        } else {
            foreach ($xml->entry as $item) {
                $this->_data[$pId][] = $item;
                if (($l = $select->getPart(Kwf_Model_Select::LIMIT_COUNT))
                    && count($this->_data[$pId]) == $l)
                {
                    break;
                }
            }
        }

        return new $this->_rowsetClass(array(
            'model' => $this,
            'dataKeys' => array_keys($this->_data[$pId]),
            'parentRow' => $parentRow
        ));
    }
    public function getRowByDataKeyAndParentRow($key, $parentRow)
    {
        $pId = $parentRow->getInternalId();
        if (!isset($this->_rows[$pId][$key])) {
            $this->_rows[$pId][$key] = new $this->_rowClass(array(
                'xml' => $this->_data[$pId][$key],
                'feed' => $parentRow,
                'model' => $this
            ));
        }
        return $this->_rows[$pId][$key];
    }

    public function createRowByParentRow(Kwf_Model_Row_Interface $parentRow, array $data = array())
    {
        throw new Kwf_Exception("read only");
    }
}
