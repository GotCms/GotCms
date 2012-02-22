<?php

/**
 * AbstractTable
 *
 * @category       Es
 * @package        AbstractTable
 * @author         RAMBAUD Pierre
 */
namespace Es\Db;

use Es\Exception,
    Es\Core\Object,
    Zend\Db\Table\Table;

abstract class AbstractTable extends Object
{
    /**
    * Zend_Db_Table collection
    *
    * @var Zend\Db\Table\AbstractTable
    */
    static $_tables = array();

    /**
    * Initialize constructor and save instance of Zend\Db\Table($_name) in
    * self::$_tables
    *
    */
    public function __construct()
    {
        if(!empty($this->_name) and !in_array($this->_name, self::$_tables))
        {
            self::$_tables[$this->_name] = new Table($this->_name);
        }

        $this->init();
    }

    /**
    * Set/Get attribute wrapper
    *
    * @param   string $method
    * @param   array $args
    * @return  Zend_Db_Table
    */
    public function __call($method, $args)
    {
        if(method_exists(self::$_tables[$this->_name], $method))
        {
            return call_user_func_array(array(self::$_tables[$this->_name], $method), $args);
        }

        return parent::__call($method, $args);
    }
}
