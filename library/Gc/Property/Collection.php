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
 * @subpackage  Property
 * @author      Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license     GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link        http://www.got-cms.com
 */

namespace Gc\Property;

use Gc\Db\AbstractTable,
    Zend\Db\Sql\Select;
/**
 * Collection of Property Model
 */
class Collection extends AbstractTable
{
    /**
     * Table name
     * @var string
     */
    protected $_name = 'property';

    /**
     * Load property
     * @param integer $document_type_id Optional
     * @param integer $tab_id Optional
     * @param integer $document_id Optional
     * @return \Gc\Property\Collection
     */
    public function load($document_type_id = NULL, $tab_id = NULL, $document_id = null)
    {
        $this->setDocumentTypeId($document_type_id);
        $this->setTabId($tab_id);
        $this->setDocumentId($document_id);

        $this->getProperties(TRUE);

        return $this;
    }

    /**
     * Get properties
     * @param boolean $force_reload to initiliaze properties
     * @return array
     */
    public function getProperties($force_reload = FALSE)
    {
        if($this->getData('properties') == NULL or $force_reload)
        {
            $select = new Select();
            $select->from('tab')
            ->columns(array())
            ->join('property', 'tab.id = property.tab_id', '*', Select::JOIN_INNER);

            if($this->getDocumentId() !== NULL)
            {
                $select->join('document', 'document.document_type_id = tab.document_type_id', array(), Select::JOIN_INNER);
                $select->join('property_value', 'document.id = property_value.document_id AND property.id = property_value.property_id', array('value'), Select::JOIN_LEFT);
                $select->where(array('document.id' => $this->getDocumentId()));
            }

            if($this->getTabId() != NULL)
            {
                $select->where(array('tab.id' => $this->getTabId()));
            }

            if($this->getDocumentTypeId() != NULL)
            {
                $select->where(array('tab.document_type_id' => $this->getDocumentTypeId()));
            }

            $select->order('property.sort_order ASC');

            $rows = $this->fetchAll($select);

            $properties = array();
            foreach($rows as $row)
            {
                $property_model = Model::fromArray((array)$row);
                if($this->getDocumentId() !== NULL)
                {
                    $property_model->setDocumentId($this->getDocumentId());
                }

                $properties[] = $property_model;
            }

            $this->setData('properties', $properties);
        }

        return $this->getData('properties');
    }

    /**
     * Set properties
     * @param array $properties
     * @return \Gc\Property\Collection
     */
    public function setProperties(array $properties)
    {
        $array = array();
        foreach($properties as $property)
        {
            $array[] = Model::fromArray($property);
        }

        $this->setData('properties', $array);

        return $this;
    }

    /**
     * Save properties
     * @return boolean
     */
    public function save()
    {
        $properties = $this->getProperties();
        try
        {
            foreach($properties as $property)
            {
                $property->save();
            }

            return TRUE;
        }
        catch(\Exception $e)
        {
            throw new \Gc\Exception($e->getMessage());
        }

        return FALSE;
    }

    /**
     * Delete properties
     * @return boolean
     */
    public function delete()
    {
        $properties = $this->getProperties();
        try
        {
            foreach($properties as $property)
            {
                $property->delete();
            }

            return TRUE;
        }
        catch(\Exception $e)
        {
            throw new \Gc\Exception($e->getMessage());
        }

        return FALSE;
    }
}
