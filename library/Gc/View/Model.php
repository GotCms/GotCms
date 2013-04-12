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
 * @subpackage View
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Gc\View;

use Gc\Db\AbstractTable;
use Zend\Db\Sql\Predicate\Expression;

/**
 * Class for manage View
 *
 * @category   Gc
 * @package    Library
 * @subpackage View
 */
class Model extends AbstractTable
{
    /**
     * Table name
     *
     * @var string
     */
    protected $name = 'view';

    /**
     * Initiliaze
     *
     * @param integer $id View id
     *
     * @return \Gc\View\Model
     */
    public function init($id = null)
    {
        $this->setId($id);
    }

    /**
     * Initiliaze from array
     *
     * @param array $array Data
     *
     * @return \Gc\View\Model
     */
    public static function fromArray(array $array)
    {
        $viewTable = new Model();
        $viewTable->setData($array);
        $viewTable->setOrigData();

        return $viewTable;
    }

    /**
     * Initiliaze from id
     *
     * @param integer $viewId View id
     *
     * @return \Gc\View\Model
     */
    public static function fromId($viewId)
    {
        $viewTable = new Model();
        $row       = $viewTable->select(array('id' => (int) $viewId));
        $current   = $row->current();
        if (!empty($current)) {
            $viewTable->setData((array) $current);
            $viewTable->setOrigData();
            return $viewTable;
        } else {
            return false;
        }
    }

    /**
     * Initiliaze from identifier
     *
     * @param string $identifier Identifier
     *
     * @return \Gc\View\Model
     */
    public static function fromIdentifier($identifier)
    {
        $viewTable = new Model();
        $row       = $viewTable->select(array('identifier' => $identifier));
        $current   = $row->current();
        if (!empty($current)) {
            $viewTable->setData((array) $current);
            $viewTable->setOrigData();
            return $viewTable;
        } else {
            return false;
        }
    }

    /**
     * Save view model
     *
     * @return integer
     */
    public function save()
    {
        $this->events()->trigger(__CLASS__, 'beforeSave', null, array('object' => $this));
        $arraySave = array(
            'name'        => $this->getName(),
            'identifier'  => $this->getIdentifier(),
            'description' => $this->getDescription(),
            'content'     => $this->getContent(),
            'updated_at'  => new Expression('NOW()'),
        );

        try {
            $id = $this->getId();
            if ($this->getId() == null) {
                $arraySave['created_at'] = new Expression('NOW()');
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
     * Delete view model
     *
     * @return boolean
     */
    public function delete()
    {
        $this->events()->trigger(__CLASS__, 'beforeDelete', null, array('object' => $this));
        $id = $this->getId();
        if (!empty($id)) {
            try {
                parent::delete(array('id' => $id));
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
