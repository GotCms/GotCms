<?php

namespace Es\User;

use Es\Db\AbstractTable,
    Es\Component\IterableInterface;

class Collection extends AbstractTable implements IterableInterface
{
    protected $_users;
    protected $_sort;
    protected $_name = 'users';

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
    * @return array of Gc_User
    */
    public function getUsers()
    {
        return $this->_users;
    }

    /**
    * @desc create an array of Gc_Model_DbTable_User_Model
    * @return Gc_Model_DbTable_User_Model
    */
    private function setUsers()
    {
        $select = $this->select()
            ->order(array('lastname '.$this->_sort));
        $rows = $this->fetchAll($select);
        $users = array();
        foreach($rows as $row)
        {
            $users[] = Gc_Model_DbTable_User_Model::fromArray((array)$row);
        }

        $this->_users = $users;
    }


    /** (non-PHPdoc)
    * @see include/Es/Interfaces/Gc_Interfaces_Iterable#getName()
    */
    public function getName()
    {
        return 'Users';
    }

    /** (non-PHPdoc)
    * @see include/Es/Interfaces/Gc_Interfaces_Iterable#getChildren()
    */
    public function getChildren()
    {
        return $this->getUsers();
    }
    /** (non-PHPdoc)
    * @see include/Es/Interfaces/Gc_Interfaces_Iterable#getParent()
    */
    public function getParent()
    {
        return FALSE;
    }

    /** (non-PHPdoc)
    * @see include/Es/Interfaces/Gc_Interfaces_Iterable#getId()
    */
    public function getId()
    {
        return FALSE;
    }

    /* (non-PHPdoc)
    * @see include/Es/Interfaces/Gc_Interfaces_Iterable#getTreeViewId()
    */
    public function getIterableId()
    {
        return 'users';
    }

    /* (non-PHPdoc)
    * @see include/Es/Interface/Gc_Interface_Iterable#getUrl()
    */
    public function getUrl()
    {
        return 'javascript:loadController(\''.Zend_Controller_Action_HelperBroker::getStaticHelper('url')->url(array('controller'=>'config', 'action'=>'users')).'\')';
    }

    /* (non-PHPdoc)
    * @see include/Es/Interface/Gc_Interface_Iterable#getIcon()
    */
    public function getIcon()
    {
        return 'folder';
    }
}
