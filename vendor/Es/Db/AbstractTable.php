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
    Zend\Db\ResultSet\ResultSet,
    Zend\Db\TableGateway;

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
            self::$_tables[$this->_name] = new TableGateway\TableGateway($this->_name, TableGateway\StaticAdapterTableGateway::getStaticAdapter());
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

    public function fetchRow($query)
    {
        if($query instanceof ResultSet)
        {
            return $query->current();
        }

        return $this->_executeQuery($query)->current();
    }

    public function fetchAll($query)
    {
        if($query instanceof ResultSet)
        {
            return $query->toArray();
        }

        return $this->_executeQuery($query)->toArray();
    }

    protected function _executeQuery($query)
    {
        // create a statement to use
        $statment = $this->getAdapter()->createStatement();

        // prepare statement with $select
        $query->prepareStatement($this->getAdapter(), $statment);

        // instantiate a result set for result-as-object iteration
        $result_set = new ResultSet();
        $result_set->setDataSource($statment->execute());

        return $result_set;
    }
}
