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
    Zend\Db\Sql\Select,
    Zend\Db\TableGateway\TableGateway;
/**
 * Role Model
 */
class Model extends AbstractTable
{
    /**
     * Table name
     * @var string
     */
    protected $_name = 'user_acl_role';

    /**
     * Save Role
     * @return integer
     */
    public function save()
    {
        $array_save = array(
            'name' => $this->getName(),
            'description' => $this->getDescription(),
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
     * Delete Role
     * @return boolean
     */
    public function delete()
    {
        $id = $this->getId();
        if(!empty($id))
        {
            parent::delete('id = '.$id);
            unset($this);
            return TRUE;
        }

        return FALSE;
    }

    /**
     * Initiliaze from array
     * @param array $array
     * @return \Gc\User\Model\Role
     */
    static function fromArray(array $array)
    {
        $role_table = new Model();
        $role_table->setData($array);

        return $role_table;
    }

    /**
     * Initiliaze from id
     * @param integer $id
     * @return \Gc\User\Model\Role
     */
    static function fromId($id)
    {
        $role_table = new Model();
        $row = $role_table->select(array('id' => $id));
        $current = $row->current();
        if(!empty($current))
        {
            return $role_table->setData((array)$current);
        }
        else
        {
            return FALSE;
        }
    }

    /**
     * Get User permissions
     * @return array
     */
    public function getUserPermissions()
    {
        $select = new Select();
        $select->from('user_acl_role')
            ->join('user_acl', 'user_acl.user_acl_role_id = user_acl_role.id', array())
            ->join('user_acl_permission', 'user_acl_permission.id = user_acl.user_acl_permission_id', array('userPermissionId' => 'id', 'permission'))
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
