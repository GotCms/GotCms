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
 * @subpackage Tab
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Gc\Tab;

use Gc\Db\AbstractTable;
use Gc\Property;
use Zend\Db\Sql\Select;

/**
 * Tab Model
 *
 * @category   Gc
 * @package    Library
 * @subpackage Tab
 */
class Model extends AbstractTable
{
    /**
     * Table name
     *
     * @var string
     */
    protected $name = 'tab';

    /**
     * Initiliaze Tab
     *
     * @param integer $tabId          Optional tab id
     * @param integer $documentTypeId Optional document type id
     *
     * @return \Gc\Tab\Model
     */
    public function load($tabId = null, $documentTypeId = null)
    {
        $this->setId((int) $tabId);
        $this->setDocumentTypeId((int) $documentTypeId);
        $tabId = $this->getId();

        $select = $this->select(
            function (Select $select) use ($documentTypeId, $tabId) {
                if (!empty($documentTypeId)) {
                    $select->where->equalTo('document_type_id', $documentTypeId);
                }

                if (!empty($tabId)) {
                    $select->where->equalTo('id', $tabId);
                }
            }
        );

        $row = $this->fetchRow($select);
        if (empty($row['id'])) {
            return false;
        }

        $this->setName($row['name']);
        $this->setDescription($row['description']);
        $this->setDocumentTypeId($row['document_type_id']);
        $this->setSortOrder($row['sort_order']);

        return $this;
    }

    /**
     * Save tab
     *
     * @return integer
     */
    public function save()
    {
        $this->events()->trigger(__CLASS__, 'beforeSave', null, array('object' => $this));
        $arraySave = array(
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'sort_order' => $this->getSortOrder(),
            'document_type_id' => $this->getDocumentTypeId(),
        );

        try {
            $id = $this->getId();
            if (empty($id)) {
                $this->insert($arraySave);
                $this->setId($this->getLastInsertId());
            } else {
                $this->update($arraySave, array('id' => (int) $this->getId()));
            }

            $this->events()->trigger(__CLASS__, 'afterSave', null, array('object' => $this));

            return $this->getId();
        } catch (\Exception $e) {
            throw new \Gc\Exception($e->getMessage(), $e->getCode(), $e);
        }

        $this->events()->trigger(__CLASS__, 'afterSaveFailed', null, array('object' => $this));

        return false;
    }

    /**
     * Delete tab
     *
     * @return boolean
     */
    public function delete()
    {
        $this->events()->trigger(__CLASS__, 'beforeDelete', null, array('object' => $this));
        $tabId = $this->getId();
        if (!empty($tabId)) {
            try {
                $propertiesCollection = new Property\Collection();
                $propertiesCollection->load(null, $tabId);
                $propertiesCollection->delete();
                parent::delete(array('id' => $tabId));
            } catch (\Exception $e) {
                throw new \Gc\Exception($e->getMessage(), $e->getCode(), $e);
            }

            $this->events()->trigger(__CLASS__, 'afterDelete', null, array('object' => $this));
            unset($this);

            return true;
        }

        $this->events()->trigger(__CLASS__, 'afterDeleteFailed', null, array('object' => $this));

        return false;
    }

    /**
     * Initiliaze from array
     *
     * @param array $array Data
     *
     * @return void
     */
    public static function fromArray(array $array)
    {
        $tabTable = new Model();
        $tabTable->setData($array);
        $tabTable->setOrigData();

        return $tabTable;
    }

    /**
     * Initialize from id
     *
     * @param integer $tabId Tab id
     *
     * @return \Gc\Tab\Model
     */
    public static function fromId($tabId)
    {
        $tabTable = new Model();
        $row      = $tabTable->fetchRow($tabTable->select(array('id' => (int) $tabId)));
        if (!empty($row)) {
            $tabTable->setData((array) $row);
            $tabTable->setOrigData();
            return $tabTable;
        } else {
            return false;
        }
    }

    /**
     * Get Document type
     *
     * @return \Gc\DocumentType\Model
     */
    public function getDocumentType()
    {
        if ($this->getData('document_type') === null) {
            $this->setData('document_type', \Gc\DocumentType\Model::fromId($this->getDocumentTypeId()));
        }

        return $this->getData('document_type');
    }

    /**
     * Return properties
     *
     * @return \Gc\Property\Collection
     */
    public function getProperties()
    {
        if ($this->getData('properties') === null) {
            $propertiesCollection = new Property\Collection();
            $propertiesCollection->load($this->getDocumentTypeId(), $this->getId());

            $this->setData('properties', $propertiesCollection->getProperties());
        }

        return $this->getData('properties');
    }
}
