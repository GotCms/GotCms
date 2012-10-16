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
 * @subpackage  Tab
 * @author      Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license     GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link        http://www.got-cms.com
 */

namespace Gc\Tab;

use Gc\Db\AbstractTable,
    Gc\Property;
/**
 * Tab Model
 */
class Model extends AbstractTable
{
    /**
     * Table name
     * @var string
     */
    protected $_name = 'tab';

    /**
     * Initiliaze Tab
     * @param optional integer $tab_id
     * @param optional integer $document_type_id
     * @return \Gc\Model\Tab
     */
    public function load($tab_id = NULL, $document_type_id = NULL)
    {
        $this->setId($tab_id);
        $this->setDocumentTypeId($document_type_id);

        $select = $this->select();
        if($this->getDocumentTypeId() !== NULL)
        {
            $select->where('document_type_id = ?',$this->getDocumentTypeId());
        }

        if($this->getId() !== NULL)
        {
            $select->where('id = ?', $this->getId());
        }

        $row = $this->fetchRow($select);
        if(empty($row))
        {
            return FALSE;
        }

        $this->setName($row->name);
        $this->setDescription($row->description);
        $this->setDocumentTypeId($row->document_type_id);
        $this->setSortOrder($row->order);

        return $this;
    }

    /**
     * Save tab
     * @return integer
     */
    public function save()
    {
        $this->events()->trigger(__CLASS__, 'beforeSave', NULL, array('object' => $this));
        $array_save = array(
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'sort_order' => $this->getOrder(),
            'document_type_id' => $this->getDocumentTypeId(),
        );

        try
        {
            $id = $this->getId();
            if(empty($id))
            {
                $this->insert($array_save);
                $this->setId($this->getLastInsertId());
            }
            else
            {
                $this->update($array_save, sprintf('id = %s', (int)$this->getId()));
            }

            $this->events()->trigger(__CLASS__, 'afterSave', NULL, array('object' => $this));

            return $this->getId();
        }
        catch (Exception $e)
        {
            /**
            * TODO(Make \Gc\Error)
            */
            \Gc\Error::set(get_class($this),$e);
        }

        $this->events()->trigger(__CLASS__, 'afterSaveFailed', NULL, array('object' => $this));

        return FALSE;
    }

    /**
     * Delete tab
     * @return boolean
     */
    public function delete()
    {
        $tab_id = $this->getId();
        if(!empty($tab_id))
        {
            try
            {
                $properties_collection = new Property\Collection();
                $properties_collection->load(NULL, $tab_id);
                $properties_collection->delete();
                parent::delete('id = '.$tab_id);
            }
            catch(Exception $e)
            {
                throw new \Gc\Exception($e->getMessage());
            }

            return TRUE;
        }

        return FALSE;
    }

    /**
     * Initiliaze from array
     * @param array $array
     * @return \Gc\Tab\Model
     */
    static function fromArray(array $array)
    {
        $tab_table = new Model();
        $tab_table->setData($array);

        return $tab_table;
    }

    /**
     * Initialize from id
     * @param integer $id
     * @return \Gc\Tab\Model
     */
    static function fromId($id)
    {
        $tab_table = new Model();
        $row = $tab_table->select(array('id' => $id));
        $current = $row->current();
        if(!empty($current))
        {
            return $tab_table->setData((array)$current);
        }
        else
        {
            return FALSE;
        }
    }

    /**
     * Get Document type
     * @return \Gc\DocumentType\Model
     */
    public function getDocumentType()
    {
        if($this->getData('document_type') === NULL)
        {
            $this->setData('document_type', \Gc\DocumentType\Model::fromId($this->getDocumentTypeId()));
        }

        return $this->getData('document_type');
    }

    /**
     * Return properties
     * @return \Gc\Property\Collection
     */
    public function getProperties()
    {
        if($this->getData('properties') === NULL )
        {
            $properties_collection = new Property\Collection();
            $properties_collection->load($this->getDocumentTypeId(), $this->getId());

            $this->setData('properties', $properties_collection->getProperties());
        }

        return $this->getData('properties');
    }
}
