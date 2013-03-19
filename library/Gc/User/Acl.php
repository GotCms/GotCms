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
 * @subpackage User
 * @author     Pierre Rambaud (GoT) http://rambaudpierre.fr
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Gc\User;

use Gc\User\Role\Model as RoleModel;
use Gc\User\Model as UserModel;
use Zend\Permissions\Acl as ZendAcl;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;

/**
 * Use Acl
 *
 * @category   Gc
 * @package    Library
 * @subpackage User
 */
class Acl extends ZendAcl\Acl
{
    /**
     * Role model
     *
     * @var \Gc\User\Role\Model
     */
    protected $roleTable = null;

    /**
     * User Role id
     *
     * @var integer role_id
     */
    protected $userRole = null;

    /**
     * User Role name
     *
     * @var mixed role name
     */
    protected $userRoleName = null;

    /**
     * User model
     *
     * @var \Gc\User\Model
     */
    protected $user = null;

    /**
     * Initiliaze Acl
     *
     * @param UserModel $user_model User model
     *
     * @return void
     */
    public function __construct(UserModel $user_model)
    {
        $this->roleTable = new RoleModel();
        $this->roleResource();
        $this->user = $user_model;

        $select = new Select();
        $select->from('user_acl_role')
            ->join('user', 'user.user_acl_role_id = user_acl_role.id');
        $select->where->equalTo('user.id', $this->user->getId());
        $user_role = $this->roleTable->fetchRow($select);

        $this->userRole     = empty($user_role['role_id']) ? 0 : $user_role['role_id'];
        $this->userRoleName = empty($user_role['name']) ? null : $user_role['name'];
    }

    /**
     * Initiliaze Roles
     *
     * @return void
     */
    protected function initRoles()
    {
        $roles = $this->roleTable->fetchAll($this->roleTable->select());
        foreach ($roles as $role) {
            $this->addRole(new ZendAcl\Role\GenericRole($role['name']));
        }
    }

    /**
     * Initiliaze resources
     *
     * @return void
     */
    protected function initResources()
    {
        $this->initRoles();
        $select = new Select();
        $select->from('user_acl_resource');
        $resources = $this->roleTable->fetchAll($select);

        foreach ($resources as $key => $value) {
            if (!$this->hasResource($value['resource'])) {
                $this->addResource(new ZendAcl\Resource\GenericResource($value['resource']));
            }
        }
    }

    /**
     * Initiliaze role resource
     *
     * @return void
     */
    protected function roleResource()
    {
        $this->initResources();
        $select = new Select();
        $select->from('user_acl_role')
            ->columns(
                array(
                    'name'
                ),
                true
            )
            ->join('user_acl', 'user_acl.user_acl_role_id = user_acl_role.id', array())
            ->join(
                'user_acl_permission',
                'user_acl_permission.id = user_acl.user_acl_permission_id',
                array('permission')
            )
            ->join(
                'user_acl_resource',
                'user_acl_resource.id = user_acl_permission.user_acl_resource_id',
                array('resource')
            );

        $acl = $this->roleTable->fetchAll($select);

        foreach ($acl as $key => $value) {
            $this->allow($value['name'], $value['resource'], $value['permission']);
        }
    }

    /**
     * List Roles
     *
     * @return array
     */
    public function listRoles()
    {
        return $this->roleTable->fetchAll($this->roleTable->select());
    }

    /**
     * Get role id from role name
     *
     * @param string $role_name Role name
     *
     * @return array|\Zend\Db\ResultSet\RowObjectInterface
     */
    public function getRoleId($role_name)
    {
        return $this->roleTable->fetchRow(
            $this->roleTable->select(array('name' => $role_name))
        );
    }

    /**
     * List resources
     *
     * @return array
     */
    public function listResources()
    {
        $select = new Select();
        $select->from('user_acl_resource');
        return $this->roleTable->fetchAll($select);
    }

    /**
     * List all resources by group
     *
     * @param array $group Resource group
     *
     * @return array
     */
    public function listResourcesByGroup($group)
    {
        $result = null;
        $select = new Select();
        $select->from(array('uar' => 'user_acl_resource'))
            ->join(array('uap' => 'user_acl_permission'), 'uar.id = uap.user_acl_resource_id')
            ->where->equalTo('uar.resource', $group);
        $group = $this->roleTable->fetchAll($select);

        foreach ($group as $key => $value) {
            if ($this->isAllowed($this->user->getRole()->getName(), $value['resource'], $value['permission'])) {
                $result[] = $value['permission'];
            }
        }

        return $result;
    }
}
