<?php

namespace Gc\User;

use Gc\Db\AbstractTable,
    Gc\Component\IterableInterface;

class Collection extends AbstractTable implements IterableInterface
{
    protected $_users;
    protected $_sort;
    protected $_name = 'user';

    /**
    * @param unknown_type $order
    * @desc create instance
    */
    public function init($order = 'ASC')
    {
        if($order != 'DESC')
        {
            $order = 'ASC';
        }

        $this->_sort = $order;
        $this->setUsers();
    }

    /**
    * @return array of Gc\User
    */
    public function getUsers()
    {
        return $this->_users;
    }

    /**
    * @desc create an array of Gc\User\Model
    * @return Gc\User\Model
    */
    private function setUsers()
    {
        $select = $this->select()
            ->order(array('lastname '.$this->_sort));
        $rows = $this->fetchAll($select);
        $users = array();
        foreach($rows as $row)
        {
            $users[] = Gc\User\Model::fromArray((array)$row);
        }

        $this->_users = $users;
    }


    /** (non-PHPdoc)
    * @see include/Es/Interfaces/Gc\Component\IterableInterfaces#getName()
    */
    public function getName()
    {
        return 'Users';
    }

    /** (non-PHPdoc)
    * @see include/Es/Interfaces/Gc\Component\IterableInterfaces#getChildren()
    */
    public function getChildren()
    {
        return $this->getUsers();
    }
    /** (non-PHPdoc)
    * @see include/Es/Interfaces/Gc\Component\IterableInterfaces#getParent()
    */
    public function getParent()
    {
        return FALSE;
    }

    /** (non-PHPdoc)
    * @see include/Es/Interfaces/Gc\Component\IterableInterfaces#getId()
    */
    public function getId()
    {
        return FALSE;
    }

    /* (non-PHPdoc)
    * @see include/Es/Interfaces/Gc\Component\IterableInterfaces#getTreeViewId()
    */
    public function getIterableId()
    {
        return 'users';
    }

    /* (non-PHPdoc)
    * @see include/Gc/Interface/Gc\Component\IterableInterface#getUrl()
    */
    public function getUrl()
    {
        return 'javascript:loadController(\''.Zend_Controller_Action_HelperBroker::getStaticHelper('url')->url(array('controller'=>'config', 'action'=>'users')).'\')';
    }

    /* (non-PHPdoc)
    * @see include/Gc/Interface/Gc\Component\IterableInterface#getIcon()
    */
    public function getIcon()
    {
        return 'folder';
    }
}
