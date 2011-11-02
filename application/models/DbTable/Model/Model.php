<?php

/**
 * Es_Model_DbTable_Model_Model
 *
 * @category       Es_Model_DbTable
 * @package        Es_Model_DbTable_Model_Model
 * @author          RAMBAUD Pierre
 */
class Es_Model_DbTable_Model_Model extends Es_Db_Table implements Es_Interface_Iterable
{
    protected $_name = 'models';

    /**
    * @param integer $defaultId
    */
    public function init($id = NULL)
    {
        return $this->setId($id);
    }

    /**
    * @param array $array
    * @return Es_Model_Model
    */
    static function fromArray(Array $array)
    {
        $model = new Es_Model_DbTable_Model_Model();
        $model->init($array['id']);
        $model->setName($array['name']);
        $model->setDescription($array['description']);
        $model->setIdentifier($array['identifier']);

        return $model;
    }

    /**
    * @param integer $id
    * @return Es_Model_Model
    */
    static function fromId($id)
    {
        $model = new Es_Model_DbTable_Model_Model();
        $select = $model->select()
            ->where('id = ?', $id);
        $model = $model->fetchRow($select);
        if(!empty($model))
        {
            return self::fromArray($model->toArray());
        }
        else
        {
            return FALSE;
        }
    }

    public function save()
    {
        $arraySave = array(
            'name'=>$this->getName()
            , 'identifier'=>$this->getIdentifier()
            , 'description'=>$this->getDescription()
        );

        try
        {
            $id = $this->getId();
            if(empty($id))
            {
                $this->setId($this->insert($arraySave));
            }
            else
            {
                $this->update($arraySave, sprintf('id = %d', $id));
            }

            return $id;
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
        $id = $this->getId();
        if(!empty($id))
        {
            if(parent::delete(sprintf('id = %d', $id)))
            {
                unset($this);
                return TRUE;
            }
        }

        return FALSE;
    }

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
        return 'model_'.$this->getId();
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
        return 'javascript:loadController(\''.Zend_Controller_Action_HelperBroker::getStaticHelper('url')->url(array('controller'=>'development','action'=>'edit')).'/type/model/id/'.$this->getId().'\')';
    }

    /* (non-PHPdoc)
    * @see include/Es/Interface/Es_Interface_Iterable#getIcon()
    */
    public function getIcon()
    {
        return 'file';
    }
}
