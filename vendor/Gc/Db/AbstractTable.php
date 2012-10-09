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
 * @subpackage  Db
 * @author      Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license     GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link        http://www.got-cms.com
 */

namespace Gc\Db;

use Gc\Exception,
    Gc\Core\Object,
    Gc\Registry,
    Zend\Db\ResultSet\ResultSet,
    Zend\Db\TableGateway;
/**
 * Extension of Zend\Db\TableGateway
 * This is better to use fetchRow(), fetchAll(),
 * execute() and add generic methods.
 */
abstract class AbstractTable extends Object
{
    /**
     * Zend_Db_Table collection
     *
     * @var array of \Zend\Db\TableGateway\TableGateway
     */
    static $_tables = array();

    /**
     * Initialize constructor and save instance of \Zend\Db\TableGateway\TableGateway($_name) in
     * self::$_tables
     * @return void
     */
    public function __construct()
    {
        if(!empty($this->_name) and !in_array($this->_name, self::$_tables))
        {
            self::$_tables[$this->_name] = new TableGateway\TableGateway($this->_name, TableGateway\Feature\GlobalAdapterFeature::getStaticAdapter());
        }

        $this->init();
    }

    /**
     * Set/Get attribute wrapper
     *
     * @param   string $method
     * @param   array $args
     * @return  \Zend\Db\TableGateway\TableGateway
     */
    public function __call($method, $args)
    {
        if(empty(self::$_tables[$this->_name]))
        {
            $this->__construct();
        }

        if(method_exists(self::$_tables[$this->_name], $method))
        {
            return call_user_func_array(array(self::$_tables[$this->_name], $method), $args);
        }

        return parent::__call($method, $args);
    }

    /**
     * Fetch Row
     * @param mixed $query (\Zend\Db\Sql\Select|string)
     * @return array|Zend\Db\ResultSet\RowObjectInterface
     */
    public function fetchRow($query)
    {
        if($query instanceof ResultSet)
        {
            return $query->current();
        }

        $result_set = new ResultSet();
        $result_set->initialize($this->execute($query));

        return $result_set->current();
    }

    /**
     * Fetch Row
     * @param mixed $query (\Zend\Db\Sql\Select|string)
     * @return array
     */
    public function fetchAll($query)
    {
        if($query instanceof ResultSet)
        {
            return $query->toArray();
        }

        $result_set = new ResultSet();
        $result_set->initialize($this->execute($query));

        return $result_set->toArray();
    }

    /**
     * Fetch One
     * @param mixed $query (\Zend\Db\Sql\Select|string)
     * @return mixte
     */
    public function fetchOne($query)
    {
        if($query instanceof ResultSet)
        {
            $row = $query->current();
        }
        else
        {
            $row = $this->fetchRow($query);
        }

        if(!empty($row))
        {
            $array = $row->getArrayCopy();
            return array_shift($array);
        }

        return FALSE;
    }

    /**
     * Execute query
     * @param mixed $query (\Zend\Db\Sql\*|string)
     * @return array|Zend\Db\ResultSet\ResultSet
     */
    public function execute($query)
    {
        if(is_string($query))
        {
            $statement = $this->getAdapter()->createStatement($query);
        }
        else
        {
            $statement = $this->getAdapter()->createStatement();
            $query->prepareStatement($this->getAdapter(), $statement);
        }

        return $statement->execute();
    }

    /**
     * Get last insert id
     * @param string optional table name
     * @return integer
     */
    public function getLastInsertId($table_name = NULL)
    {
        $table_name = empty($table_name) ? $this->_name : $table_name;
        $configuration = Registry::get('Configuration');
        if($configuration['db']['driver'] == 'pdo_pgsql')
        {
            $row = $this->fetchRow(sprintf("SELECT currval('%s_id_seq') AS value", $table_name));
            return $row['value'];
        }

        return $this->getAdapter()->getDriver()->getConnection()->getLastGeneratedValue($table_name);
    }
}
