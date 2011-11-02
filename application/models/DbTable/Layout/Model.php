<?php
class Es_Model_DbTable_Layout_Model extends Es_Db_Table implements Es_Interface_Iterable
{
    protected $_name = 'layouts';

    /**
    * @param integer $id
    * @return Es_Model_DbTable_Layout_Model
    */
    public function init($id = NULL)
    {
        $this->setId($id);

        return $this;
    }

    /**
    * @param array $layout
    * @return Es_Model_DbTable_Layout_Model
    */
    static function fromArray(Array $array)
    {
        $layout = new Es_Model_DbTable_Layout_Model();
        $layout->init($array['id']);
        $layout->setName($array['name']);
        $layout->setIdentifier($array['identifier']);
        $layout->setDescription($array['description']);
        $layout->setContent($array['content']);
        $layout->setCreatedAt($array['created_at']);
        $layout->setUpdatedAt($array['updated_at']);

        return $layout;
    }


    /**
    * @param integer $id
    * @return Es_Model_DbTable_Layout_Model
    */
    static function fromId($id)
    {
        $layout_table = new Es_Model_DbTable_Layout_Model();
        $select = $layout_table->select();
        $select->where('id = ?', $id);
        $layout = $layout_table->fetchRow($select);
        if(!empty($layout))
        {
            return self::fromArray($layout->toArray());
        }
        else
        {
            return FALSE;
        }
    }

    /**
    * @return unknown_type
    */
    public function save()
    {
        $arraySave = array('name' => $this->getName(),
            'identifier' => $this->getIdentifier(),
            'description' => $this->getDescription(),
            'content' => $this->getContent(),
            'updated_at' => new Zend_Db_Expr('NOW()')
        );

        try
        {
            $id = $this->getId();
            if(empty($id))
            {
                $arraySave['created_at'] = new Zend_Db_Expr('NOW()');
                $this->setId($this->insert($arraySave));
            }
            else
            {
                $this->update($arraySave, sprintf('id = %d', $id));
            }

            return $this->getId();
        }
        catch (Exception $e)
        {
            /**
            * TODO(Make Es_Error)
            */
            Es_Error::set(get_class($this), $e);
        }

        return FALSE;
    }

    public function delete()
    {
        if(!empty($this->_id))
        {
            if(parent::delete('id = '.$this->getId()))
            {
                unset($this);
                return TRUE;
            }
        }

        return FALSE;
    }

    /*
    * Es_Interface Methods
    */
    /* (non-PHPdoc)
    * @see include/Es/Interface/Es_Interface_Iterable#getParent()
    */
    public function getParent()
    {
        return FALSE;
    }

    /* (non-PHPdoc)
    * @see include/Es/Interface/Es_Interface_Iterable#getChildren()
    */
    public function getChildren()
    {
        return FALSE;
    }

    /* (non-PHPdoc)
    * @see include/Es/Interface/Es_Interface_Iterable#getId()
    */
    public function getId()
    {
        return $this->getData('id');
    }

    /* (non-PHPdoc)
    * @see include/Es/Interface/Es_Interface_Iterable#getIterableId()
    */
    public function getIterableId()
    {
        return 'layout-'.$this->getId();
    }

    /* (non-PHPdoc)
    * @see include/Es/Interface/Es_Interface_Iterable#getName()
    */
    public function getName()
    {
        return $this->getData('name');
    }

    /* (non-PHPdoc)
    * @see include/Es/Interface/Es_Interface_Iterable#getUrl()
    */
    public function getUrl()
    {
        return 'javascript:loadController(\''.Zend_Controller_Action_HelperBroker::getStaticHelper('url')->url(array('controller'=>'development','action'=>'edit')).'/type/layout/id/'.$this->getId().'\')';
    }

    public function getIcon()
    {
        return 'file';
    }
}
