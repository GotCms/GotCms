<?php
/**
 * @author Rambaud Pierre
 *
 */
class Es_Model_DbTable_Tab_Model extends Es_Db_Table
{
    protected $_name = 'tabs';

    public function init()
    {
    }

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
    * @param string $value
    */
    public function setOrder($order = NULL)
    {
        if(empty($order))
        {
            $this->checkOrder($order);
        }

        $this->setData('order', $order);
        return $this;
    }

    /**
    * @return integer
    */
    private function checkOrder(&$order)
    {
        $select = $this->getAdapter()->select()
            ->from($this->_name, array('max_order'=>'MAX("order")'));
        if($this->getDocumentTypeId() !== NULL)
        {
            $select->where('document_type_id = ?',$this->getDocumentTypeId());
        }

        if($this->getId() !== NULL)
        {
            $select->where('id = ?', $this->getId());
        }

        $row = $this->getAdapter()->fetchRow($select);
        if(!empty($row))
        {
            $order = $row['max_order'] + 1;
        }
        else
        {
            $order = 1;
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
            $this->delete('id = '.$id);
            unset($this);

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
