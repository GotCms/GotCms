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
 * @category   Gc_Library
 * @package    Modules
 * @subpackage ActivityLog\Model
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Modules\ActivityLog\Model\Event;

use Gc\Db\AbstractTable;
use Gc\User\Model as UserModel;
use Zend\Db\Sql\Predicate\Expression;

/**
 * ActivityLog Event table
 *
 * @category   Gc_Library
 * @package    Modules
 * @subpackage ActivityLog\Model
 */
class Model extends AbstractTable
{
    /**
     * Table name
     *
     * @var string
     */
    protected $name = 'activity_log_event';

    /**
     * Initiliaze from array
     *
     * @param array $array Data
     *
     * @return \Modules\ActivityLog\Model\Event\Model
     */
    public static function fromArray(array $array)
    {
        $eventTable = new Model();
        $eventTable->setData($array);
        $eventTable->setOrigData();

        return $eventTable;
    }

    /**
     * Initiliaze from id
     *
     * @param integer $id Event id
     *
     * @return boolean|\Modules\ActivityLog\Model\Event\Model
     */
    public static function fromId($id)
    {
        $eventTable = new Model();
        $row        = $eventTable->fetchRow($eventTable->select(array('id' => (int) $id)));
        if (!empty($row)) {
            $eventTable->setData((array) $row);
            $eventTable->setOrigData();
            return $eventTable;
        } else {
            return false;
        }
    }

    /**
     * Retrieve user who trigger the event
     *
     * @return boolean|\Gc\User\Model
     */
    public function getUser()
    {
        if ($this->hasData('user') === false) {
            $this->setData('user', UserModel::fromId($this->getUserId()));
        }

        return $this->getData('user');
    }

    /**
     * Save event
     *
     * @return integer|boolean
     */
    public function save()
    {
        /**
         * No one can edit an event
         */
        if ($this->getId() !== null) {
            return true;
        }

        $arraySave = array(
            'created_at' => new Expression('NOW()'),
            'content' => $this->getContent(),
            'template_id' => $this->getTemplateId(),
            'user_id' => $this->getUserId(),
        );

        try {
            $this->insert($arraySave);
            $this->setId((int) $this->getLastInsertId());

            return $this->getId();
        } catch (\Exception $e) {
            throw new \Gc\Exception($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Delete event
     *
     * @return boolean
     */
    public function delete()
    {
        $id = $this->getId();
        if (!empty($id)) {
            try {
                parent::delete(array('id' => $id));
            } catch (\Exception $e) {
                throw new \Gc\Exception($e->getMessage(), $e->getCode(), $e);
            }

            unset($this);
            return true;
        }

        return false;
    }
}
