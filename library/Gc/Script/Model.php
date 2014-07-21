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
 * @subpackage Script
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Gc\Script;

use Gc\Db\AbstractTable;

/**
 * Script Model
 *
 * @category   Gc
 * @package    Library
 * @subpackage Script
 */
class Model extends AbstractTable
{
    /**
     * Table name
     *
     * @var string
     */
    protected $name = 'script';

    /**
     * Initiliaze
     *
     * @param integer $id Script id
     *
     * @return \Gc\Script\Model
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
     * @return Model
     */
    public static function fromArray(array $array)
    {
        $scriptTable = new Model();
        $scriptTable->setData($array);
        $scriptTable->setOrigData();

        return $scriptTable;
    }

    /**
     * Initiliaze from id
     *
     * @param integer $scriptId Script id
     *
     * @return \Gc\Script\Model
     */
    public static function fromId($scriptId)
    {
        $scriptTable = new Model();
        $row         = $scriptTable->fetchRow($scriptTable->select(array('id' => (int) $scriptId)));
        $scriptTable->events()->trigger(__CLASS__, 'before.load', $scriptTable);
        if (!empty($row)) {
            $scriptTable->setData((array) $row);
            $scriptTable->setOrigData();
            $scriptTable->events()->trigger(__CLASS__, 'after.load', $scriptTable);
            return $scriptTable;
        } else {
            $scriptTable->events()->trigger(__CLASS__, 'after.load.failed', $scriptTable);
            return false;
        }
    }
    /**
     * Initiliaze from id
     *
     * @param integer $identifier Identifier
     *
     * @return \Gc\Script\Model
     */
    public static function fromIdentifier($identifier)
    {
        $scriptTable = new Model();
        $row         = $scriptTable->fetchRow($scriptTable->select(array('identifier' => $identifier)));
        $scriptTable->events()->trigger(__CLASS__, 'before.load', $scriptTable);
        if (!empty($row)) {
            $scriptTable->setData((array) $row);
            $scriptTable->setOrigData();
            $scriptTable->events()->trigger(__CLASS__, 'after.load', $scriptTable);
            return $scriptTable;
        } else {
            $scriptTable->events()->trigger(__CLASS__, 'after.load.failed', $scriptTable);
            return false;
        }
    }

    /**
     * Save script model
     *
     * @return integer
     */
    public function save()
    {
        $this->events()->trigger(__CLASS__, 'before.save', $this);
        $this->setUpdatedAt(date('Y-m-d H:i:s'));
        $arraySave = array(
            'name' => $this->getName(),
            'identifier' => $this->getIdentifier(),
            'description' => $this->getDescription(),
            'content' => $this->getContent(),
            'updated_at'  => $this->getUpdatedAt(),
        );

        try {
            if ($this->getId() == null) {
                $this->setCreatedAt($this->getUpdatedAt());
                $arraySave['created_at'] = $this->getCreatedAt();
                $this->insert($arraySave);
                $this->setId($this->getLastInsertId());
            } else {
                $this->update($arraySave, array('id' => (int) $this->getId()));
            }

            $oldFilename = sprintf(GC_TEMPLATE_PATH . '/script/%s.phtml', $this->getOrigData('identifier'));
            if (file_exists($oldFilename)) {
                unlink($oldFilename);
            }

            file_put_contents($this->getFilePath(), $this->getContent());
            $this->events()->trigger(__CLASS__, 'after.save', $this);

            return $this->getId();
        } catch (\Exception $e) {
            $this->events()->trigger(__CLASS__, 'after.save.failed', $this);
            throw new \Gc\Exception($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Delete script model
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

            if (file_exists($this->getFilePath())) {
                unlink($this->getFilePath());
            }

            $this->events()->trigger(__CLASS__, 'after.delete', $this);
            unset($this);

            return true;
        }

        $this->events()->trigger(__CLASS__, 'after.delete.failed', $this);

        return false;
    }

    /**
     * Return file path
     *
     * @return string
     */
    public function getFilePath()
    {
        $filename = GC_TEMPLATE_PATH . '/script/%s.phtml';
        return sprintf($filename, $this->getIdentifier());
    }

    /**
     * Return file contents
     *
     * @return string
     */
    public function getFileContents()
    {
        return file_get_contents($this->getFilePath());
    }
}
