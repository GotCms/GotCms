<?php
/**
 * This source file is part of Got CMS.
 *
 * Got CMS is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Got CMS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License along
 * with Got CMS. If not, see <http://www.gnu.org/licenses/lgpl-3.0.html>.
 *
 * PHP Version >=5.3
 *
 * @category    Gc
 * @package     Library
 * @subpackage  User
 * @author      Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license     GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link        http://www.got-cms.com
 */

namespace Gc\User;

use Gc\User\Role\Model as RoleModel,
    Gc\User\Model as UserModel,
    Zend\Acl as ZendAcl,
    Zend\Db\TableGateway\TableGateway,
    Zend\Db\Sql\Select;

class Acl extends ZendAcl\Acl
{
    /**
     * @var \Gc\User\Role\Model
     */
    protected $_role_table = NULL;

    /**
     * @var integer role_id
     */
    protected $_user_role = NULL;

    /**
     * @var mixed role name
     */
    protected $_user_role_name = NULL;

    /**
     * @var \Gc\User\Model
     */
    protected $_user = NULL;

    /**
     * Initiliaze Acl
     * @return void
     */
    public function __construct(UserModel $user_model)
    {
        $this->_role_table = new RoleModel();
        $this->roleResource();
        $this->_user = $user_model;

        $select = new Select();
        $user_role = $this->_role_table->fetchRow(
            $select->from('user_acl_role')
                ->join('user', 'user.user_acl_role_id = user_acl_role.id')
                ->where(sprintf('"user".id = %s', $this->_user->getId()))
        );

        $this->_user_role = empty($user_role['role_id']) ? 0 : $user_role['role_id'];
        $this->_user_role_name = empty($user_role['name']) ? NULL : $user_role['name'];
    }

    /**
     * Initiliaze Roles
     * @return void
     */
    private function initRoles()
    {
        $roles = $this->_role_table->fetchAll($this->_role_table->select());
        foreach($roles as $role) {
            $this->addRole(new ZendAcl\Role\GenericRole($role['name']));
        }
    }

    /**
     * Initiliaze resources
     * @return void
     */
    protected function initResources()
    {
        $this->initRoles();
        $select = new Select();
        $select->from('user_acl_resource');
        $resources = $this->_role_table->fetchAll($select);

        foreach ($resources as $key=>$value){
            if (!$this->hasResource($value['resource'])) {
                $this->addResource(new ZendAcl\Resource\GenericResource($value['resource']));
            }
        }
    }

    /**
     * Initiliaze role resource
     * @return void
     */
    private function roleResource()
    {
        $this->initResources();
        $select = new Select();
        $select->from('user_acl_role')
            ->columns(array(
                'name'
            ), TRUE)
            ->join('user_acl', 'user_acl.user_acl_role_id = user_acl_role.id', array())
            ->join('user_acl_permission', 'user_acl_permission.id = user_acl.user_acl_permission_id', array('permission'))
            ->join('user_acl_resource', 'user_acl_resource.id = user_acl_permission.user_acl_resource_id', array('resource'));

        $acl = $this->_role_table->fetchAll($select);

        foreach ($acl as $key=>$value) {
            $this->allow($value['name'], $value['resource'],$value['permission']);
        }
    }

    /**
     * List Roles
     * @return array
     */
    public function listRoles()
    {
        return $this->_role_table->fetchAll(
        $this->_role_table->select()
            ->from('acl_roles'));
    }

    /**
     * Get role id from role name
     * @param string $role_name
     * @return array|Zend\Db\ResultSet\RowObjectInterface
     */
    public function getRoleId($role_name)
    {
        return $this->_role_table->fetchRow(
            $this->_role_table->select(array('name' => $role_name))
        );
    }

    /**
     * List resources
     * @return array
     */
    public function listResources()
    {
        return $this->_role_table->fetchAll(
            $this->_role_table->select()
                ->from('user_acl_resource')
        );
    }

    /**
     * List all resources by group
     * @return array
     */
    public function listResourcesByGroup($group)
    {
        $result = null;
        $group = $this->_role_table->fetchAll($this->_role_table->select()
            ->from('acl_resources')
            ->from('acl_permissions')
            ->where(sprintf("\"acl_resources\".resource = '%s'", $group))
            ->where('uid = resource_uid')
        );

        foreach ($group as $key=>$value) {
            if($this->isAllowed($this->_user, $value['resource'], $value['permission'])) {
                $result[] = $value['permission'];
            }
        }

        return $result;
    }
}
