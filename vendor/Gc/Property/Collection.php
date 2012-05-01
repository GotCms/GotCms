<?php

namespace Gc\Property;

use Gc\Db\AbstractTable,
    Zend\Db\Sql\Select;

class Collection extends AbstractTable
{
    protected $_name = 'property';
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
            $select = new Select();
            $select->from('tab')
            ->columns(array())
            ->join('property', 'tab.id = property.tab_id', '*', Select::JOIN_INNER);

            if($this->getDocumentId() !== NULL)
            {
                $select->join('document', 'document.document_type_id = tab.document_type_id', array(), Select::JOIN_INNER);
                $select->join('property_value', 'document.id = property_value.document_id AND property.id = property_value.property_id', array('value'), Select::JOIN_LEFT);
                $select->where(array('document.id' => $this->getDocumentId()));
            }

            if($this->getTabId() != NULL)
            {
                $select->where(array('tab.id' => $this->getTabId()));
            }

            if($this->getDocumentTypeId() != NULL)
            {
                $select->where(array('tab.document_type_id' => $this->getDocumentTypeId()));
            }

            //$select->order('properties.order ASC'); @TODO order statments

            $rows = $this->fetchAll($select);

            $properties = array();
            foreach($rows as $row)
            {
                $property_model = Model::fromArray((array)$row);
                if($this->getDocumentId() !== NULL)
                {
                    $property_model->setDocumentId($this->getDocumentId());
                }

                $properties[] = $property_model;
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
            throw new \Gc\Exception($e->getMessage());
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
            throw new \Gc\Exception($e->getMessage());
        }
    }
}
