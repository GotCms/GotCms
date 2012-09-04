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
 * @subpackage  DocumentType
 * @author      Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license     GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link        http://www.got-cms.com
 */

namespace Gc\DocumentType;

use Gc\Db\AbstractTable,
    Gc\Component\IterableInterface,
    Zend\Db\Sql\Select;
/**
 * Collection of Document Type Model
 */
class Collection extends AbstractTable
{
    /**
     * Table name
     * @var string
     */
    protected $_name = 'document_type';

    /**
     * Load document type collection
     * @param integer @parent_id
     * @return void
     */
    public function init($parent_id = NULL)
    {
        $this->setParentId($parent_id);
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
            $parent_id = $this->getParentId();
            if(!empty($parent_id))
            {
                $select->join(array('dtd' => 'document_type_dependency'), 'dtd.children_id = document_type.id', array());
                $select->where->equalTo('dtd.parent_id', $parent_id);
            }

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

    /**
     * Return array for input select
     * @param integer
     * @return array
     */
    public function getDependencies($parent_id)
    {

    }
}
