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
 * @category    Gc
 * @package     Library
 * @subpackage  User\Role
 * @author      Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license     GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link        http://www.got-cms.com
 */

namespace Gc\User\Role;

use Gc\Db\AbstractTable,
    Zend\Db\Sql\Select;
/**
 * Collection of Role Model
 */
class Collection extends AbstractTable
{
    /**
     * List of roles
     * @var array
     */
    protected $_roles;

    /**
     * Table name
     * @var string
     */
    protected $_name = 'user_acl_role';

    /**
     * Initiliaze role collection
     * @return void
     */
    public function init()
    {
        $this->getRoles(TRUE);
    }

    /**
     * Get Roles
     * @param boolean $force_reload
     * @return array \Gc\User\Role\Model
     */
    public function getRoles($force_reload = FALSE)
    {
        if(empty($this->_roles) or $force_reload === TRUE)
        {
            $rows = $this->select(function(Select $select)
            {
                $select->order('name');
            });

            $roles = array();
            foreach($rows as $row)
            {
                $roles[] = Model::fromArray((array)$row);
            }

            $this->_roles = $roles;
        }

        return $this->_roles;
    }
}
