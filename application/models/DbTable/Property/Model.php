<?php
/**
 * @author Rambaud Pierre
 *
 */
class Es_Model_DbTable_Property_Model extends Es_Db_Table
{

    protected         $_value;
    protected         $_name = 'properties';
    /**
    * @param integer $defaultId
    */
    public function load()
    {
    }

    /**
    * @param Boolean $value
    * @return mixte boolean, this
    */
    public function isRequired($value = NULL)
    {
        if($value === NULL)
        {
            return $this->getData('is_required');
        }

        if($value === TRUE)
        {
            $this->setData('is_required', TRUE);
        }
        else
        {
            $this->setData('is_required', FALSE);
        }

        return $this;
    }

    public function getOrder()
    {
        if($this->getData('order') === NULL)
        {
            $this->setData('order', 1);
        }

        return $this->getData('order');
    }

    /**
    * @param mixte $value
    * @return $this
    */
    public function setValue($value)
    {
        if(empty($this->_value))
        {
            $this->getValue();
        }

        $this->_value->setValue($value);
        return $this;
    }

    /**
    * @return mixte
    */
    public function getValue()
    {
        if(empty($this->_value))
        {
            $property_value = new Es_Model_DbTable_Property_Value_Model();
            $property_value->load(NULL, $this->getDocumentId(), $this->getId());

            $this->_value = $property_value;
        }

        return $this->_value->getValue();
    }

    public function saveValue()
    {
        return $this->_value->save();
        return FALSE;
    }

    /**
    * @param unknown_type $value
    * @return Es_Component_Model
    */
    public function setPropertyValue($value)
    {
        $this->_value = $value;
        return $this;
    }

    /**
    * @param unknown_type $value
    * @return Es_Component_Value_Model
    */
    public function getPropertyValue($value = FALSE)
    {
        return $this->_value;
    }

    /**
    * @return boolean
    */
    public function save()
    {
        $array_save = array(
            'name' => $this->getName()
            , 'description' => $this->getDescription()
            , 'identifier' => $this->getIdentifier()
            , 'required' => $this->isRequired() == TRUE ? 'TRUE' : 'FALSE'
            , 'order' => $this->getOrder()
            , 'tab_id' => $this->getTabId()
            , 'datatype_id' => $this->getDatatypeId()
        );

        try
        {
            $id = $this->getId();
            if(empty($id))
            {
                $this->setId($this->insert($array_save));
            }
            else
            {
                $this->update($array_save, $this->getAdapter()->quoteInto('id =  ?',$id));
            }

            return TRUE;
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
    /**
    * @return boolean
    */
    public function delete()
    {
        $id = $this->getId();
        if(!empty($id))
        {
            try
            {
                parent::delete($this->getAdapter()->quoteInto('id = ?', $id));
                $this->getAdapter()->delete('properties_values', $this->getAdapter()->quoteInto('property_id = ?', $id));
            }
            catch(Exception $e)
            {
                throw new Es_Exception($e->getMessage());

            }
            return TRUE;
        }

        return FALSE;
    }
    /**
    * @param array $array
    * @return Es_Component_Model
    */
    static function fromArray(Array $array)
    {
        $property = new Es_Model_Dbtable_Property_Model();
        $property->setData($array);
        return $property;
    }

    /**
    * @param integer $id
    * @return Es_Component_Model
    */
    static function fromId($id)
    {
        $property = new Es_Model_Dbtable_Property_Model();
        $select = $property->select();
        $select->where('id = ?', (int)$id);
        $property = $this->fetchRow($select);
        if(!empty($property))
        {
            return self::fromArray($property);
        }
        else
        {
            return FALSE;
        }
    }
}
