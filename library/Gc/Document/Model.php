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
 * @category   Gc
 * @package    Library
 * @subpackage Document
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Gc\Document;

use Gc\Db\AbstractTable,
    Gc\Component\IterableInterface,
    Gc\DocumentType,
    Gc\Media\Icon,
    Gc\Property\Model as PropertyModel,
    Gc\Registry,
    Gc\View,
    Zend\Db\TableGateway\TableGateway,
    Zend\Db\Sql\Predicate\Expression;

/**
 * Document Model
 *
 * @category   Gc
 * @package    Library
 * @subpackage Document
 */
class Model extends AbstractTable implements IterableInterface
{
    /**
     * Table name
     *
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
     *
     * @param integer $document_id
     * @return void
     */
    public function init($document_id = NULL)
    {
        if(!empty($document_id))
        {
            $this->setId($document_id);
        }

        $this->getChildren();
    }

    /**
     * get View Model
     *
     * @return \Gc\View\Model
     */
    public function getView()
    {
        if($this->getData('view') == NULL)
        {
            $view = View\Model::fromId($this->getViewId());
            if($view !== NULL)
            {
                $this->setData('view', $view);
            }
        }

        return $this->getData('view');
    }

    /**
     * Get Document type
     *
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
     *
     * @param boolean $is_show Optional
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
     *
     * @return boolean
     */
    public function isPublished()
    {
        return $this->getStatus() == self::STATUS_ENABLE;
    }

    /**
     * Initialize document from array
     *
     * @param array $array
     * @return \Gc\Document\Model
     */
    static function fromArray(array $array)
    {
        $document_table = new Model();
        $document_table->setData($array);
        $document_table->setOrigData();

        return $document_table;
    }

    /**
     * Initiliaze document from id
     *
     * @param integer $document_id
     * @return \Gc\Document\Model
     */
    static function fromId($document_id)
    {
        $document_table = new Model();
        $row = $document_table->fetchRow($document_table->select(array('id' => (int)$document_id)));
        if(!empty($row))
        {
            $document_table->setData((array)$row);
            $document_table->setOrigData();
            return $document_table;
        }
        else
        {
            return FALSE;
        }
    }

    /**
     * Initiliaze from url and parent
     *
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

        $row = $document_table->fetchRow($document_table->select($sql_data));
        if(!empty($row))
        {
            $document_table->setData((array)$row);
            $document_table->setOrigData();
            return $document_table;
        }
        else
        {
            return FALSE;
        }
    }

    /**
     * Save Model
     *
     * @return integer
     */
    public function save()
    {
        $this->events()->trigger(__CLASS__, 'beforeSave', NULL, array('object' => $this));
        $array_save = array(
            'name' => $this->getName(),
            'url_key' => $this->getUrlKey(),
            'updated_at' => new Expression('NOW()'),
            'status' => ($this->getStatus() === NULL ? self::STATUS_DISABLE : $this->getStatus()),
            'sort_order' => (int)$this->getSortOrder(),
            'user_id' => (int)$this->getUserId(),
            'document_type_id' => (int)$this->getDocumentTypeId() == 0 ? NULL : (int)$this->getDocumentTypeId(),
            'view_id' => (int)$this->getViewId() == 0 ? NULL : (int)$this->getViewId(),
            'layout_id' => (int)$this->getLayoutId() == 0 ? NULL : (int)$this->getLayoutId(),
            'parent_id' => (int)$this->getParentId() == 0 ? NULL : (int)$this->getParentId(),
        );

        if($this->getDriverName() == 'pdo_pgsql')
        {
            $array_save['show_in_nav'] = $this->showInNav() === TRUE ? 'TRUE' : 'FALSE';
        }
        else
        {
            $array_save['show_in_nav'] = $this->showInNav() === TRUE ? 1 : 0;
        }

        try
        {
            $document_id = $this->getId();
            if(empty($document_id))
            {
                $array_save['created_at'] = new Expression('NOW()');
                $this->insert($array_save);
                $this->setId($this->getLastInsertId());
            }
            else
            {
                $this->update($array_save, array('id' => $this->getId()));
            }

            $this->events()->trigger(__CLASS__, 'afterSave', NULL, array('object' => $this));

            return $this->getId();
        }
        catch(\Exception $e)
        {
            throw new \Gc\Exception($e->getMessage(), $e->getCode(), $e);
        }

        $this->events()->trigger(__CLASS__, 'afterSaveFailed', NULL, array('object' => $this));

        return FALSE;
    }

    /**
     * Delete document
     *
     * @return boolean
     */
    public function delete()
    {
        $this->events()->trigger(__CLASS__, 'beforeDelete', NULL, array('object' => $this));
        $document_id = $this->getId();
        if(!empty($document_id))
        {
            try
            {
                $properties_table = new TableGateway('property_value', $this->getAdapter());
                $properties_table->delete(array('document_id' => $this->getId()));
                parent::delete(array('id' => $document_id));
            }
            catch(\Exception $e)
            {
                throw new \Gc\Exception($e->getMessage(), $e->getCode(), $e);
            }

            $this->events()->trigger(__CLASS__, 'afterDelete', NULL, array('object' => $this));
            unset($this);

            return TRUE;
        }

        $this->events()->trigger(__CLASS__, 'afterDeleteFailed', NULL, array('object' => $this));

        return FALSE;
    }

    /**
     * Get document url
     *
     * @return string
     */
    public function getUrl()
    {
        $parent = $this->getParent();
        $path = '/' . $this->getUrlKey();
        if(!empty($parent))
        {
            $path = $parent->getUrl() . $path;
        }

        return $path;
    }

    /**
     * Get property
     *
     * @param string $property_name
     * @return FALSE | PropertyModel
     */
    public function getProperty($property_name)
    {
        if(!$this->hasData('id'))
        {
            return FALSE;
        }

        return PropertyModel::fromIdentifier($property_name, $this->getId());
    }

    /**
     * Retrieve children with his status is enable
     *
     * @return FALSE | PropertyModel
     */
    public function getAvailableChildren()
    {
        if($this->getData('available_children') === NULL)
        {
            $children = new Collection();
            $children->load($this->getId());
            $this->setData('available_children', $children->getAvailableChildren());
        }

        return $this->getData('available_children');
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
        return 'document_' . $this->getId();
    }

    /** (non-PHPdoc)
     * @see include \Gc\Component\IterableInterface#getEditUrl()
     */
    public function getEditUrl()
    {
        return Registry::get('Application')->getMvcEvent()->getRouter()->assemble(array('id' => $this->getId()), array('name' => 'documentEdit'));
    }
}
