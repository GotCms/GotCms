<?php

namespace Gc\User\Permission;

use Gc\Db\AbstractTable,
    Zend\Db\Sql\Select;

class Collection extends AbstractTable
{
    protected $_permissions;
    protected $_name = 'user_acl_permissions';

    /**
    * @param unknown_type $order
    * @desc create instance
    */
    public function init()
    {
        $this->getPermissions();
    }

    /**
    * @return array of Gc\User
    */
    public function getPermissions()
    {
        if(empty($this->_permissions))
        {
            $select = new Select();
            $select->from('user_acl_permission')
                ->columns(array(
                    'id'
                    , 'permission'
                ), TRUE)
                ->join('user_acl_resource', 'user_acl_resource.id = user_acl_permission.user_acl_resource_id', array('resource'));

            $rows = $this->fetchAll($select);
            $permissions = array();
            foreach($rows as $permission)
            {
                if(empty($permissions[$permission['resource']]))
                {
                    $permissions[$permission['resource']] = array();
                }

                $permissions[$permission['resource']][$permission['id']] = $permission['permission'];
            }

            $this->_permissions = $permissions;
        }

        return $this->_permissions;
    }
}
