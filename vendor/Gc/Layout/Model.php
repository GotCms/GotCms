<?php

namespace Gc\Layout;

use Gc\Db\AbstractTable,
    Gc\Component\IterableInterface;

class Model extends AbstractTable implements IterableInterface
{
    protected $_name = 'layout';

    /**
    * @param integer $id
    * @return Model
    */
    public function init($id = NULL)
    {
        $this->setId($id);

        return $this;
    }

    /**
    * @param array $layout
    * @return Model
    */
    static function fromArray(Array $array)
    {
        $layout_table = new Model();
        $layout_table->setData($array);

        return $layout_table;
    }


    /**
    * @param integer $id
    * @return Model
    */
    static function fromId($id)
    {
        $layout_table = new Model();
        $row = $layout_table->select(array('id' => $id));
        if(!empty($row))
        {
            return $layout_table->setData((array)$row->current());
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
        $array_save = array('name' => $this->getName(),
            'identifier' => $this->getIdentifier(),
            'description' => $this->getDescription(),
            'content' => $this->getContent(),
            'updated_at' => date('Y-m-d H:i:s')
        );

        try
        {
            $id = $this->getId();
            if(empty($id))
            {
                $array_save['created_at'] = date('Y-m-d H:i:s');
                $this->setId($this->insert($array_save));
            }
            else
            {
                $this->update($array_save, sprintf('id = %d', $id));
            }

            return $this->getId();
        }
        catch (Exception $e)
        {
            /**
            * TODO(Make \Gc\Error)
            */
            \Gc\Error::set(get_class($this), $e);
        }

        return FALSE;
    }

    public function delete()
    {
        $id = $this->getId();
        if(!empty($id))
        {
            if(parent::delete('id = '.$id))
            {
                unset($this);
                return TRUE;
            }
        }

        return FALSE;
    }

    /*
    * Gc\Component\IterableInterface Methods
    */
    /* (non-PHPdoc)
    * @see include/Gc/Interface/Gc\Component\IterableInterface#getParent()
    */
    public function getParent()
    {
        return FALSE;
    }

    /* (non-PHPdoc)
    * @see include/Gc/Interface/Gc\Component\IterableInterface#getChildren()
    */
    public function getChildren()
    {
        return FALSE;
    }

    /* (non-PHPdoc)
    * @see include/Gc/Interface/Gc\Component\IterableInterface#getId()
    */
    public function getId()
    {
        return $this->getData('id');
    }

    /* (non-PHPdoc)
    * @see include/Gc/Interface/Gc\Component\IterableInterface#getIterableId()
    */
    public function getIterableId()
    {
        return 'layout-'.$this->getId();
    }

    /* (non-PHPdoc)
    * @see include/Gc/Interface/Gc\Component\IterableInterface#getName()
    */
    public function getName()
    {
        return $this->getData('name');
    }

    /* (non-PHPdoc)
    * @see include/Gc/Interface/Gc\Component\IterableInterface#getUrl()
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
