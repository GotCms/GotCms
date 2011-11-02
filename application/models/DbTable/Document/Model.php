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

    /**
    * @param integer $document_id
    */
    public function __construct($document_id = -1)
    {
        $this->setData('document_id', $document_id);
        $this->getChildren();
    }

    /**
    * @param integer $view_id
    * @return Es_Document_Model
    */
    public function setDocumentUrlKey($value)
    {
        $select = $this->select()
            ->where('url_key = ?', $value)
            ->where('parent_id = ? ', (int)$this->getParentId())
            ->where('document_id != ?', (int)$this->getDocumentId());
        $url_key = $this->fetchRow($select);
        if(count($url_key) > 0)
        {
            return FALSE;
        }

        $this->setData('document_url_key', strtolower($value));
        return $this;
    }
    /**
    * @param integer $view_id
    * @return Es_Document_Model
    */
    public function setViewId($view_id)
    {
        if($view_id === null || $view_id == 0)
        {
            $document_type = Es_Model_DbTable_DocumentType_Model::fromId($this->getDocumentTypeId());
            $view_id = $document_type->getDefaultViewId();
        }

        $this->setData('view_id', $view_id);
        return $this;
    }

    public function getView()
    {
        if($this->getData('view') == null)
        {
            $view = Es_Model_DbTable_View_Model::fromId($this->getViewId());
            if($view !== null)
            {
                $this->setData('view',$view->getContent());
            }
        }

        return $this->getData('view');
    }

    /**
    * @param Boolean $flag
    * @return Es_Document_Model
    */
    public function setDocumentStatus($flag)
    {
        if((bool)$flag === TRUE)
        {
            $flag = 'TRUE';
        }
        else
        {
            $flag = 'FALSE';
        }

        $this->setData('document_status', $flag);
        return $this;
    }

    /**
    * @param Boolean $flag
    * @return Es_Document_Model
    */
    public function setDocumentShowInNav($flag)
    {
        if((bool)$flag === TRUE)
        {
            $flag = 'TRUE';
        }
        else
        {
            $flag = 'FALSE';
        }

        $this->setData('document_show_in_nav', $flag);
        return $this;
    }

    /**
    * @return boolean
    */
    public function canShowInNav()
    {
        return  ($this->getDocumentShowInNav() == 'TRUE' ? TRUE : FALSE);
    }

    /**
    * @return boolean
    */
    public function isPublished()
    {
        return  ($this->getDocumentStatus() == 'TRUE' ? TRUE : FALSE);
    }

    /**
    * @param array $values
    * @return Es_Document_Model
    */
    static function fromArray(array $array)
    {
        $d = new Es_Document_Model($array['document_id']);
        $d->setDocumentName($array['document_name'])
        ->setDocumentTypeId($array['document_type_id'])
        ->setDocumentStatus($array['document_status'])
        ->setDocumentDateCreated($array['document_date_created'])
        ->setDocumentShowInNav($array['document_show_in_nav'])
        ->setUserId($array['user_id'])
        ->setLayoutId($array['layout_id'])
        ->setParentId($array['parent_id'])
        ->setViewId($array['view_id'])
        ->setDocumentUrlKey($array['document_url_key']);
        return $d;
    }

    /**
    * @param array $document_id
    * @return Es_Document_Model|null
    */
    static function fromId($document_id)
    {
        $select = $this->select()
            ->where('id = ?', (int)$document_id);
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
    * @param array $urlKey
    * @return Es_Document_Model|null
    */
    static function fromUrlKey($urlKey)
    {
        $this = Zend_Registry::get('db');
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
        $arraySave = array(
            'name' => $this->getName()
            , 'document_url_key' => $this->getDocumentUrlKey()
            , 'document_status' => $this->getDocumentStatus()
            , 'document_show_in_nav' => $this->getDocumentShowInNav()
            , 'user_id' => (int)$this->getUserId()
            , 'document_type_id' => (int)$this->getDocumentTypeId()
            , 'view_id' => (int)$this->getViewId()
            , 'layout_id' => (int)$this->getLayoutId()
            , 'parent_id' => (int)$this->getParentId()
        );

        try
        {
            if($this->getId() == -1)
            {
                $arraySave['created_at'] = new Zend_Db_Expr('NOW()');
                $this->setId($this->insert($arraySave));
            }
            else
            {
                $this->update($arraySave, 'id = '.$this->getId());
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
        if($this->getDocumentId() !== null)
        {
            $this->beginTransaction();
            try
            {
                if(parent::delete('id = '.$this->getId()))
                {
                    $this->getAdapter()->delete('properties_value', 'document_id = '.$this->getId());
                    unset($this);
                    $this->commit();

                    return TRUE;
                }
            }
            catch (Exception $e)
            {
                $this->rollBack();
                Es_Error::set(get_class($this), $e);
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
        if($this->getData('children') === null)
        {
            $children = new Es_Model_DbTable_Document_Collection($this->getId());
            $this->setData('children', $children->getChildren());
        }

        return $this->getData('children');
    }

    /* (non-PHPdoc)
    * @see include/Es/Interface/Es_Interface_Iterable#getIcon()
    */
    public function getIcon()
    {
        if($this->_icon === null)
        {
            $select = $this->select()
                ->where('document_type_id = ?', $this->getDocumentTypeId());
            $icon = $this->fetchRow($select);
            if(!empty($icon))
            {
                $icon = Es_Model_DbTable_Media_Icon_Model::fromId($icon);
                $this->_icon = $icon->getIconUrl();
            }
        }

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
        return 'javascript:loadController(\''.Zend_Controller_Action_HelperBroker::getStaticHelper('url')->url(array('action' => 'edit')).'/type/document/id/'.$this->getId().'\')';
    }

}
