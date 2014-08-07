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
 * @subpackage Property
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Gc\Property;

use Gc\Db\AbstractTable;
use Zend\Db\Sql\Select;

/**
 * Collection of Property Model
 *
 * @category   Gc
 * @package    Library
 * @subpackage Property
 */
class Collection extends AbstractTable
{
    /**
     * List of \Gc\Property\Model
     *
     * @var array
     */
    protected $properties = null;

    /**
     * Table name
     *
     * @var string
     */
    protected $name = 'property';

    /**
     * Load property
     *
     * @param integer $documentTypeId Optional
     * @param integer $tabId          Optional
     * @param integer $documentId     Optional
     *
     * @return \Gc\Property\Collection
     */
    public function load($documentTypeId = null, $tabId = null, $documentId = null)
    {
        $this->setDocumentTypeId($documentTypeId);
        $this->setTabId($tabId);
        $this->setDocumentId($documentId);

        $this->getAll(true);

        return $this;
    }

    /**
     * Get properties
     *
     * @param boolean $forceReload to initiliaze properties
     *
     * @return array
     */
    public function getAll($forceReload = false)
    {
        if ($this->properties == null or $forceReload) {
            $select = new Select();
            $select->from('tab')
                ->columns(array())
                ->join('property', 'tab.id = property.tab_id', '*', Select::JOIN_INNER);

            if ($this->getDocumentId() !== null) {
                $select->join(
                    'document',
                    'document.document_type_id = tab.document_type_id',
                    array(),
                    Select::JOIN_INNER
                );
                $select->join(
                    'property_value',
                    'document.id = property_value.document_id AND property.id = property_value.property_id',
                    array('value'),
                    Select::JOIN_LEFT
                );
                $select->where(array('document.id' => $this->getDocumentId()));
            }

            if ($this->getTabId() != null) {
                $select->where(array('tab.id' => $this->getTabId()));
            }

            if ($this->getDocumentTypeId() != null) {
                $select->where(array('tab.document_type_id' => $this->getDocumentTypeId()));
            }

            $select->order('property.sort_order ASC');

            $rows = $this->fetchAll($select);

            $properties = array();
            foreach ($rows as $row) {
                $propertyModel = Model::fromArray((array) $row);
                if ($this->getDocumentId() !== null) {
                    $propertyModel->setDocumentId($this->getDocumentId());
                }

                $properties[] = $propertyModel;
            }

            $this->properties = $properties;
        }

        return $this->properties;
    }

    /**
     * Set properties
     *
     * @param array $properties All
     *
     * @return \Gc\Property\Collection
     */
    public function setProperties(array $properties)
    {
        $array = array();
        foreach ($properties as $property) {
            $array[] = Model::fromArray($property);
        }

        $this->properties = $array;

        return $this;
    }

    /**
     * Save properties
     *
     * @return boolean
     */
    public function save()
    {
        $properties = $this->getAll();
        try {
            foreach ($properties as $property) {
                $property->save();
            }

            return true;
        } catch (\Exception $e) {
            throw new \Gc\Exception($e->getMessage());
        }
    }

    /**
     * Delete properties
     *
     * @return boolean
     */
    public function delete()
    {
        $properties = $this->getAll();
        try {
            foreach ($properties as $property) {
                $property->delete();
            }

            return true;
        } catch (\Exception $e) {
            throw new \Gc\Exception($e->getMessage());
        }
    }
}
