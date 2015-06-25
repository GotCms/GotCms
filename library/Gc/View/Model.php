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
        if (empty($array['content'])) {
            $viewTable->setContent($viewTable->getContent());
        }

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
        $viewTable->events()->trigger(__CLASS__, 'before.load', $viewTable);
        if (!empty($current)) {
            $viewTable = self::fromArray((array) $current);
            $viewTable->events()->trigger(__CLASS__, 'after.load', $viewTable);
            return $viewTable;
        } else {
            $viewTable->events()->trigger(__CLASS__, 'after.load.failed', $viewTable);
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
        $viewTable->events()->trigger(__CLASS__, 'before.load', $viewTable);
        if (!empty($current)) {
            $viewTable = self::fromArray((array) $current);
            $viewTable->events()->trigger(__CLASS__, 'after.load', $viewTable);
            return $viewTable;
        } else {
            $viewTable->events()->trigger(__CLASS__, 'after.load.failed', $viewTable);
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
        $this->events()->trigger(__CLASS__, 'before.save', $this);
        $arraySave = array(
            'name'        => $this->getName(),
            'identifier'  => $this->getIdentifier(),
            'description' => $this->getDescription(),
            'updated_at'  => new Expression('NOW()'),
        );

        try {
            if ($this->getId() == null) {
                $arraySave['created_at'] = new Expression('NOW()');
                $this->insert($arraySave);
                $this->setId($this->getLastInsertId());
            } else {
                $this->update($arraySave, array('id' => (int) $this->getId()));
            }

            $oldFilename = sprintf(GC_TEMPLATE_PATH . '/view/%s.phtml', $this->getOrigData('identifier'));
            if (file_exists($oldFilename)) {
                unlink($oldFilename);
            }

            file_put_contents($this->getFilePath(), $this->getData('content'));
            $this->events()->trigger(__CLASS__, 'after.save', $this);

            return $this->getId();
        } catch (\Exception $e) {
            $this->events()->trigger(__CLASS__, 'after.save.failed', $this);
            throw new \Gc\Exception($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Delete view model
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
        $filename = GC_TEMPLATE_PATH . '/view/%s.phtml';
        return sprintf($filename, $this->getIdentifier());
    }

    /**
     * Return file contents
     *
     * @return string
     */
    public function getContent()
    {
        if (file_exists($this->getFilePath())) {
            return file_get_contents($this->getFilePath());
        } else {
            return '';
        }
    }
}
