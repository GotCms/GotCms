<?php

namespace Gc\User\Role;

use Gc\Db\AbstractTable;

class Collection extends AbstractTable
{
    protected $_roles;
    protected $_name = 'user_acl_roles';

    /**
    * @param unknown_type $order
    * @desc create instance
    */
    public function init()
    {
        $this->getRoles();
    }

    /**
    * @return array of Gc\User
    */
    public function getRoles()
    {
        if(empty($this->_roles))
        {
            $rows = $this->select();
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
