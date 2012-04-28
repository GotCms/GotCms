<?php

namespace Gc\User\Resource;

use Gc\Db\AbstractTable;

class Collection extends AbstractTable
{
    protected $_resources;
    protected $_name = 'user_acl_Resources';

    /**
    * @param unknown_type $order
    * @desc create instance
    */
    public function init()
    {
        $this->getResources();
    }

    /**
    * @return array of Gc\User
    */
    public function getResources()
    {
        if(empty($this->_resources))
        {
            $rows = $this->select();
            $resources = array();
            foreach($rows as $row)
            {
                $resources[] = Model::fromArray((array)$row);
            }

            $this->_resources = $resources;
        }

        return $this->_resources;
    }
}
