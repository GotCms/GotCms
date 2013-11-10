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
 * @subpackage Layout
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Gc\Layout;

use Gc\Db\AbstractTable;
use Zend\Db\Sql\Predicate\Expression;

/**
 * Layout Model
 *
 * @category   Gc
 * @package    Library
 * @subpackage Layout
 */
class Model extends AbstractTable
{
    /**
     * Table name
     *
     * @var string
     */
    protected $name = 'layout';

    /**
     * Initiliaze layout
     *
     * @param integer $id Layout id
     *
     * @return \Gc\Layout\Model
     */
    public function init($id = null)
    {
        $this->setId($id);

        return $this;
    }

    /**
     * Initiliaze from array
     *
     * @param array $array Data
     *
     * @return \Gc\Layout\Model
     */
    public static function fromArray(array $array)
    {
        $layoutTable = new Model();
        $layoutTable->setData($array);
        $layoutTable->setOrigData();

        return $layoutTable;
    }

    /**
     * Initiliaze from id
     *
     * @param integer $layoutId Layout id
     *
     * @return \Gc\Layout\Model
     */
    public static function fromId($layoutId)
    {
        $layoutTable = new Model();
        $row         = $layoutTable->fetchRow($layoutTable->select(array('id' => (int) $layoutId)));
        $layoutTable->events()->trigger(__CLASS__, 'before.load', null, array('object' => $layoutTable));
        if (!empty($row)) {
            $layoutTable->setData((array) $row);
            $layoutTable->setOrigData();
            $layoutTable->events()->trigger(__CLASS__, 'after.load', null, array('object' => $layoutTable));
            return $layoutTable;
        } else {
            $layoutTable->events()->trigger(__CLASS__, 'after.load.failed', null, array('object' => $layoutTable));
            return false;
        }
    }

    /**
     * Initiliaze from identifier
     *
     * @param string $identifier Identifier
     *
     * @return \Gc\Layout\Model
     */
    public static function fromIdentifier($identifier)
    {
        $layoutTable = new Model();
        $row         = $layoutTable->fetchRow($layoutTable->select(array('identifier' => $identifier)));
        $layoutTable->events()->trigger(__CLASS__, 'before.load', null, array('object' => $layoutTable));
        if (!empty($row)) {
            $layoutTable->setData((array) $row);
            $layoutTable->setOrigData();
            $layoutTable->events()->trigger(__CLASS__, 'after.load', null, array('object' => $layoutTable));
            return $layoutTable;
        } else {
            $layoutTable->events()->trigger(__CLASS__, 'after.load.failed', null, array('object' => $layoutTable));
            return false;
        }
    }

    /**
     * Save layout
     *
     * @return integer
     */
    public function save()
    {
        $this->events()->trigger(__CLASS__, 'before.save', null, array('object' => $this));
        $arraySave = array('name' => $this->getName(),
            'identifier' => $this->getIdentifier(),
            'description' => $this->getDescription(),
            'content' => $this->getContent(),
            'updated_at' => new Expression('NOW()')
        );

        try {
            $id = $this->getId();
            if (empty($id)) {
                $arraySave['created_at'] = new Expression('NOW()');
                $this->insert($arraySave);
                $this->setId($this->getLastInsertId());
            } else {
                $this->update($arraySave, array('id' => $this->getId()));
            }

            file_put_contents($this->getFilePath(), $this->getContent());
            $this->events()->trigger(__CLASS__, 'after.save', null, array('object' => $this));

            return $this->getId();
        } catch (\Exception $e) {
            $this->events()->trigger(__CLASS__, 'after.save.failed', null, array('object' => $this));
            throw new \Gc\Exception($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Delete layout
     *
     * @return boolean
     */
    public function delete()
    {
        $this->events()->trigger(__CLASS__, 'before.delete', null, array('object' => $this));
        $id = $this->getId();
        if (!empty($id)) {
            try {
                parent::delete(array('id' => $id));
            } catch (\Exception $e) {
                throw new \Gc\Exception($e->getMessage(), $e->getCode(), $e);
            }

            if (file_exists($this->getFilePath())) {
                unlink($this->getFilePath());
            }

            $this->events()->trigger(__CLASS__, 'after.delete', null, array('object' => $this));
            unset($this);

            return true;
        }

        $this->events()->trigger(__CLASS__, 'after.delete.failed', null, array('object' => $this));

        return false;
    }

    /**
     * Return file path
     *
     * @return string
     */
    public function getFilePath()
    {
        $filename = GC_TEMPLATE_PATH . '/layout/%s.phtml';
        return sprintf($filename, $this->getIdentifier());
    }
}
