<?php
namespace Application\Model\View;

use Es\Db\AbstractTable,
    Es\Component\IterableInterface;

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
        $view = new Model();
        $view->init($array['id']);
        $view->setName($array['name']);
        $view->setIdentifier($array['identifier']);
        $view->setDescription($array['description']);
        $view->setContent($array['content']);
        $view->setCreatedAt($array['created_at']);
        $view->setUpdatedAt($array['updated_at']);

        return $view;
    }

    /**
    * @param integer $id
    * @return Model
    */
    static function fromId($id)
    {
        $view_table = new Model();
        $select = $view_table->select()
            ->where('id = ?', $id);
        $view = $view_table->fetchRow($select);
        if(!empty($view))
        {
            return self::fromArray($view->toArray());
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
        return parent::getId();
    }

    /* (non-PHPdoc)
    * @see include/Es/Interface/Es_Interface_Iterable#getIterableId()
    */
    public function getIterableId()
    {
        return 'view-'.$this->getId();
    }

    /* (non-PHPdoc)
    * @see include/Es/Interface/Es_Interface_Iterable#getName()
    */
    public function getName()
    {
        return parent::getName();
    }

    /* (non-PHPdoc)
    * @see include/Es/Interface/Es_Interface_Iterable#getUrl()
    */
    public function getUrl()
    {
        return '';
    }

    /* (non-PHPdoc)
    * @see include/Es/Interface/Es_Interface_Iterable#getIcon()
    */
    public function getIcon()
    {
        return 'file';
    }
}
