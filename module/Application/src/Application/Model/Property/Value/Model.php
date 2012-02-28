<?php

namespace Application\Model\Property\Value;

use Es\Db\AbstractTable;

class Model extends AbstractTable
{
    protected $_name = 'properties_values';

    public function load($value_id = NULL, $document_id = NULL, $property_id = NULL)
    {
        $this->setId($value_id);
        $this->setDocumentId($document_id);
        $this->setPropertyId($property_id);
        if(!empty($document_id) and !empty($property_id))
        {
            $prevalue_value = $this->fetchRow($this->select()->where('property_id = ?', $property_id)->where('document_id = ?', $document_id));
            if(!empty($prevalue_value->id))
            {
                $this->setId($prevalue_value->id);
                $this->setValue($prevalue_value->value);
            }
        }
    }

    /**
    * @param array $array
    * @return Es_Component_Property_Model
    */
    static function fromArray(Array $array)
    {
        $property_value_table = new Model($array);
        $property_value_table->setData($array);

        return $property_value_table;
    }

    /**
    * @param integer $property_id
    * @return Es_Component_Property_Model
    */
    static function fromId($property_value_id)
    {
        $property_value_table = new Model($array);
        $select = $property_value_table->select();
        $select->where('id = ?', (int)$property_value_id);
        $row = $property_value_table->fetchRow($select);
        if(!empty($row))
        {
            return $property_value_table->setData($row->toArray());
        }
        else
        {
            return NULL;
        }
    }

    public function save()
    {
        $array_save = array(
            'value' => $this->getValue()
            , 'document_id' => $this->getDocumentId()
            , 'property_id' => $this->getPropertyId()
        );

        $id = $this->getId();
        try
        {
            if(empty($id))
            {
                $this->setId($this->insert($array_save));
            }
            else
            {
                $this->update($array_save, $this->getAdapter()->quoteInto('id = ?', $id));
            }

            return $id;
        }
        catch (Exception $e)
        {
            /**
            * TODO(Make Es_Error)
            */
            Es_Error::set(get_class($this),$e);
        }
        return FALSE;
    }
}
