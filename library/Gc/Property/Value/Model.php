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
     * @param integer $value_id    Optional value id
     * @param integer $document_id Optional document id
     * @param integer $property_id Optional property id
     *
     * @return \Gc\Property\Model\Value
     */
    public function load($value_id = null, $document_id = null, $property_id = null)
    {
        $this->setId($value_id);
        $this->setDocumentId($document_id);
        $this->setPropertyId($property_id);
        if (!empty($document_id) and !empty($property_id)) {
            $property_value = $this->fetchRow(
                $this->select(
                    array('property_id' => $property_id, 'document_id' => $document_id)
                )
            );

            if (!empty($property_value['id'])) {
                $this->setId($property_value['id']);
                if ($this->getDriverName() == 'pdo_pgsql') {
                    $this->setValue(stream_get_contents($property_value['value']));
                } else {
                    $this->setValue($property_value['value']);
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
        $property_value_table = new Model();
        $property_value_table->setData($array);
        $property_value_table->setOrigData();

        return $property_value_table;
    }

    /**
     * Initialize from id
     *
     * @param integer $property_value_id Property value id
     *
     * @return \Gc\Property\Value\Model|boolean
     */
    public static function fromId($property_value_id)
    {
        $property_value_table = new Model();
        $select               = $property_value_table->select(array('id' => (int) $property_value_id));
        $current              = $property_value_table->fetchRow($select);
        if (!empty($current)) {
            $property_value_table->setData((array) $current);
            $property_value_table->setOrigData();
            return $property_value_table;
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
        $this->events()->trigger(__CLASS__, 'beforeSave', null, array('object' => $this));
        $array_save = array(
            'value' => ($this->getDriverName() == 'pdo_pgsql') ? pg_escape_bytea($this->getValue()) : $this->getValue(),
            'document_id' => $this->getDocumentId(),
            'property_id' => $this->getPropertyId(),
        );

        try {
            $id = $this->getId();
            if (empty($id)) {
                $this->insert($array_save);
                $this->setId($this->getLastInsertId());
            } else {
                $this->update($array_save, array('id' => $this->getId()));
            }

            $this->events()->trigger(__CLASS__, 'afterSave', null, array('object' => $this));

            return $this->getId();
        } catch (\Exception $e) {
            throw new \Gc\Exception($e->getMessage(), $e->getCode(), $e);
        }

        $this->events()->trigger(__CLASS__, 'afterSaveFailed', null, array('object' => $this));

        return false;
    }
}
