<?php

namespace Es\Property;

use Es\Db\AbstractTable,
    Zend\Db\Sql\Select;

class Collection extends AbstractTable
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
            ->from('tabs')
            ->columns(array())
            ->join('properties', 'tabs.id = properties.tab_id', '*', Select::JOIN_INNER);

            if($this->getDocumentId() !== NULL)
            {
                $select->join('documents', 'documents.document_type_id = tabs.document_type_id', array(), Select::JOIN_INNER);
                $select->join('properties_values', 'documents.id = properties_values.document_id AND properties.id = properties_values.property_id', array('value'), Select::JOIN_LEFT);
                $select->where(array('documents.id' => $this->getDocumentId()));
            }

            if($this->getTabId() != NULL)
            {
                $select->where(array('tabs.id' => $this->getTabId()));
            }

            if($this->getDocumentTypeId() != NULL)
            {
                $select->where(array('tabs.document_type_id' => $this->getDocumentTypeId()));
            }

            //$select->order('properties.order ASC'); @TODO order statments

            $rows = $this->fetchAll($select);

            $properties = array();
            foreach($rows as $row)
            {
                $properties[] = Model::fromArray((array)$row);
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
            $array[] = Model::fromArray($property);
        }

        $this->setData('properties', $array);
    }

    public function save()
    {
        $properties = $this->getProperties();
        try
        {
            foreach($properties as $property)
            {
                $property->save();
            }

        }
        catch(Exception $e)
        {
            throw new Gc_Exception($e->getMessage());
        }
    }

    public function delete()
    {
        $properties = $this->getProperties();
        try
        {
            foreach($properties as $property)
            {
                $property->delete();

            }
        }
        catch(Exception $e)
        {
            throw new Gc_Exception($e->getMessage());
        }
    }
}
