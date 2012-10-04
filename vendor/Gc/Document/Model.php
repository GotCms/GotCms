<?php
/**
 * This source file is part of GotCms.
 *
 * GotCms is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * GotCms is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License along
 * with GotCms. If not, see <http://www.gnu.org/licenses/lgpl-3.0.html>.
 *
 * PHP Version >=5.3
 *
 * @category    Gc
 * @package     Library
 * @subpackage  Document
 * @author      Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license     GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link        http://www.got-cms.com
 */

namespace Gc\Document;

use Gc\Db\AbstractTable,
    Gc\Component\IterableInterface,
    Gc\DocumentType,
    Gc\Media\Icon,
    Gc\View;
/**
 * Document Model
 */
class Model extends AbstractTable implements IterableInterface
{
    /**
     * Table name
     * @var string
     */
    protected $_name = 'document';

    /**
     * @const STATUS_DISABLE
     */
    const STATUS_DISABLE     = 0;

    /**
     * @const STATUS_ENABLE
     */
    const STATUS_ENABLE      = 1;

    /**
     * Initiliaze document
     * @param integer $document_id
     * @return void
     */
    public function init($document_id = NULL)
    {
        if(!empty($document_id))
        {
            $this->setData('document_id', $document_id);
        }

        $this->getChildren();
    }

    /**
     * get View Model
     * @return \Gc\View\Model
     */
    public function getView()
    {
        if($this->getData('view') == NULL)
        {
            $view = View\Model::fromId($this->getViewId());
            if($view !== NULL)
            {
                $this->setData('view',$view->getContent());
            }
        }

        return $this->getData('view');
    }

    /**
     * Get Document type
     * @return \Gc\DocumentType\Model
     */
     public function getDocumentType()
     {
         if($this->getData('document_type') === NULL)
         {
             $this->setData('document_type', DocumentType\Model::fromId($this->getDocumentTypeId()));
         }

         return $this->getData('document_type');
     }

    /**
     * Define if document is show in navigation
     * @param optional $is_show
     * @return boolean
     */
    public function showInNav($is_show = NULL)
    {
        if(!is_null($is_show))
        {
            $this->setData('show_in_nav', $is_show);
        }

        return (bool)$this->getData('show_in_nav') != FALSE ? TRUE : FALSE;
    }

    /**
     * Test if status is equal to self::STATUS_ENABLE
     * @return boolean
     */
    public function isPublished()
    {
        return $this->getStatus() == self::STATUS_ENABLE;
    }

    /**
     * Initialize document from array
     * @param array $array
     * @return \Gc\Document\Model
     */
    static function fromArray(array $array)
    {
        $document_table = new Model();
        $document_table->setData($array);

        return $document_table;
    }

    /**
     * Initiliaze document from id
     * @param array $document_id
     * @return \Gc\Document\Model
     */
    static function fromId($document_id)
    {
        $document_table = new Model();
        $row = $document_table->select(array('id' => $document_id));
        $current = $row->current();
        if(!empty($current))
        {
            return $document_table->setData((array)$current);
        }
        else
        {
            return FALSE;
        }
    }

    /**
     * Initiliaze from url and parent
     * @param string $url_key
     * @param mixed $parent_id
     * @return \Gc\Document\Model
     */
    static function fromUrlKey($url_key, $parent_id = NULL)
    {
        $document_table = new Model();
        $sql_data = array('url_key' => $url_key);
        if(!empty($parent_id))
        {
            $sql_data['parent_id'] = $parent_id;
        }

        $rowset = $document_table->select($sql_data);
        $row = $rowset->current();
        if(!empty($row))
        {
            return $document_table->setData((array)$row);
        }
        else
        {
            return FALSE;
        }
    }

    /**
     * Save Model
     * @return integer
     */
    public function save()
    {
        $array_save = array(
            'name' => $this->getName(),
            'url_key' => $this->getUrlKey(),
            'updated_at' => date('Y-m-d H:i:s'),
            'status' => ($this->getStatus() === NULL ? self::STATUS_DISABLE : $this->getStatus()),
            'sort_order' => (int)$this->getSortOrder(),
            'show_in_nav' => $this->showInNav() === TRUE ? 'TRUE' : 'FALSE',
            'user_id' => (int)$this->getUserId(),
            'document_type_id' => (int)$this->getDocumentTypeId() == 0 ? NULL : (int)$this->getDocumentTypeId(),
            'view_id' => (int)$this->getViewId() == 0 ? NULL : (int)$this->getViewId(),
            'layout_id' => (int)$this->getLayoutId() == 0 ? NULL : (int)$this->getLayoutId(),
            'parent_id' => (int)$this->getParentId() == 0 ? NULL : (int)$this->getParentId(),
        );

        try
        {
            $document_id = $this->getId();
            if(empty($document_id))
            {
                $array_save['created_at'] = date('Y-m-d H:i:s');
                $this->insert($array_save);
                $this->setId($this->getLastInsertId());
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
             * TODO(Make \Gc\Error)
             */
            \Gc\Error::set(get_class($this),$e);
        }

        return FALSE;
    }

    /**
     * Delete document
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
                    $properties_table = new \Zend\Db\TableGateway\TableGateway('property_value', $this->getAdapter());
                    $properties_table->delete(array('document_id' => $this->getId()));
                    unset($this);

                    return TRUE;
                }
            }
            catch (Exception $e)
            {
                \Gc\Error::set(get_class($this), $e);
            }
        }

        return FALSE;
    }

    /**
     * Get document path
     * @return string
     */
    public function getPath()
    {
        $parent = $this->getParent();
        $path = '/' . $this->getUrlKey();
        if(!empty($parent))
        {
            $path = $parent->getPath() . $path;
        }

        return $path;
    }

    /** (non-PHPdoc)
     * @see include \Gc\Component\IterableInterface#getName()
     */
    public function getName()
    {
        return $this->getData('name');
    }

    /** (non-PHPdoc)
     * @see include \Gc\Component\IterableInterface#getId()
     */
    public function getId()
    {
        return $this->getData('id');
    }

    /** (non-PHPdoc)
     * @see include \Gc\Component\IterableInterface#getParent()
     */
    public function getParent()
    {
        $parent_id = $this->getData('parent_id');

        return Model::fromId($parent_id);
    }

    /** (non-PHPdoc)
     * @see include \Gc\Component\IterableInterface#getChildren()
     */
    public function getChildren()
    {
        if($this->getData('children') === NULL)
        {
            $children = new Collection();
            $children->load($this->getId());
            $this->setData('children', $children->getChildren());
        }

        return $this->getData('children');
    }

    /** (non-PHPdoc)
     * @see include \Gc\Component\IterableInterface#getIcon()
     */
    public function getIcon()
    {
        if($this->getData('icon') === NULL)
        {
            $icon = Icon\Model::fromId($this->getDocumentType()->getIconId());
            if(empty($icon))
            {
                return FALSE;
            }

            $this->setData('icon', $icon->getUrl());
        }

        return $this->getData('icon');
    }

    /** (non-PHPdoc)
     * @see include \Gc\Component\IterableInterface#getIterableId()
     */
    public function getIterableId()
    {
        return 'document_'.$this->getId();
    }

    /** (non-PHPdoc)
     * @see include \Gc\Component\IterableInterface#getUrl()
     */
    public function getUrl()
    {
        return \Gc\Registry::get('Application')->getMvcEvent()->getRouter()->assemble(array('id' => $this->getId()), array('name' => 'documentEdit'));
    }
}
