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

use Gc\Db\AbstractTable;
use Gc\Component\IterableInterface;
use Gc\DocumentType;
use Gc\Media\Icon;
use Gc\Property\Model as PropertyModel;
use Gc\Registry;
use Gc\View;
use Gc\Layout;
use Zend\Db\TableGateway\TableGateway;

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
    protected $name = 'document';

    /**
     * @const STATUS_DISABLE
     */
    const STATUS_DISABLE = 0;

    /**
     * @const STATUS_ENABLE
     */
    const STATUS_ENABLE = 1;

    /**
     * Initiliaze document
     *
     * @param integer $documentId Document id
     *
     * @return void
     */
    public function init($documentId = null)
    {
        if (!empty($documentId)) {
            $this->setId($documentId);
        }

        $this->getChildren();
    }

    /**
     * get View Model
     *
     * @return \Gc\View\Model
     */
    public function getLayout()
    {
        if ($this->getData('layout') == null) {
            $view = Layout\Model::fromId($this->getLayoutId());
            if ($view !== null) {
                $this->setData('layout', $view);
            }
        }

        return $this->getData('layout');
    }

    /**
     * get View Model
     *
     * @return \Gc\View\Model
     */
    public function getView()
    {
        if ($this->getData('view') == null) {
            $view = View\Model::fromId($this->getViewId());
            if ($view !== null) {
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
        if ($this->getData('document_type') === null) {
            $this->setData('document_type', DocumentType\Model::fromId($this->getDocumentTypeId()));
        }

        return $this->getData('document_type');
    }

    /**
     * Define if document is show in navigation
     *
     * @param boolean $isShow Optional
     *
     * @return boolean
     */
    public function showInNav($isShow = null)
    {
        if ($isShow !== null) {
            $this->setData('show_in_nav', $isShow);
        }

        return (bool) $this->getData('show_in_nav');
    }

    /**
     * Define if document can be cached
     *
     * @param boolean $canBeCached Optional
     *
     * @return boolean
     */
    public function canBeCached($canBeCached = null)
    {
        if ($canBeCached !== null) {
            $this->setData('can_be_cached', $canBeCached);
        }

        return (bool) $this->getData('can_be_cached');
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
     * @param array $array Data
     *
     * @return \Gc\Document\Model
     */
    public static function fromArray(array $array)
    {
        $documentTable = new Model();
        $documentTable->setData($array);
        $documentTable->setOrigData();

        return $documentTable;
    }

    /**
     * Initiliaze document from id
     *
     * @param integer $documentId Document id
     *
     * @return \Gc\Document\Model
     */
    public static function fromId($documentId)
    {
        $documentTable = new Model();
        $row           = $documentTable->fetchRow($documentTable->select(array('id' => (int) $documentId)));
        $documentTable->events()->trigger(__CLASS__, 'before.load', $documentTable);
        if (!empty($row)) {
            $documentTable->setData((array) $row);
            $documentTable->setOrigData();
            $documentTable->events()->trigger(__CLASS__, 'after.load', $documentTable);
            return $documentTable;
        } else {
            $documentTable->events()->trigger(__CLASS__, 'after.load.failed', $documentTable);
            return false;
        }
    }

    /**
     * Initiliaze from url and parent
     *
     * @param string        $urlKey   Url key
     * @param integer|null  $parentId Parent id
     *
     * @return \Gc\Document\Model
     */
    public static function fromUrlKey($urlKey, $parentId = false)
    {
        $documentTable = new Model();
        $sqlData       = array('url_key' => $urlKey);
        $documentTable->events()->trigger(__CLASS__, 'before.load', $documentTable);
        if ($parentId !== false) {
            $sqlData['parent_id'] = $parentId;
        }

        $row = $documentTable->fetchRow($documentTable->select($sqlData));
        if (!empty($row)) {
            $documentTable->setData((array) $row);
            $documentTable->setOrigData();
            $documentTable->events()->trigger(__CLASS__, 'after.load', $documentTable);
            return $documentTable;
        } else {
            $documentTable->events()->trigger(__CLASS__, 'after.load.failed', $documentTable);
            return false;
        }
    }

    /**
     * Save Model
     *
     * @return integer
     */
    public function save()
    {
        $this->events()->trigger(__CLASS__, 'before.save', $this);
        $this->setUpdatedAt(date('Y-m-d H:i:s'));
        $arraySave = array(
            'name' => $this->getName(),
            'url_key' => $this->getUrlKey(),
            'updated_at' => $this->getUpdatedAt(),
            'status' => ($this->getStatus() === null ? self::STATUS_DISABLE : $this->getStatus()),
            'sort_order' => (int) $this->getSortOrder(),
            'user_id' => (int) $this->getUserId(),
            'document_type_id' => (int) $this->getDocumentTypeId() == 0 ? null : (int) $this->getDocumentTypeId(),
            'view_id' => (int) $this->getViewId() == 0 ? null : (int) $this->getViewId(),
            'layout_id' => (int) $this->getLayoutId() == 0 ? null : (int) $this->getLayoutId(),
            'parent_id' => (int) $this->getParentId() == 0 ? null : (int) $this->getParentId(),
            'locale' => $this->getLocale()
        );

        if ($this->getDriverName() == 'pdo_pgsql') {
            $arraySave['show_in_nav']   = $this->showInNav() === true ? 'true' : 'false';
            $arraySave['can_be_cached'] = $this->canBeCached() === true ? 'true' : 'false';
        } else {
            $arraySave['show_in_nav']   = $this->showInNav() === true ? 1 : 0;
            $arraySave['can_be_cached'] = $this->canBeCached() === true ? 1 : 0;
        }

        try {
            $documentId = $this->getId();
            if (empty($documentId)) {
                $this->setCreatedAt($this->getUpdatedAt());
                $arraySave['created_at'] = $this->getCreatedAt();
                $this->insert($arraySave);
                $this->setId($this->getLastInsertId());
            } else {
                $this->update($arraySave, array('id' => $this->getId()));
            }

            $this->events()->trigger(__CLASS__, 'after.save', $this);

            return $this->getId();
        } catch (\Exception $e) {
            $this->events()->trigger(__CLASS__, 'after.save.failed', $this);
            throw new \Gc\Exception($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Delete document
     *
     * @return boolean
     */
    public function delete()
    {
        $this->events()->trigger(__CLASS__, 'before.delete', $this);
        $documentId = $this->getId();
        if (!empty($documentId)) {
            try {
                $propertiesTable = new TableGateway('property_value', $this->getAdapter());
                $propertiesTable->delete(array('document_id' => $this->getId()));
                parent::delete(array('id' => $documentId));
            } catch (\Exception $e) {
                throw new \Gc\Exception($e->getMessage(), $e->getCode(), $e);
            }

            $this->events()->trigger(__CLASS__, 'after.delete', $this);
            unset($this);

            return true;
        }

        $this->events()->trigger(__CLASS__, 'after.delete.failed', $this);

        return false;
    }

    /**
     * Get document url
     *
     * @param boolean $forceCanonical Force canonical url
     *
     * @return string
     */
    public function getUrl($forceCanonical = false)
    {
        $parent = $this->getParent();
        $path   = $this->getUrlKey();

        if (!empty($parent)) {
            $path = $parent->getUrl() . '/' . $path;
        }

        $url = '/' . ltrim($path, '/');
        if ($forceCanonical) {
            $serverUrl = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('ServerUrl');
            $url       = $serverUrl() . $url;
        }

        return $url;
    }

    /**
     * Get property
     *
     * @param string $propertyName Property name
     *
     * @return false | PropertyModel
     */
    public function getProperty($propertyName)
    {
        if (!$this->hasData('id')) {
            return false;
        }

        return PropertyModel::fromIdentifier($propertyName, $this->getId());
    }

    /**
     * Retrieve children with his status is enable
     *
     * @return false | PropertyModel
     */
    public function getAvailableChildren()
    {
        if ($this->getData('available_children') === null) {
            $children = new Collection();
            $children->load($this->getId());
            $this->setData('available_children', $children->getAvailableChildren());
        }

        return $this->getData('available_children');
    }

    /** (non-PHPdoc)
     *
     * @see include \Gc\Component\IterableInterface#getName()
     * @return string
     */
    public function getName()
    {
        return $this->getData('name');
    }

    /** (non-PHPdoc)
     *
     * @see include \Gc\Component\IterableInterface#getId()
     * @return integer
     */
    public function getId()
    {
        return $this->getData('id');
    }

    /** (non-PHPdoc)
     *
     * @see include \Gc\Component\IterableInterface#getParent()
     * @return Model
     */
    public function getParent()
    {
        $parentId = $this->getData('parent_id');

        return Model::fromId($parentId);
    }

    /** (non-PHPdoc)
     *
     * @see include \Gc\Component\IterableInterface#getChildren()
     * @return array
     */
    public function getChildren()
    {
        if ($this->getData('children') === null) {
            $children = new Collection();
            $children->load($this->getId());
            $this->setData('children', $children->getChildren());
        }

        return $this->getData('children');
    }

    /** (non-PHPdoc)
     *
     * @see include \Gc\Component\IterableInterface#getIcon()
     * @return mixed
     */
    public function getIcon()
    {
        if ($this->getData('icon') === null) {
            $icon = Icon\Model::fromId($this->getDocumentType()->getIconId());
            if (empty($icon)) {
                return false;
            }

            $this->setData('icon', $icon->getUrl());
        }

        return $this->getData('icon');
    }

    /** (non-PHPdoc)
     *
     * @see include \Gc\Component\IterableInterface#getIterableId()
     * @return string
     */
    public function getIterableId()
    {
        return 'document_' . $this->getId();
    }

    /** (non-PHPdoc)
     *
     * @see include \Gc\Component\IterableInterface#getEditUrl()
     * @return mixed
     */
    public function getEditUrl()
    {
        return Registry::get('Application')
            ->getMvcEvent()
            ->getRouter()
            ->assemble(
                array('id' => $this->getId()),
                array('name' => 'content/document/edit')
            );
    }
}
