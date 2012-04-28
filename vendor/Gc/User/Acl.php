<?php

namespace Gc\User;

use Gc\Db\AbstractTable,
    Zend\Db\Sql\Expression,
    Gc\User\Role\Model as RoleModel,
    Zend\Acl as ZendAcl,
    Zend\Db\TableGateway\TableGateway,
    Zend\Db\Sql\Select;

class Acl extends ZendAcl\Acl
{
    protected $_name = 'user_acl_roles';
    protected $_table = NULL;
    protected $_user_role = NULL;
    protected $_user_role_name = NULL;
    protected $_user_id = NULL;

    public function __construct($user_id)
    {
        $this->_table = new RoleModel();
        $this->roleResource();
        $this->_user_id = $user_id;

        $select = new Select();
        $user_role = $this->_table->fetchRow(
            $select->from('user_acl_roles')
                ->join('users', 'users.user_acl_role_id = user_acl_roles.id')
                ->where('users.id = ?', $this->_user_id)
                ->where('users.user_acl_role_id = user_acl_roles.id')
        );

        $this->_user_role = empty($user_role['role_id']) ? 0 : $user_role['role_id'];
        $this->_user_role_name = empty($user_role['name']) ? NULL : $user_role['name'];


        $this->addRole(new ZendAcl\Role\GenericRole($this->_user_role_name), $this->_user_role);
    }

    private function initRoles()
    {
        $roles = $this->_table->fetchAll($this->select());

        $this->addRole(new ZendAcl\Role\GenericRole($roles[0]['name']));

        for ($i = 1; $i < count($roles); $i++) {
            $this->addRole(new ZendAcl\Role\GenericRole($roles[$i]['name']), $roles[$i-1]['name']);
        }
    }
    protected function initResources()
    {
        $this->initRoles();
        $select = new Select();
        $select->from('user_acl_resources');
        $resources = $this->_table->fetchAll($select);

        foreach ($resources as $key=>$value){
            if (!$this->has($value['resource'])) {
                $this->addResource(new ZendAcl\Resource\GenericResource($value['resource']));
            }
        }
    }

    private function roleResource()
    {
        $this->initResources();
        $select = new Select();
        $select->from('user_acl_roles')
            ->join('user_acl_permissions', 'user_acl_permissions.user_acl_role_id = user_acl_roles.id')
            ->join('user_acl_resources', 'user_acl_resources.id = user_acl_permissions.user_acl_resource_id');

        $acl = $this->_table->fetchAll($select);

        foreach ($acl as $key=>$value) {
            $this->allow($value['role_name'], $value['resource'],$value['permission']);
        }
    }

    public function listRoles()
    {
        return $this->_table->fetchAll(
        $this->select()
            ->from('acl_roles'));
    }

    public function getRoleId($roleName)
    {
        return $this->_table->fetchRow(
        $this->select()
            ->from('acl_roles', 'role_id')
            ->where('acl_roles.role_name = "' . $roleName . '"'));
    }

    public function listResources()
    {
        return $this->_table->fetchAll(
        $this->select()
            ->from('acl_resources')
            ->from('acl_permissions')
            ->where('resource_uid = uid'));
    }

    public function listResourcesByGroup($group)
    {
        $result = null;
        $group = $this->_table->fetchAll($this->select()
            ->from('acl_resources')
            ->from('acl_permissions')
            ->where('acl_resources.resource = "' . $group . '"')
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
