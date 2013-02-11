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
 * @subpackage Datatype
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Gc\Datatype;

use Gc\Db\AbstractTable,
    Gc\Form\AbstractForm,
    Gc\Property\Model as PropertyModel,
    Datatypes,
    Zend\Form\Fieldset;
/**
 * Datatype Model
 * Simply class to edit one datatype
 *
 * @category   Gc
 * @package    Library
 * @subpackage Datatype
 */
class Model extends AbstractTable
{
    /**
     * Table name
     *
     * @var string
     */
    protected $_name = 'datatype';

    /**
     * Set prevalue value
     *
     * @param mixed $value
     * @return \Gc\Datatype\Model
     */
    public function setPrevalueValue($value)
    {
        if(is_string($value))
        {
            $value = unserialize($value);
        }

        $this->setData('prevalue_value', $value);

        return $this;
    }

    /**
     * Get Model from array
     *
     * @param array $array
     * @return \Gc\Datatype\Model
     */
    static function fromArray(array $array)
    {
        $datatype_table = new Model();
        $datatype_table->setData($array);
        $datatype_table->setOrigData();

        return $datatype_table;
    }

    /**
     * Get model from id
     *
     * @param integer $datatype_id
     * @return FALSE|\Gc\Datatype\Model
     */
    static function fromId($datatype_id)
    {
        $datatype_table = new Model();
        $row = $datatype_table->fetchRow($datatype_table->select(array('id' => (int)$datatype_id)));
        if(!empty($row))
        {
            $datatype_table->setData((array)$row);
            $datatype_table->setOrigData();
            return $datatype_table;
        }
        else
        {
            return FALSE;
        }
    }

    /**
     * Save Datatype model
     *
     * @return integer
     */
    public function save()
    {
        $this->events()->trigger(__CLASS__, 'beforeSave', NULL, array('object' => $this));
        $array_save = array(
            'name' => $this->getName(),
            'prevalue_value' => serialize($this->getPrevalueValue()),
            'model' => $this->getModel(),
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

    /**
     * Delete datatype model
     *
     * @return boolean
     */
    public function delete()
    {
        $this->events()->trigger(__CLASS__, 'beforeDelete', NULL, array('object' => $this));
        $id = $this->getId();
        if(!empty($id))
        {
            try
            {
                parent::delete(array('id' => $id));
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
     * Save prevalue editor
     *
     * @param AbstractDatatype $datatype
     * @return Model
     */
    static function savePrevalueEditor(AbstractDatatype $datatype)
    {
        $datatype->getPrevalueEditor()->save();
        return $datatype->getConfig();
    }

    /**
     * Save editor
     *
     * @param PropertyModel $property
     * @return mixed
     */
    static function saveEditor(PropertyModel $property)
    {
        $datatype = self::loadDatatype($property->getDatatypeId(), $property->getDocumentId());
        $datatype->getEditor($property)->save();
        if(!$property->saveValue())
        {
            return FALSE;
        }
        else
        {
            return TRUE;
        }
    }

    /**
     * Load prevalue editor
     *
     * @param AbstractDatatype $datatype
     * @return mixed
     */
    static function loadPrevalueEditor(AbstractDatatype $datatype)
    {
        $fieldset = new Fieldset('prevalue-editor');
        AbstractForm::addContent($fieldset, $datatype->getPrevalueEditor()->load());
        return $fieldset;
    }

    /**
     * Load editor
     *
     * @param PropertyModel $property
     * @return mixed
     */
    static function loadEditor(PropertyModel $property)
    {
        $datatype = self::loadDatatype($property->getDatatypeId(), $property->getDocumentId());

        return $datatype->getEditor($property)->load();
    }

    /**
     * Load Datatype
     *
     * @param integer $datatype_id
     * @param integer $document_id Optional
     * @return \Gc\Datatype\AbstractDatatype
     */
    static function loadDatatype($datatype_id, $document_id = NULL)
    {
        $datatype = Model::fromId($datatype_id);
        $class = 'Datatypes\\' . $datatype->getModel() . '\Datatype';

        $object = new $class();
        $object->load($datatype, $document_id);
        return $object;
    }
}
