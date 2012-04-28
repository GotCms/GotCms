<?php
namespace Gc\View;

use Gc\Db\AbstractTable,
    Gc\Component\IterableInterface;

class Model extends AbstractTable implements IterableInterface
{
    protected $_name = 'views';

    /**
    * @param integer $id
    * @return Model
    */
    public function init($id = NULL)
    {
        $this->setId($id);
    }

    /**
    * @param array $view
    * @return Model
    */
    static function fromArray(Array $array)
    {
        $view_table = new Model();
        $view_table->setData($array);

        return $view_table;
    }

    /**
    * @param integer $id
    * @return Model
    */
    static function fromId($id)
    {
        $view_table = new Model();
        $row = $view_table->select(array('id' => $id));
        if(!empty($row))
        {
            return $view_table->setData((array)$row->current());
        }
        else
        {
            return FALSE;
        }
    }

    /**
    * @return boolean
    */
    public function save()
    {
        $array_save = array('name' => $this->getName(),
            'identifier' => $this->getIdentifier(),
            'description' => $this->getDescription(),
            'content' => $this->getContent(),
            'updated_at' => new \Zend\Db\Expr('NOW()')
        );

        try
        {
            if($this->getId() == NULL)
            {
                $array_save['created_at'] = new \Zend\Db\Expr('NOW()');
                $id = $this->insert($array_save);
                $this->setId($id);
            }
            else
            {
                $this->update($array_save, 'id = '.(int)$this->getId());
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
            if(parent::delete(sprintf('id = %d', $id)))
            {
                unset($this);
                return TRUE;
            }
        }

        return FALSE;
    }

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
        return parent::getId();
    }

    /* (non-PHPdoc)
    * @see include/Gc/Interface/Gc\Component\IterableInterface#getIterableId()
    */
    public function getIterableId()
    {
        return 'view-'.$this->getId();
    }

    /* (non-PHPdoc)
    * @see include/Gc/Interface/Gc\Component\IterableInterface#getName()
    */
    public function getName()
    {
        return parent::getName();
    }

    /* (non-PHPdoc)
    * @see include/Gc/Interface/Gc\Component\IterableInterface#getUrl()
    */
    public function getUrl()
    {
        return '';
    }

    /* (non-PHPdoc)
    * @see include/Gc/Interface/Gc\Component\IterableInterface#getIcon()
    */
    public function getIcon()
    {
        return 'file';
    }
}
