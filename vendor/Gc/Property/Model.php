<?php

namespace Gc\Property;

use Gc\Db\AbstractTable;

class Model extends AbstractTable
{
    protected         $_value;
    protected         $_name = 'property';
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
            return $this->getData('required');
        }

        if($value === TRUE)
        {
            $this->setData('required', TRUE);
        }
        else
        {
            $this->setData('required', FALSE);
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
        $this->_value->setValue($value);

        return $this;
    }

    public function loadValue()
    {
        $property_value = new Value\Model();
        $property_value->load(NULL, $this->getDocumentId(), $this->getId());

        $this->_value = $property_value;
    }
    /**
    * @return mixte
    */
    public function getValue()
    {
        if(empty($this->_value))
        {
            $this->loadValue();
        }

        return $this->_value->getValue();
    }

    public function saveValue()
    {
        $value = $this->getValue();
        $this->_value->save();
        if(empty($value) and $this->isRequired())
        {
            return FALSE;
        }
        else
        {
            return TRUE;
        }
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
                $this->insert($array_save);
                $this->setId($this->getLastInsertId());
            }
            else
            {
                $this->update($array_save, sprintf('id =  %s', (int)$this->getId()));
            }

            return $this->getId();
        }
        catch (Exception $e)
        {
            /**
            * TODO(Make \Gc\Error)
            */
            \Gc\Error::set(get_class($this),$e);
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
                parent::delete(sprintf('id = %s', (int)$id));
                $table = new \Zend\Db\TableGateway\TableGateway('property_value', $this->getAdapter());
                $result = $table->delete(array('property_id' => (int)$id));
            }
            catch(Exception $e)
            {
                throw new \Gc\Exception($e->getMessage());

            }
            return TRUE;
        }

        return FALSE;
    }
    /**
    * @param array $array
    * @return Model
    */
    static function fromArray(Array $array)
    {
        $property = new Model();
        $property->setData($array);

        return $property;
    }

    /**
    * @param integer $id
    * @return Model
    */
    static function fromId($id)
    {
        $property_table = new Model();
        $row = $property_table->select(array('id' => $id));
        if(!empty($row))
        {
            return $property_table->setData((array)$row->current());
        }
        else
        {
            return FALSE;
        }
    }
}
