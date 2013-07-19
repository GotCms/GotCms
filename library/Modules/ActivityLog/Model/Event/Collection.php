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
use Zend\Db\Sql\Select;

/**
 * ActivityLog Event table
 *
 * @category   Gc_Library
 * @package    Modules
 * @subpackage ActivityLog\Model
 */
class Collection extends AbstractTable
{
    /**
     * Table name
     *
     * @var string
     */
    protected $name = 'activity_log_event';

    /**
     * Return all documents with Event(s)
     *
     * @return array
     */
    public function getEvents()
    {
        $events = array();
        $rows   = $this->fetchAll(
            $this->select(
                function (Select $select) {
                    $select->order('created_at DESC');
                }
            )
        );
        foreach ($rows as $row) {
            $events[] = Model::fromArray($row);
        }

        return $events;
    }
}
