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
use Zend\Db\TableGateway\TableGateway;

/**
 * Property Model
 *
 * @category   Gc
 * @package    Library
 * @subpackage Property
 */
class Model extends AbstractTable
{
    /**
     * Accessor for \Gc\Property\Value\Model
     *
     * @var \Gc\Property\Value\Model
     */
    protected $value;

    /**
     * Table name
     *
     * @var string
     */
    protected $name = 'property';

    /**
     * Get if property is required or not
     *
     * @param Boolean $value Value
     *
     * @return mixed
     */
    public function isRequired($value = null)
    {
        if ($value === null) {
            return $this->getData('required');
        }

        if ($value === true) {
            $this->setData('required', true);
        } else {
            $this->setData('required', false);
        }

        return $this;
    }

    /**
     * Set property value
     *
     * @param mixed $value Value
     *
     * @return \Gc\Property\Model
     */
    public function setValue($value)
    {
        if (empty($this->value)) {
            $this->loadValue();
        }

        $this->value->setValue($value);

        return $this;
    }

    /**
     * Load property value
     *
     * @return \Gc\Property\Model
     */
    public function loadValue()
    {
        $propertyvalue = new Value\Model();
        $propertyvalue->load(null, $this->getDocumentId(), $this->getId());

        $this->value = $propertyvalue;

        return $this;
    }

    /**
     * Return property value
     *
     * @return mixed
     */
    public function getValue()
    {
        if (empty($this->value)) {
            $this->loadValue();
        }

        return $this->value->getValue();
    }

    /**
     * Return property value model
     *
     * @return Value\Model
     */
    public function getValueModel()
    {
        if (empty($this->value)) {
            $this->loadValue();
        }

        return $this->value;
    }

    /**
     * Save property value
     *
     * @return boolean
     */
    public function saveValue()
    {
        $value = $this->getValue();
        $this->value->save();
        if ((is_null($value) or $value === '') and $this->isRequired()) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Save property
     *
     * @return integer
     */
    public function save()
    {
        $this->events()->trigger(__CLASS__, 'before.save', $this);
        $arraySave = array(
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'identifier' => $this->getIdentifier(),
            'sort_order' => $this->getSortOrder(),
            'tab_id' => $this->getTabId(),
            'datatype_id' => $this->getDatatypeId(),
        );

        if ($this->getDriverName() == 'pdo_pgsql') {
            $arraySave['required'] = $this->isRequired() === true ? 'true' : 'false';
        } else {
            $arraySave['required'] = $this->isRequired() === true ? 1 : 0;
        }

        try {
            $id = $this->getId();
            if (empty($id)) {
                $this->insert($arraySave);
                $this->setId($this->getLastInsertId());
            } else {
                $this->update($arraySave, array('id' => (int) $this->getId()));
            }

            $this->events()->trigger(__CLASS__, 'after.save', $this);

            return $this->getId();
        } catch (\Exception $e) {
            $this->events()->trigger(__CLASS__, 'after.save.failed', $this);
            throw new \Gc\Exception($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Delete property
     *
     * @return boolean
     */
    public function delete()
    {
        $this->events()->trigger(__CLASS__, 'before.delete', $this);
        $id = $this->getId();
        if (!empty($id)) {
            try {
                parent::delete(array('id' => (int) $id));
                $table  = new TableGateway('property_value', $this->getAdapter());
                $result = $table->delete(array('property_id' => (int) $id));
            } catch (\Exception $e) {
                throw new \Gc\Exception($e->getMessage());
            }

            $this->events()->trigger(__CLASS__, 'after.delete', $this);

            return true;
        }

        $this->events()->trigger(__CLASS__, 'after.delete.failed', $this);

        return false;
    }

    /**
     * Initiliaze model from array
     *
     * @param array $array Data
     *
     * @return Model
     */
    public static function fromArray(array $array)
    {
        $propertyTable = new Model();
        $propertyTable->setData($array);
        $propertyTable->setOrigData();

        return $propertyTable;
    }

    /**
     * Initiliaze model from id
     *
     * @param integer $propertyId Property id
     *
     * @return \Gc\Property\Model
     */
    public static function fromId($propertyId)
    {
        $propertyTable = new Model();
        $row           = $propertyTable->fetchRow($propertyTable->select(array('id' => (int) $propertyId)));
        $propertyTable->events()->trigger(__CLASS__, 'before.load', $propertyTable);
        if (!empty($row)) {
            $propertyTable->setData((array) $row);
            $propertyTable->setOrigData();
            $propertyTable->events()->trigger(__CLASS__, 'after.load', $propertyTable);
            return $propertyTable;
        } else {
            $propertyTable->events()->trigger(__CLASS__, 'after.load.failed', $propertyTable);
            return false;
        }
    }

    /**
     * Initiliaze model from identifier
     *
     * @param string  $identifier Identifier
     * @param integer $documentId Document id
     *
     * @return \Gc\Property\Model
     */
    public static function fromIdentifier($identifier, $documentId)
    {
        $propertyTable = new Model();
        $row           = $propertyTable->fetchRow(
            $propertyTable->select(
                function (Select $select) use ($documentId, $identifier) {
                    $select->join(array('t' => 'tab'), 't.id = property.tab_id', array());
                    $select->join(array('dt' => 'document_type'), 'dt.id = t.document_type_id', array());
                    $select->join(array('d' => 'document'), 'd.document_type_id = dt.id', array());
                    $select->where->equalTo('d.id', $documentId);
                    $select->where->equalTo('identifier', $identifier);
                }
            )
        );
        $propertyTable->events()->trigger(__CLASS__, 'before.load', $propertyTable);

        if (!empty($row)) {
            $propertyTable->setData((array) $row);
            $propertyTable->setDocumentId($documentId);
            $propertyTable->setOrigData();
            $propertyTable->events()->trigger(__CLASS__, 'after.load', $propertyTable);
            return $propertyTable;
        } else {
            $propertyTable->events()->trigger(__CLASS__, 'after.load.failed', $propertyTable);
            return false;
        }
    }
}
