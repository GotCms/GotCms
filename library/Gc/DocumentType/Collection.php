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
 * @subpackage DocumentType
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Gc\DocumentType;

use Gc\Db\AbstractTable;
use Zend\Db\Sql\Select;

/**
 * Collection of Document Type Model
 *
 * @category   Gc
 * @package    Library
 * @subpackage DocumentType
 */
class Collection extends AbstractTable
{
    /**
     * Table name
     *
     * @var string
     */
    protected $name = 'document_type';

    /**
     * Load document type collection
     *
     * @param integer $parent_id Parent id
     *
     * @return void
     */
    public function init($parent_id = null)
    {
        $this->setParentId($parent_id);
        $this->setDocumentTypes();
    }

    /**
     * Initialize document types
     *
     * @return \Gc\Document\Collection
     */
    protected function setDocumentTypes()
    {
        $rows = $this->fetchAll(
            $this->select(
                function (Select $select) {
                    $parent_id = $this->getParentId();
                    if (!empty($parent_id)) {
                        $select->join(
                            array('dtd' => 'document_type_dependency'),
                            'dtd.children_id = document_type.id',
                            array()
                        );
                        $select->where->equalTo('dtd.parent_id', $parent_id);
                    }

                    $select->order('name ASC');
                }
            )
        );

        $document_types = array();
        foreach ($rows as $row) {
            $document_types[] = Model::fromArray((array) $row);
        }

        $this->setData('document_types', $document_types);
    }

    /**
     * Return array for input select
     *
     * @return array
     */
    public function getSelect()
    {
        $select         = array();
        $document_types = $this->getDocumentTypes();

        foreach ($document_types as $document_type) {
            $select[$document_type->getId()] = $document_type->getName();
        }

        return $select;
    }
}
