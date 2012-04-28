<?php

namespace Gc\User\Role;

use Gc\Db\AbstractTable,
    Zend\Db\Sql\Expression,
    Zend\Db\Sql\Select;

class Model extends AbstractTable
{
    protected $_name = 'user_acl_roles';

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
            if($this->getId() === NULL)
            {
                $this->insert($array_save);
            }
            else
            {
                $this->update($array_save, 'id = '.$this->getId());
            }

            return TRUE;
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

    public function getPermissions()
    {
        $select = new Select();
        $select->from('user_acl_roles')
            ->join('user_acl_permissions', 'user_acl_permissions.user_acl_role_id = user_acl_roles.id')
            ->join('user_acl_resources', 'user_acl_resources.id = user_acl_permissions.user_acl_resource_id')
            ->where(sprintf('user_acl_roles.id = %s', $this->getId()));

        return $this->fetchAll($select);
    }
}
