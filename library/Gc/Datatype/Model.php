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

use Gc\Db\AbstractTable;
use Gc\Form\AbstractForm;
use Gc\Property\Model as PropertyModel;
use Datatypes;
use Zend\Form\Fieldset;
use Zend\ServiceManager\ServiceManager;

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
    protected $name = 'datatype';

    /**
     * Set prevalue value
     *
     * @param mixed $value Value
     *
     * @return \Gc\Datatype\Model
     */
    public function setPrevalueValue($value)
    {
        if (is_string($value)) {
            $value = unserialize($value);
        }

        $this->setData('prevalue_value', $value);

        return $this;
    }

    /**
     * Get Model from array
     *
     * @param array $array Data
     *
     * @return Model
     */
    public static function fromArray(array $array)
    {
        $datatypeTable = new Model();
        $datatypeTable->setData($array);
        $datatypeTable->setOrigData();

        return $datatypeTable;
    }

    /**
     * Get model from id
     *
     * @param integer $datatypeId Datatype id
     *
     * @return AbstractTable
     */
    public static function fromId($datatypeId)
    {
        $datatypeTable = new Model();
        $row           = $datatypeTable->fetchRow($datatypeTable->select(array('id' => (int) $datatypeId)));
        $datatypeTable->events()->trigger(__CLASS__, 'before.load', $datatypeTable);
        if (!empty($row)) {
            $datatypeTable->setData((array) $row);
            $datatypeTable->setOrigData();
            $datatypeTable->events()->trigger(__CLASS__, 'after.load', $datatypeTable);
            return $datatypeTable;
        } else {
            $datatypeTable->events()->trigger(__CLASS__, 'after.load.failed', $datatypeTable);
            return false;
        }
    }

    /**
     * Save Datatype model
     *
     * @return integer
     */
    public function save()
    {
        $this->events()->trigger(__CLASS__, 'before.save', $this);
        $arraySave = array(
            'name' => $this->getName(),
            'prevalue_value' => serialize($this->getPrevalueValue()),
            'model' => $this->getModel(),
        );

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

    /**
     * Delete datatype model
     *
     * @return boolean
     */
    public function delete()
    {
        $this->events()->trigger(__CLASS__, 'before.delete', $this);
        $id = $this->getId();
        if (!empty($id)) {
            try {
                parent::delete(array('id' => $id));
            } catch (\Exception $e) {
                throw new \Gc\Exception($e->getMessage(), $e->getCode(), $e);
            }

            $this->events()->trigger(__CLASS__, 'after.delete', $this);
            unset($this);

            return true;
        }

        $this->events()->trigger(__CLASS__, 'after.delete.failed', $this);

        return false;
    }

    /**
     * Save prevalue editor
     *
     * @param AbstractDatatype $datatype Datatype
     *
     * @return Model
     */
    public static function savePrevalueEditor(AbstractDatatype $datatype)
    {
        $datatype->getPrevalueEditor()->save();
        return $datatype->getConfig();
    }

    /**
     * Save editor
     *
     * @param ServiceManager $serviceManager Service manager
     * @param PropertyModel  $property       Property
     *
     * @return boolean
     */
    public static function saveEditor(ServiceManager $serviceManager, PropertyModel $property)
    {
        $datatype = self::loadDatatype($serviceManager, $property->getDatatypeId(), $property->getDocumentId());
        $datatype->getEditor($property)->save();
        if (!$property->saveValue()) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Load prevalue editor
     *
     * @param AbstractDatatype $datatype Datatype
     *
     * @return Fieldset
     */
    public static function loadPrevalueEditor(AbstractDatatype $datatype)
    {
        $fieldset = new Fieldset('prevalue-editor');
        AbstractForm::addContent($fieldset, $datatype->getPrevalueEditor()->load());
        return $fieldset;
    }

    /**
     * Load editor
     *
     * @param ServiceManager $serviceManager Service manager
     * @param PropertyModel  $property       Property
     *
     * @return mixed
     */
    public static function loadEditor(ServiceManager $serviceManager, PropertyModel $property)
    {
        $datatype = self::loadDatatype($serviceManager, $property->getDatatypeId(), $property->getDocumentId());

        return $datatype->getEditor($property)->load();
    }

    /**
     * Load Datatype
     *
     * @param ServiceManager $serviceManager Service manager
     * @param integer        $datatypeId     Datatype id
     * @param integer        $documentId     Optional document id
     *
     * @return mixed
     */
    public static function loadDatatype(ServiceManager $serviceManager, $datatypeId, $documentId = null)
    {
        $datatype = Model::fromId($datatypeId);
        $class    = 'Datatypes\\' . $datatype->getModel() . '\Datatype';

        $object = new $class();
        $object->setRequest($serviceManager->get('Request'))
            ->setRouter($serviceManager->get('Router'))
            ->setHelperManager($serviceManager->get('viewhelpermanager'))
            ->load($datatype, $documentId);
        return $object;
    }
}
