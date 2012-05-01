<?php

namespace Gc\Tab;

use Gc\Db\AbstractTable,
    Gc\Property;

class Model extends AbstractTable
{
    protected $_name = 'tab';

    /**
    * @return FALSE|Model
    */
    public function load($tab_id = NULL, $document_type_id = NULL)
    {
        $this->setId($tab_id);
        $this->setDocumentTypeId($document_type_id);

        $select = $this->select();
        if($this->getDocumentTypeId() !== NULL)
        {
            $select->where('document_type_id = ?',$this->getDocumentTypeId());
        }

        if($this->getId() !== NULL)
        {
            $select->where('id = ?', $this->getId());
        }

        $row = $this->fetchRow($select);
        if(empty($row))
        {
            return FALSE;
        }

        $this->setName($row->name);
        $this->setDescription($row->description);
        $this->setDocumentTypeId($row->document_type_id);
        $this->setOrder($row->order);

        return $this;
    }

    /**
    * @return boolean
    */
    public function save()
    {
        $array_save = array(
            'name' => $this->getName()
            , 'description' => $this->getDescription()
            , 'order' => $this->getOrder()
            , 'document_type_id' => $this->getDocumentTypeId()
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
                $this->update($array_save, $this->getAdapter()->quoteInto('id = ?', $this->getId()));
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
        $tab_id = $this->getId();
        if(!empty($tab_id))
        {
            try
            {
                $properties_collection = new Property\Collection();
                $properties_collection->load(NULL, $tab_id);
                $properties_collection->delete();
                parent::delete('id = '.$tab_id);
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
        $tab_table = new Model();
        $tab_table->setData($array);

        return $tab_table;
    }

    /**
    * @param integer $id
    * @return Model
    */
    static function fromId($id)
    {
        $tab_table = new Model();
        $row = $tab_table->select(array('id' => $id));
        if(!empty($row))
        {
            return $tab_table->setData((array)$row->current());
        }
        else
        {
            return FALSE;
        }
    }

    /**
    * @return Gc\Tab\Model
    */
    public function getProperties()
    {
        if($this->getData('properties') === NULL )
        {
            $properties_collection = new Property\Collection();
            $properties_collection->load($this->getDocumentTypeId(), $this->getId());

            $this->setData('properties', $properties_collection->getProperties());
        }

        return $this->getData('properties');
    }
}
