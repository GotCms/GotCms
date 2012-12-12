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
    protected $_name = 'property_value';

    /**
     * Load property value
     *
     * @param integer $value_id Optional
     * @param integer $document_id Optional
     * @param integer $property_id Optional
     * @return \Gc\Property\Model\Value
     */
    public function load($value_id = NULL, $document_id = NULL, $property_id = NULL)
    {
        $this->setId($value_id);
        $this->setDocumentId($document_id);
        $this->setPropertyId($property_id);
        if(!empty($document_id) and !empty($property_id))
        {
            $property_value = $this->select(array('property_id' => $property_id, 'document_id' => $document_id))->current();

            if(!empty($property_value->id))
            {
                $this->setId($property_value->id);
                if($this->getDriverName() == 'pdo_pgsql')
                {
                    $this->setValue(stream_get_contents($property_value->value));
                }
                else
                {
                    $this->setValue($property_value->value);
                }
            }
        }

        return $this;
    }

    /**
     * Initialize from array
     *
     * @param array $array
     * @return \Gc\Property\Value\Model
     */
    static function fromArray(array $array)
    {
        $property_value_table = new Model();
        $property_value_table->setData($array);

        return $property_value_table;
    }

    /**
     * Initialize from id
     *
     * @param integer $property_value_id
     * @return \Gc\Property\Value\Model|boolean
     */
    static function fromId($property_value_id)
    {
        $property_value_table = new Model();
        $select = $property_value_table->select(array('id' => (int)$property_value_id));
        $row = $property_value_table->fetchRow($select);
        if(!empty($row))
        {
            return $property_value_table->setData((array)$row);
        }
        else
        {
            return FALSE;
        }
    }

    /**
     * Save property value
     *
     * @return integer
     */
    public function save()
    {
        $this->events()->trigger(__CLASS__, 'beforeSave', NULL, array('object' => $this));
        $array_save = array(
            'value' => ($this->getDriverName() == 'pdo_pgsql') ? pg_escape_bytea($this->getValue()) : $this->getValue(),
            'document_id' => $this->getDocumentId(),
            'property_id' => $this->getPropertyId(),
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
                $this->update($array_save, array('id' => $this->getId()));
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
}
