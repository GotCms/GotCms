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
 * @subpackage Module
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Gc\Module;

use Gc\Db\AbstractTable;
use Gc\ModuleType;
use Gc\Media\Icon;
use Gc\View;
use Zend\Db\Sql\Predicate\Expression;

/**
 * Module Model
 *
 * @category   Gc
 * @package    Library
 * @subpackage Module
 */
class Model extends AbstractTable
{
    /**
     * Table name
     *
     * @var string
     */
    protected $name = 'module';


    /**
     * Initiliaze module from name
     *
     * @param array $moduleName Module name
     *
     * @return \Gc\Module\Model
     */
    public static function fromName($moduleName)
    {
        $moduleTable = new Model();
        $row         = $moduleTable->fetchRow($moduleTable->select(array('name' => $moduleName)));
        if (!empty($row)) {
            $moduleTable->setData((array) $row);
            $moduleTable->setOrigData();
            return $moduleTable;
        } else {
            return false;
        }
    }

    /**
     * Initialize module from array
     *
     * @param array $array Data
     *
     * @return \Gc\Module\Model
     */
    public static function fromArray(array $array)
    {
        $moduleTable = new Model();
        $moduleTable->setData($array);
        $moduleTable->setOrigData();

        return $moduleTable;
    }

    /**
     * Initiliaze module from id
     *
     * @param array $moduleId Module id
     *
     * @return \Gc\Module\Model
     */
    public static function fromId($moduleId)
    {
        $moduleTable = new Model();
        $row         = $moduleTable->fetchRow($moduleTable->select(array('id' => (int) $moduleId)));
        if (!empty($row)) {
            $moduleTable->setData((array) $row);
            $moduleTable->setOrigData();
            return $moduleTable;
        } else {
            return false;
        }
    }

    /**
     * Save Model
     *
     * @return integer
     */
    public function save()
    {
        $this->events()->trigger(__CLASS__, 'beforeSave', null, array('object' => $this));
        $arraySave = array(
            'name' => $this->getName(),
        );

        try {
            $moduleId = $this->getId();
            if (empty($moduleId)) {
                $arraySave['created_at'] = new Expression('NOW()');
                $this->insert($arraySave);
                $this->setId($this->getLastInsertId());
            } else {
                $this->update($arraySave, array('id' => $this->getId()));
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
     * Delete module
     *
     * @return boolean
     */
    public function delete()
    {
        $this->events()->trigger(__CLASS__, 'beforeDelete', null, array('object' => $this));
        $moduleId = $this->getId();
        if (!empty($moduleId)) {
            try {
                parent::delete(array('id' => $moduleId));
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
}
