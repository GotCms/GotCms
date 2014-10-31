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
 * @subpackage Property\Value
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Gc\Property\Value;

use Gc\Db\AbstractTable;

/**
 * Property value Model
 *
 * @category   Gc
 * @package    Library
 * @subpackage Property\Value
 */
class Model extends AbstractTable
{
    /**
     * Table name
     *
     * @var string
     */
    protected $name = 'property_value';

    /**
     * Load property value
     *
     * @param integer $valueId    Optional value id
     * @param integer $documentId Optional document id
     * @param integer $propertyId Optional property id
     *
     * @return Model
     */
    public function load($valueId = null, $documentId = null, $propertyId = null)
    {
        $this->setId($valueId);
        $this->setDocumentId($documentId);
        $this->setPropertyId($propertyId);
        if (!empty($documentId) and !empty($propertyId)) {
            $propertyValue = $this->fetchRow(
                $this->select(
                    array('property_id' => $propertyId, 'document_id' => $documentId)
                )
            );

            if (!empty($propertyValue['id'])) {
                $this->setId($propertyValue['id']);
                if ($this->getDriverName() == 'pdo_pgsql') {
                    $this->setValue(stream_get_contents($propertyValue['value']));
                } else {
                    $this->setValue($propertyValue['value']);
                }
            }
        }

        return $this;
    }

    /**
     * Initialize from array
     *
     * @param array $array Data
     *
     * @return \Gc\Property\Value\Model
     */
    public static function fromArray(array $array)
    {
        $propertyValueTable = new Model();
        $propertyValueTable->setData($array);
        $propertyValueTable->setOrigData();

        return $propertyValueTable;
    }

    /**
     * Initialize from id
     *
     * @param integer $propertyValueId Property value id
     *
     * @return \Gc\Property\Value\Model|boolean
     */
    public static function fromId($propertyValueId)
    {
        $propertyValueTable = new Model();
        $select             = $propertyValueTable->select(array('id' => (int) $propertyValueId));
        $current            = $propertyValueTable->fetchRow($select);
        if (!empty($current)) {
            $propertyValueTable->setData((array) $current);
            $propertyValueTable->setOrigData();
            return $propertyValueTable;
        } else {
            return false;
        }
    }

    /**
     * Save property value
     *
     * @return integer
     */
    public function save()
    {
        $this->events()->trigger(__CLASS__, 'before.save', $this);
        $arraySave = array(
            'document_id' => $this->getDocumentId(),
            'property_id' => $this->getPropertyId(),
        );

        if ($this->getDriverName() == 'pdo_pgsql') {
            $arraySave['value'] = pg_escape_bytea($this->getValue());
        } else {
            $arraySave['value'] = $this->getValue();
        }

        try {
            $id = $this->getId();
            if (empty($id)) {
                $this->insert($arraySave);
                $this->setId($this->getLastInsertId());
            } else {
                $this->update($arraySave, array('id' => $this->getId()));
            }

            $this->events()->trigger(__CLASS__, 'after.save', $this);

            return $this->getId();
        } catch (\Exception $e) {
            $this->events()->trigger(__CLASS__, 'after.save.failed', $this);
            throw new \Gc\Exception($e->getMessage(), $e->getCode(), $e);
        }
    }
}
