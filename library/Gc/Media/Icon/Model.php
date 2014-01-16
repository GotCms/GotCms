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

namespace Gc\Media\Icon;

use Gc\Db\AbstractTable;

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
    protected $name = 'icon';

    /**
     * Initiliaze from array
     *
     * @param array $array Data
     *
     * @return \Gc\Media\Icon\Model
     */
    public static function fromArray(array $array)
    {
        $iconTable = new Model();
        $iconTable->setData($array);
        $iconTable->setOrigData();

        return $iconTable;
    }

    /**
     * Initiliaze from id
     *
     * @param integer $iconId Icon id
     *
     * @return \Gc\Media\Icon\Model
     */
    public static function fromId($iconId)
    {
        $iconTable = new Model();
        $row       = $iconTable->fetchRow($iconTable->select(array('id' => (int) $iconId)));
        $iconTable->events()->trigger(__CLASS__, 'before.load', $iconTable);
        if (!empty($row)) {
            $iconTable->setData((array) $row);
            $iconTable->setOrigData();
            $iconTable->events()->trigger(__CLASS__, 'after.load', $iconTable);
            return $iconTable;
        } else {
            $iconTable->events()->trigger(__CLASS__, 'after.load.failed', $iconTable);
            return false;
        }
    }

    /**
     * Save icon
     *
     * @return integer
     */
    public function save()
    {
        $this->events()->trigger(__CLASS__, 'before.save', $this);
        $arraySave = array(
            'name' => $this->getName(),
            'url' => $this->getUrl(),
        );

        try {
            $id = $this->getId();
            if ($this->getId() == null) {
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
     * Delete icon
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
}
