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
    Gc\Property,
    Zend\Db\Sql\Select;
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
     * @param integer $tab_id Optional
     * @param integer $document_type_id Optional
     * @return \Gc\Tab\Model
     */
    public function load($tab_id = NULL, $document_type_id = NULL)
    {
        $this->setId((int)$tab_id);
        $this->setDocumentTypeId((int)$document_type_id);

        $select = $this->select(function(Select $select)
        {
            if($this->getDocumentTypeId() !== NULL)
            {
                $select->where->equalTo('document_type_id', $this->getDocumentTypeId());
            }

            if($this->getId() !== NULL)
            {
                $select->where->equalTo('id', $this->getId());
            }
        });

        $row = $this->fetchRow($select);
        if(empty($row['id']))
        {
            return FALSE;
        }

        $this->setName($row->name);
        $this->setDescription($row->description);
        $this->setDocumentTypeId($row->document_type_id);
        $this->setSortOrder($row->sort_order);

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
            'sort_order' => $this->getSortOrder(),
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
                $this->update($array_save, array('id' => (int)$this->getId()));
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
     * Delete tab
     * @return boolean
     */
    public function delete()
    {
        $this->events()->trigger(__CLASS__, 'beforeDelete', NULL, array('object' => $this));
        $tab_id = $this->getId();
        if(!empty($tab_id))
        {
            try
            {
                $properties_collection = new Property\Collection();
                $properties_collection->load(NULL, $tab_id);
                $properties_collection->delete();
                parent::delete(array('id' => $tab_id));
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
     * @param integer $tab_id
     * @return \Gc\Tab\Model
     */
    static function fromId($tab_id)
    {
        $tab_table = new Model();
        $row = $tab_table->select(array('id' => (int)$tab_id));
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
