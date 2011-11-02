<?php
class Es_Model_DbTable_Property_Collection extends Es_Db_Table
{
    protected $_name = 'properties';
    private $_document_id;

    public function load($document_type_id = NULL, $tab_id = NULL, $document_id = null)
    {
        $this->setDocumentTypeId($document_type_id);
        $this->setTabId($tab_id);
        $this->setDocumentId($document_id);

        $this->getProperties(TRUE);
    }

    public function getProperties($force_reload = FALSE)
    {
        if($this->getData('properties') == NULL or $force_reload)
        {
            $select = $this->getAdapter()->select()
            ->from(array('t'=>'tabs'), array())
            ->joinInner(array('p'=>'properties'), 't.tab_id = p.tab_id');

            if($this->getDocumentId() !== NULL)
            {
                $select->joinInner(array('d'=>'documents'), 'd.document_type_id = t.document_type_id', array('d.document_id'));
                $select->joinLeft(array('pv'=>'properties_value'), 'd.document_id = pv.document_id AND p.property_id = pv.property_id', array('pv.value', 'pv.property_value_id'));
                $select->where('d.document_id = ?', $this->_document_id);
            }

            if($this->getTabId() != NULL)
            {
                $select->where('t.tab_id = ?', array($this->getTabId()));
            }

            $select->where('t.document_type_id = ? ',$this->getDocumentTypeId());
            $select->order('p.property_order ASC');

            $rows = $this->getAdapter()->fetchAll($select);

            $properties = array();
            foreach($rows as $row)
            {
                $properties[] = Es_Model_DbTable_Property_Model::fromArray($row);
            }

            $this->setData('properties', $properties);
        }

        return $this->getData('properties');
    }

    public function addProperty($property)
    {
        $this->_properties_elements[] = $property;
        return $this;
    }

    public function setProperties(Array $properties)
    {
        $array = array();
        foreach($properties as $property)
        {
            $array[] = Es_Model_DbTable_Property_Model::fromArray($property);
        }

        $this->setData('properties', $array);
    }

    public function save()
    {
        $properties = $this->getProperties();
        try
        {
            $this->getAdapter()->beginTransaction();
            foreach($properties as $property)
            {
                $property->save();
            }

            $this->getAdapter()->commit();
        }
        catch(Exception $e)
        {
            $this->getAdapter()->rollBack();
            Es_Exception($e->getMessage());
        }
    }
}
