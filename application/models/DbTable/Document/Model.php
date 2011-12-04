<?php

/**
 * Es Object
 *
 * @category       Es_Model_DbTable
 * @package        Es_Model_DbTable_Document_Model
 * @author          RAMBAUD Pierre
 */
class Es_Model_DbTable_Document_Model extends Es_Db_Table implements Es_Interface_Iterable
{
    protected $_icon;
    protected $_name = 'documents';

    const STATUS_DISABLE     = 0;
    const STATUS_ENABLE      = 1;

    /**
    * @param integer $document_id
    */
    public function init($document_id = NULL)
    {
        if(!empty($document_id))
        {
            $this->setData('document_id', $document_id);
        }

        $this->getChildren();
    }


    public function getView()
    {
        if($this->getData('view') == NULL)
        {
            $view = Es_Model_DbTable_View_Model::fromId($this->getViewId());
            if($view !== NULL)
            {
                $this->setData('view',$view->getContent());
            }
        }

        return $this->getData('view');
    }

    /**
    * @return boolean
    */
    public function showInNav()
    {
        return (bool)$this->getData('show_in_nav') != FALSE ? TRUE : FALSE;
    }

    /**
    * @return boolean
    */
    public function getStatus()
    {
        return (bool)$this->getData('status') != FALSE ? TRUE : FALSE;
    }

    /**
    * @param array $values
    * @return Es_Model_DbTable_Document_Model
    */
    static function fromArray(array $array)
    {
        $document = new Es_Model_DbTable_Document_Model();
        $document->setData($array);

        return $document;
    }

    /**
    * @param array $document_id
    * @return Es_Model_DbTable_Document_Model | FALSE
    */
    static function fromId($document_id)
    {
        $document = new Es_Model_DbTable_Document_Model();
        $select = $document->select()
            ->where('id = ?', (int)$document_id);
        $document = $document->fetchRow($select);
        if(!empty($document))
        {
            return self::fromArray($document->toArray());
        }
        else
        {
            return FALSE;
        }
    }

    /**
    * @return Integer
    */
    public function getUserId()
    {
        $user_id = $this->getData('user_id');
        if(empty($user_id))
        {
            $this->setData('user_id', Zend_Registry::get('user_id'));
        }

        return $this->getData('user_id');
    }

    /**
    * @param array $urlKey
    * @return Es_Model_DbTable_Document_Model | FALSE
    */
    static function fromUrlKey($urlKey)
    {
        $select = $this->select()
            ->where('url_key = ?', $urlKey);
        $document = $this->fetchRow($select);
        if(!empty($document))
        {
            return self::fromArray($document);
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
        $array_save = array(
            'name' => $this->getName()
            , 'url_key' => $this->getUrlKey()
            , 'status' => $this->getStatus() === TRUE ? 'TRUE' : 'FALSE'
            , 'show_in_nav' => $this->showInNav() === TRUE ? 'TRUE' : 'FALSE'
            , 'user_id' => (int)$this->getUserId()
            , 'document_type_id' => (int)$this->getDocumentTypeId() == 0 ? NULL : (int)$this->getDocumentTypeId()
            , 'view_id' => (int)$this->getViewId() == 0 ? NULL : (int)$this->getViewId()
            , 'layout_id' => (int)$this->getLayoutId() == 0 ? NULL : (int)$this->getLayoutId()
            , 'parent_id' => (int)$this->getParentId() == 0 ? NULL : (int)$this->getParentId()
        );

        try
        {
            $document_id = $this->getId();
            if(empty($document_id))
            {
                $arraySave['created_at'] = new Zend_Db_Expr('NOW()');
                $this->setId($this->insert($array_save));
            }
            else
            {
                $this->update($array_save, 'id = '.$this->getId());
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
        $document_id = $this->getId();
        if(!empty($document_id))
        {
            try
            {
                if(parent::delete('id = '.$this->getId()))
                {
                    $this->getAdapter()->delete('properties_value', 'document_id = '.$this->getId());
                    unset($this);

                    return TRUE;
                }
            }
            catch (Exception $e)
            {
                Es_Error::set(get_class($this), $e);
            }
        }

        return FALSE;
    }


    /* (non-PHPdoc)
    * @see include/Es/Interface/Es_Interface_Iterable#getName()
    */
    public function getName()
    {
        return $this->getData('name');
    }

    /* (non-PHPdoc)
    * @see include/Es/Interface/Es_Interface_Iterable#getId()
    */
    public function getId()
    {
        return $this->getData('id');
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
        if($this->getData('children') === NULL)
        {
            $children = new Es_Model_DbTable_Document_Collection();
            $children->load($this->getId());
            $this->setData('children', $children->getChildren());
        }

        return $this->getData('children');
    }

    /* (non-PHPdoc)
    * @see include/Es/Interface/Es_Interface_Iterable#getIcon()
    */
    public function getIcon()
    {
        if($this->getData('icon') === NULL)
        {
            if($this->getIconId() === NULL)
            {
                $children = $this->getChildren();
                if(empty($children))
                {
                    $this->setData('icon', 'file');
                }
                else
                {
                    $this->setData('icon', 'folder');
                }
            }
            else
            {
                $db = $this->getAdapter();
                $select_icon = $db->select()->from(array('i' => 'icons'))
                    ->where('id = ?', $this->getIconId());
                $icon = $db->fetchRow($select_icon);
                $this->setData('icon', $icon['filename']);
            }
        }

        return $this->getData('icon');

        return $this->_icon;
    }

    /* (non-PHPdoc)
    * @see include/Es/Interface/Es_Interface_Iterable#getIterableId()
    */
    public function getIterableId()
    {
        return 'document_'.$this->getId();
    }

    /* (non-PHPdoc)
    * @see include/Es/Interface/Es_Interface_Iterable#getUrl()
    */
    public function getUrl()
    {
        return '';
    }
}
