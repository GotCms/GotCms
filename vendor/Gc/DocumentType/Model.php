<?php

namespace Gc\DocumentType;

use Gc\Db\AbstractTable,
    Gc\Component\IterableInterface,
    Gc\User,
    Gc\Tab,
    Gc\View;

class Model extends AbstractTable implements IterableInterface
{
    protected $_name = 'document_type';

    public function getUser()
    {
        if($this->getData('user') === NULL AND $this->getUserId() != NULL)
        {
            $this->setData('user', new User\Model($this->getUserId()));
        }

        return $this->getData('user');
    }

    public function addView($view_id)
    {
        $this->_views[] = $view_id;
        return $this;
    }

    public function addViews($views)
    {
        $this->_views += $views;
        return $this;
    }

    /**
    * @return Gc\Tab\Collection
    */
    public function getTabs()
    {
        if($this->getData('tabs') === NULL )
        {
            $tabs_collection = new Tab\Collection();
            $tabs_collection->load($this->getId());

            $this->setData('tabs', $tabs_collection->getTabs());
        }

        return $this->getData('tabs');
    }

    public function getAvailableViews()
    {
        if($this->getData('available_views') === NULL)
        {
            $views_collection = new View\Collection();
            $views_collection->init($this->getId());

            $this->setData('available_views', $views_collection);
        }

        return $this->getData('available_views');
    }

    public function save()
    {
        $array_save = array(
            'name' => $this->getName()
            , 'updated_at' => date('Y-m-d H:i:s')
            , 'description' => $this->getDescription()
            , 'icon_id' => $this->getIconId()
            , 'default_view_id' => $this->getDefaultViewId()
            , 'user_id' => $this->getUserId()
        );

        try
        {
            $id = $this->getId();
            if(empty($id))
            {
                $array_save['created_at'] = date('Y-m-d H:i:s');
                $this->insert($array_save);
                $this->setId($this->getLastInsertId());
            }
            else
            {
                $this->update($array_save, $this->getAdapter()->quoteInto('id = ? ', $this->getId()));
            }

            $views = $this->getViews();
            if(!empty($views))
            {
                $db = $this->getAdapter();
                $db->delete('document_type_views', $db->quoteInto('document_type_id = ?', $this->getId()));
                foreach($views as $view);
                {
                    $db->insert('document_type_views', array('document_type_id' => $this->getId(), 'view_id' => $view));
                }
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
        $document_type_id = $this->getId();
        if(!empty($document_type_id))
        {
            $tab_collection = new Gc\Tab\Collection();
            $tab_collection->load($document_type_id);
            $tab_collection->delete();
            $this->getAdapter()->delete('document_type_views', $this->getAdapter()->quoteInto('document_type_id = ?', $document_type_id));
            parent::delete('id = '.$document_type_id);

            return TRUE;
        }

        return FALSE;
    }

    /**
    * @param array $array
    * @return Gc\DocumentType\Model
    */
    static function fromArray(Array $array)
    {
        $document_type_table = new Model();
        $document_type_table->setData($array);

        return $document_type_table;
    }

    /**
    * @param integer $document_type_id
    * @return Gc\DocumentType\Model
    */
    static function fromId($document_type_id)
    {
        $document_type_table = new Model();
        $row = $document_type_table->select(array('id' => $document_type_id));
        if(!empty($row))
        {
            return $document_type_table->setData((array)$row->current());
        }
        else
        {
            return FALSE;
        }
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

    public function getId()
    {
        return parent::getId();
    }

    public function getName()
    {
        return parent::getName();
    }

    /* (non-PHPdoc)
    * @see include/Gc/Interface/Gc\Component\IterableInterface#getIterableId()
    */
    public function getIterableId()
    {
        return 'documenttype_'.$this->getId();
    }

    /* (non-PHPdoc)
    * @see include/Gc/Interface/Gc\Component\IterableInterface#getUrl()
    */
    public function getUrl()
    {
        return 'javascript:loadController(\''.Zend_Controller_Action_HelperBroker::getStaticHelper('url')->url(array('action' => 'edit')).'/type/documenttype/id/'.$this->getId().'\')';
    }

    /* (non-PHPdoc)
    * @see include/Gc/Interface/Gc\Component\IterableInterface#getIcon()
    */
    public function getIcon()
    {
        if($this->_icon_url === NULL)
        {
            $icon = Gc_Media_Icon_Model::fromId($this->_documentType_icon);
            $this->_icon_url = $icon->getIconUrl();
        }

        return $this->_icon_url;
    }
}
