<?php

namespace Gc\User\Role;

use Gc\Db\AbstractTable,
    Zend\Db\Sql\Select,
    Zend\Db\TableGateway\TableGateway;

class Model extends AbstractTable
{
    protected $_name = 'user_acl_role';

    /**
    * @desc Save user
    */
    public function save()
    {
        $array_save = array(
            'name' => $this->getName()
            , 'description' => $this->getDescription()
        );

        try
        {
            $role_id = $this->getId();
            if(empty($role_id))
            {
                $this->insert($array_save);
                $this->setId($this->getLastInsertId());
            }
            else
            {
                $this->update($array_save, 'id = '.$this->getId());
            }

            $permissions = $this->getPermissions();
            if(!empty($permissions))
            {
                $acl_table = new TableGateway('user_acl', $this->getAdapter());
                $acl_table->delete(sprintf('user_acl_role_id = %s', $this->getId()));

                foreach($permissions as $permission_id => $value)
                {
                    if(!empty($value))
                    {
                        $acl_table->insert(array('user_acl_role_id' => $this->getId(), 'user_acl_permission_id' => $permission_id));
                    }
                }
            }

            return $this->getId();
        }
        catch (Exception $e)
        {
            /**
            * TODO(Make \Gc\Error)
            */
            \Gc\Error::set(get_class($this), $e);
        }

        return FALSE;
    }

    /**
    * @desc Delete user
    */
    public function delete()
    {
        $id = $this->getId();
        if(!empty($id))
        {
            parent::delete('id = '.$this->getId());
            unset($this);
            return TRUE;
        }

        return FALSE;
    }

    /**
    * @param array $array
    * @return Gc\User
    */
    static function fromArray(Array $array)
    {
        $role_table = new Model();
        $role_table->setData($array);

        return $role_table;
    }

    /**
    * @param integer $id
    * @return Gc\User
    */
    static function fromId($id)
    {
        $role_table = new Model();
        $row = $role_table->select(array('id' => $id));
        if(!empty($row))
        {
            return $role_table->setData((array)$row->current());
        }
        else
        {
            return FALSE;
        }
    }

    public function getUserPermissions()
    {
        $select = new Select();
        $select->from('user_acl_role')
            ->columns(array(
                'user_acl_permission.id AS userPermissionId'
            ), FALSE)
            ->join('user_acl', 'user_acl.user_acl_role_id = user_acl_role.id', array())
            ->join('user_acl_permission', 'user_acl_permission.id = user_acl.user_acl_permission_id', array('permission'))
            ->join('user_acl_resource', 'user_acl_resource.id = user_acl_permission.user_acl_resource_id', array('resource'))
            ->where(sprintf('user_acl_role.id = %s', $this->getId()));

        $permissions = $this->fetchAll($select);

        $return = array();
        foreach($permissions as $permission)
        {
            if(empty($return[$permission['resource']]))
            {
                $return[$permission['resource']] = array();
            }

            $return[$permission['resource']][$permission['userPermissionId']] = $permission['permission'];
        }

        return $return;
    }
}
