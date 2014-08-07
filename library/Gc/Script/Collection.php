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
use Zend\Db\Sql\Select;

/**
 * Collection of Script Model
 *
 * @category   Gc
 * @package    Library
 * @subpackage Script
 */
class Collection extends AbstractTable
{
    /**
     * Collection of \Gc\Script\Model
     *
     * @var array
     */
    protected $scripts = null;

    /**
     * Table name
     *
     * @var string
     */
    protected $name = 'script';

    /**
     * Get scripts
     *
     * @param boolean $forceReload to initiliaze scripts
     *
     * @return array
     */
    public function getAll($forceReload = false)
    {
        if ($forceReload or $this->scripts === null) {
            $rows = $this->fetchAll(
                $this->select(
                    function (Select $select) {
                        $select->order('name ASC');
                    }
                )
            );

            $scripts = array();
            foreach ($rows as $row) {
                $scripts[] = Model::fromArray((array) $row);
            }

            $this->scripts = $scripts;
        }

        return $this->scripts;
    }
}
