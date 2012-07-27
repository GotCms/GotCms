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
    Zend\Db\Sql\Select;

class Collection extends AbstractTable implements IterableInterface
{
    /**
     * @var string
     */
    protected $_name = 'document';

    /**
     * Load document collection
     * @param integer @parent_id
     * @return void
     */
    public function load($parent_id = NULL)
    {
        if($parent_id !== NULL)
        {
            $this->setData('parent_id', $parent_id);
            $this->setDocuments();
        }
    }

    /**
     * Initialize documents
     * @return \Gc\Document\Collection
     */
    private function setDocuments()
    {
        $parent_id = $this->getParentId();

        if(!empty($parent_id))
        {
            $rows = $this->select(function(Select $select)
            {
                $select->where->equalTo('parent_id', $this->getParentId());
                $select->order('name ASC');
            });
        }
        else
        {
            $rows = $this->select(function(Select $select)
            {
                $select->where->isNull('parent_id');
                $select->order('name ASC');
            });
        }

        $documents = array();
        foreach($rows as $row)
        {
            $documents[] = Model::fromArray((array)$row);
        }

        $this->setData('documents', $documents);

        return $this;
    }

    /**
     * Return array for input select
     * @return array
     */
    public function getSelect()
    {
        $select = array();
        $documents = $this->getDocuments();
        if(!is_array($documents))
        {
            return $select;
        }

        foreach($documents as $document)
        {
            $select[$document->getId()] = $document->getName();
        }

        return $select;
    }

    /* (non-PHPdoc)
     * @see include \Gc\Component\IterableInterface#getParent()
     */
    public function getParent()
    {
        return FALSE;
    }

    /* (non-PHPdoc)
     * @see include \Gc\Component\IterableInterface#getChildren()
     */
    public function getChildren()
    {
        return $this->getDocuments();
    }

    /* (non-PHPdoc)
     * @see include \Gc\Component\IterableInterface#getId()
     */
    public function getId()
    {
        return FALSE;
    }

    /* (non-PHPdoc)
     * @see include \Gc\Component\IterableInterface#getIcon()
     */
    public function getIcon()
    {
        return 'folder';
    }

    /* (non-PHPdoc)
     * @see include \Gc\Component\IterableInterface#getIterableId()
     */
    public function getIterableId()
    {
        return 'documents';
    }

    /* (non-PHPdoc)
     * @see include \Gc\Component\IterableInterface#getName()
     */
    public function getName()
    {
        return 'Website';
    }

    /* (non-PHPdoc)
     * @see include \Gc\Component\IterableInterface#getUrl()
     */
    public function getUrl()
    {
        return NULL;
    }

}
