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
use Zend\Db\Sql\Select;

/**
 * Collection of Document Model
 *
 * @category   Gc
 * @package    Library
 * @subpackage Document
 */
class Collection extends AbstractTable implements IterableInterface
{
    /**
     * Table name
     *
     * @var string
     */
    protected $name = 'document';

    /**
     * Load document collection
     *
     * @param integer $parentId Parent id
     *
     * @return \Gc\Document\Collection
     */
    public function load($parentId = null)
    {
        if ($parentId !== null) {
            $this->setData('parent_id', $parentId);
            $this->getAll(true);
        }

        return $this;
    }

    /**
     * Get all documents
     *
     * @param boolean $forceReload Force reload documents
     *
     * @return \Gc\Document\Collection
     */
    public function getAll($forceReload = false)
    {
        if ($forceReload) {
            $parentId = $this->getParentId();
            $rows     = $this->fetchAll(
                $this->select(
                    function (Select $select) use ($parentId) {
                        if (empty($parentId)) {
                            $select->where->isNull('parent_id');
                        } else {
                            $select->where->equalTo('parent_id', $parentId);
                        }

                        $select->order('sort_order ASC');
                        $select->order('created_at ASC');
                    }
                )
            );

            $documents = array();
            foreach ($rows as $row) {
                $documents[] = Model::fromArray((array) $row);
            }

            $this->setData('documents', $documents);
        }

        return $this->getData('documents');
    }

    /**
     * Return available Documents
     *
     * @return array
     */
    public function getAvailableDocuments()
    {
        $rows = $this->fetchAll(
            $this->select(
                function (Select $select) {
                    $select->where->equalTo('status', Model::STATUS_ENABLE);
                    $select->order('sort_order ASC');
                    $select->order('created_at ASC');
                }
            )
        );

        return $rows;
    }

    /**
     * Return available children
     *
     * @return array
     */
    public function getAvailableChildren()
    {
        $children = $this->getChildren();
        $array    = array();
        foreach ($children as $child) {
            if ($child->isPublished()) {
                $array[] = $child;
            }
        }

        return $array;
    }

    /**
     * Return array for input select
     *
     * @return array
     */
    public function getSelect()
    {
        $select    = array();
        $documents = $this->getAll();

        foreach ($documents as $document) {
            $select[$document->getId()] = $document->getName();
        }

        return $select;
    }

    /** (non-PHPdoc)
     *
     * @see include \Gc\Component\IterableInterface#getParent()
     * @return boolean
     */
    public function getParent()
    {
        return false;
    }

    /** (non-PHPdoc)
     *
     * @see include \Gc\Component\IterableInterface#getChildren()
     * @return array
     */
    public function getChildren()
    {
        return $this->getAll();
    }

    /** (non-PHPdoc)
     *
     * @see include \Gc\Component\IterableInterface#getId()
     * @return boolean
     */
    public function getId()
    {
        return false;
    }

    /** (non-PHPdoc)
     *
     * @see include \Gc\Component\IterableInterface#getIcon()
     * @return string
     */
    public function getIcon()
    {
        return 'folder';
    }

    /** (non-PHPdoc)
     *
     * @see include \Gc\Component\IterableInterface#getIterableId()
     * @return string
     */
    public function getIterableId()
    {
        return 'documents';
    }

    /** (non-PHPdoc)
     *
     * @see include \Gc\Component\IterableInterface#getName()
     * @return string
     */
    public function getName()
    {
        return 'Website';
    }

    /** (non-PHPdoc)
     *
     * @see include \Gc\Component\IterableInterface#getEditUrl()
     * @return mixed
     */
    public function getEditUrl()
    {
        return null;
    }
}
