<?php

namespace Gc\User;

use Gc\User\Role\Model as RoleModel,
    Gc\User\Model as UserModel,
    Zend\Acl as ZendAcl,
    Zend\Db\TableGateway\TableGateway,
    Zend\Db\Sql\Select;

class Acl extends ZendAcl\Acl
{
    protected $_role_table = NULL;
    protected $_user_role = NULL;
    protected $_user_role_name = NULL;
    protected $_user = NULL;

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

    private function initRoles()
    {
        $roles = $this->_role_table->fetchAll($this->_role_table->select());
        foreach($roles as $role) {
            $this->addRole(new ZendAcl\Role\GenericRole($role['name']));
        }
    }
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

    private function roleResource()
    {
        $this->initResources();
        $select = new Select();
        $select->from('user_acl_role')
            ->columns(array(
                'user_acl_role.name AS name'
            ), FALSE)
            ->join('user_acl', 'user_acl.user_acl_role_id = user_acl_role.id', array())
            ->join('user_acl_permission', 'user_acl_permission.id = user_acl.user_acl_permission_id', array('permission'))
            ->join('user_acl_resource', 'user_acl_resource.id = user_acl_permission.user_acl_resource_id', array('resource'));

        $acl = $this->_role_table->fetchAll($select);

        foreach ($acl as $key=>$value) {
            $this->allow($value['name'], $value['resource'],$value['permission']);
        }
    }

    public function listRoles()
    {
        return $this->_role_table->fetchAll(
        $this->_role_table->select()
            ->from('acl_roles'));
    }

    public function getRoleId($role_name)
    {
        return $this->_role_table->fetchRow(
            $this->_role_table->select(array('name' => $role_name))
        );
    }

    public function listResources()
    {
        return $this->_role_table->fetchAll(
            $this->_role_table->select()
                ->from('user_acl_resource')
        );
    }

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
