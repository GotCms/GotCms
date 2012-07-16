<?php
/**
 * This source file is part of Got CMS.
 *
 * Got CMS is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Got CMS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License along
 * with Got CMS. If not, see <http://www.gnu.org/licenses/lgpl-3.0.html>.
 *
 * PHP Version >=5.3
 *
 * @category    Gc
 * @package     Library
 * @subpackage  DocumentType
 * @author      Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license     GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link        http://www.got-cms.com
 */

namespace Gc\DocumentType;

use Gc\Db\AbstractTable,
    Gc\Component\IterableInterface,
    Zend\Db\Sql\Select;

class Collection extends AbstractTable implements IterableInterface
{
    /**
     * @var string
     */
    protected $_name = 'document_type';

    /**
     * Load document type collection
     * @param integer @parent_id
     * @return void
     */
    public function init($sort = 'ASC')
    {
        $this->setDocumentTypes();
    }

    /**
     * Initialize document types
     * @return \Gc\Document\Collection
     */
    private function setDocumentTypes()
    {
        $rows = $this->select(function (Select $select)
        {
            $select->order('name ASC');
        });

        $documentTypes = array();
        foreach($rows as $row)
        {
            $documentTypes[] = Model::fromArray((array)$row);
        }

        $this->setData('document_types', $documentTypes);
    }

    /**
     * Return array for input select
     * @return array
     */
    public function getSelect()
    {
        $select = array();
        $document_types = $this->getDocumentTypes();
        if(!is_array($document_types))
        {
            return $select;
        }

        foreach($document_types as $document_type)
        {
            $select[$document_type->getId()] = $document_type->getName();
        }

        return $select;
    }

    /*
     * Gc\Component\IterableInterfaces methods
     */
    /* (non-PHPdoc)
     * @see include \Gc\Component\IterableInterface#getParent()
     */
    public function getParent()
    {
        return null;
    }
    /* (non-PHPdoc)
     * @see include \Gc\Component\IterableInterface#getChildren()
     */
    public function getChildren()
    {
        return $this->getDocumentTypes();
    }
    /* (non-PHPdoc)
     * @see include \Gc\Component\IterableInterface#getId()
     */
    public function getId()
    {
        return null;
    }
    /* TODO Finish icon in Gc\DocumentType\Collection
     */
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
        return 'documenttypes';
    }
    /* (non-PHPdoc)
     * @see include \Gc\Component\IterableInterface#getName()
     */
    public function getName()
    {
        return 'Document Types';
    }
    /* (non-PHPdoc)
     * @see include \Gc\Component\IterableInterface#getUrl()
     */
    public function getUrl()
    {
        return '';
    }
}
