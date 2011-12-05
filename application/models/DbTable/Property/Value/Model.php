<?php
class Es_Model_DbTable_Property_Value_Model extends Es_Db_Table
{
    protected $_name = 'properties_values';

    public function load($property_value_id = NULL, $document_id = NULL, $property_id = NULL)
    {
        $this->setPropertyValueId($property_value_id);
        $this->setDocumentId($document_id);
        $this->setPropertyId($property_id);
    }

    /**
    * @param array $array
    * @return Es_Component_Property_Model
    */
    static function fromArray(Array $array)
    {
        if(!empty($array['property_value_id']) and !empty($array['document_id']) and !empty($array['property_id']))
        {
            $pv = new Es_Component_Property_Value_Model($array);
            $pv->setData($array);
        }
        else
        {
            $pv = NULL;
        }

        return $pv;
    }

    /**
    * @param integer $property_id
    * @return Es_Component_Property_Model
    */
    static function fromId($property_value_id)
    {
        $pv = new Es_Component_Property_Value_Model($array);
        $select = $pv->select();
        $select->where('property_value_id = ?', (int)$property_value_id);
        $property = $pv->fetchRow($select);
        if(!empty($property))
        {
            return self::fromArray($property->toArray());
        }
        else
        {
            return NULL;
        }
    }

    public function save()
    {
        $array_save = array('value'=>$this->getvalue(),
            'document_id'=>$this->getDocumentId(),
            'property_id'=>$this->getpropertyId()
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
                $this->update($array_save, $this->getAdapter()->quoteInto('property_value_id = ?', $id));
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
