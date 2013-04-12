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
 * @subpackage User\Permission
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Gc\User\Permission;

use Gc\Db\AbstractTable;
use Zend\Db\Sql\Select;

/**
 * Collection of permissions
 *
 * @category   Gc
 * @package    Library
 * @subpackage User\Permission
 */
class Collection extends AbstractTable
{
    /**
     * List of permissions
     *
     * @var array
     */
    protected $permissions = array();

    /**
     * Table name
     *
     * @var string
     */
    protected $name = 'user_acl_permission';

    /**
     * Initiliaze permissions
     *
     * @return void
     */
    public function init()
    {
        $this->getPermissions(true);
    }

    /**
     * Get permissions
     *
     * @param boolean $forceReload Force reload
     *
     * @return array
     */
    public function getPermissions($forceReload = false)
    {
        if (empty($this->permissions) or $forceReload === true) {
            $select = new Select();
            $select->from('user_acl_permission')
                ->columns(
                    array(
                        'id',
                        'permission'
                    ),
                    true
                )->join(
                    'user_acl_resource',
                    'user_acl_resource.id = user_acl_permission.user_acl_resource_id',
                    array('resource')
                );

            $rows        = $this->fetchAll($select);
            $permissions = array();
            foreach ($rows as $permission) {
                if (empty($permissions[$permission['resource']])) {
                    $permissions[$permission['resource']] = array();
                }

                $permissions[$permission['resource']][$permission['id']] = $permission['permission'];
            }

            $this->permissions = $permissions;
        }

        return $this->permissions;
    }
}
