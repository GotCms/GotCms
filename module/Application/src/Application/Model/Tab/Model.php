<?php

namespace Application\Model\Tab;

use Es\Db\AbstractTable;

class Model extends AbstractTable
{
    protected $_name = 'tabs';

    /**
    * @return FALSE|Es_Model_DbTable_Tab_Model
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
                $id = $this->insert($array_save);
                $this->setId($id);
            }
            else
            {
                $this->update($array_save, $this->getAdapter()->quoteInto('id = ?', $id));
            }

            return $this->getId();
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
        $tab_id = $this->getId();
        if(!empty($tab_id))
        {
            try
            {
                $properties_collection = new Es_Model_DbTable_Property_Collection();
                $properties_collection->load(NULL, $tab_id);
                $properties_collection->delete();
                parent::delete('id = '.$tab_id);
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
    * @return Es_Model_DbTable_Tab_Model
    */
    static function fromArray(Array $array)
    {
        $tab = new Es_Model_DbTable_Tab_Model();
        $tab->setData($array);

        return $tab;
    }

    /**
    * @param integer $id
    * @return Es_Model_DbTable_Tab_Model
    */
    static function fromId($id)
    {
        $tab_table = new Es_Model_DbTable_Tab_Model();
        $select = $tab_table->select()
            ->where('id = ?', $id);
        $tab = $tab_table->fetchRow($select);
        if(!empty($tab))
        {
            return self::fromArray($tab->toArray());
        }
        else
        {
            return FALSE;
        }
    }
}
