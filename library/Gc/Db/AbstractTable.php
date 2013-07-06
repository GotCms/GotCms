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
 * @category   Gc
 * @package    Library
 * @subpackage Db
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Gc\Db;

use Gc\Core\Object;
use Gc\Event\StaticEventManager;
use Gc\Registry;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway;
use PDO;

/**
 * Extension of Zend\Db\TableGateway
 * This is better to use fetchRow(), fetchAll(),
 * execute() and add generic methods.
 *
 * @category   Gc
 * @package    Library
 * @subpackage Db
 */
abstract class AbstractTable extends Object
{
    /**
     * AbstractTable collection
     *
     * @var array \Zend\Db\TableGateway\TableGateway
     */
    static protected $tables = array();

    /**
     * Initialize constructor and save instance of \Zend\Db\TableGateway\TableGateway($name) in self::$tables
     *
     * @return void
     */
    public function __construct()
    {
        if (!empty($this->name) and !array_key_exists($this->name, self::$tables)) {
            self::$tables[$this->name] = new TableGateway\TableGateway(
                $this->name,
                TableGateway\Feature\GlobalAdapterFeature::getStaticAdapter()
            );
        }

        $this->init();
    }

    /**
     * Set/Get attribute wrapper
     *
     * @param string $method Method
     * @param array  $args   Args
     *
     * @return  \Zend\Db\TableGateway\TableGateway
     */
    public function __call($method, $args)
    {
        if (empty(self::$tables[$this->name])) {
            $this->__construct();
        }

        if (method_exists(self::$tables[$this->name], $method)) {
            return call_user_func_array(array(self::$tables[$this->name], $method), $args);
        }

        return parent::__call($method, $args);
    }

    /**
     * Fetch Row
     *
     * @param mixed $query      (\Zend\Db\Sql\*|string) Query
     * @param mixed $parameters Parameters
     *
     * @return array|\Zend\Db\ResultSet\RowObjectInterface
     */
    public function fetchRow($query, $parameters = null)
    {
        if ($query instanceof ResultSet) {
            $resultSet = $query;
        } else {
            $resultSet = new ResultSet();
            $resultSet->initialize($this->execute($query, $parameters));
        }

        $result = $resultSet->getDataSource()->getResource()->fetch(PDO::FETCH_ASSOC);
        $resultSet->getDataSource()->getResource()->closeCursor();

        return $result;
    }

    /**
     * Fetch Row
     *
     * @param mixed $query      (\Zend\Db\Sql\*|string) Query
     * @param mixed $parameters Parameters
     *
     * @return array
     */
    public function fetchAll($query, $parameters = null)
    {
        if ($query instanceof ResultSet) {
            $resultSet = $query;
        } else {
            $resultSet = new ResultSet();
            $resultSet->initialize($this->execute($query, $parameters));
        }

        $result = $resultSet->getDataSource()->getResource()->fetchAll(PDO::FETCH_ASSOC);
        $resultSet->getDataSource()->getResource()->closeCursor();

        return $result;
    }

    /**
     * Fetch One
     *
     * @param mixed $query      (\Zend\Db\Sql\*|string) Query
     * @param mixed $parameters Parameters
     *
     * @return mixed
     */
    public function fetchOne($query, $parameters = null)
    {
        if ($query instanceof ResultSet) {
            $resultSet = $query;
        } else {
            $resultSet = new ResultSet();
            $resultSet->initialize($this->execute($query, $parameters));
        }

        $result = $resultSet->getDataSource()->getResource()->fetchColumn();
        $resultSet->getDataSource()->getResource()->closeCursor();

        return $result;
    }

    /**
     * Execute query
     *
     * @param mixed $query      (\Zend\Db\Sql\*|string) Query
     * @param mixed $parameters Parameters
     *
     * @return array|\Zend\Db\Adapter\Driver\Pdo\Result
     */
    public function execute($query, $parameters = null)
    {
        if (is_string($query)) {
            $statement = $this->getAdapter()->createStatement($query);
        } else {
            $statement = $this->getAdapter()->createStatement();
            $query->prepareStatement($this->getAdapter(), $statement);
        }

        return $statement->execute($parameters);
    }

    /**
     * Get last insert id
     *
     * @param string $tableName Optional table name
     *
     * @return integer
     */
    public function getLastInsertId($tableName = null)
    {
        $tableName = empty($tableName) ? $this->name : $tableName;
        if ($this->getDriverName() == 'pdo_pgsql') {
            $row = $this->fetchRow(sprintf("SELECT currval('%s_id_seq') AS value", $tableName));
            return $row['value'];
        }

        return $this->getAdapter()->getDriver()->getConnection()->getLastGeneratedValue($tableName);
    }

    /**
     * Retrieve event manager
     *
     * @return \Gc\Event\StaticEventManager
     */
    public function events()
    {
        return StaticEventManager::getInstance();
    }

    /**
     * Retrieve driver name
     *
     * @return string
     */
    public function getDriverName()
    {
        $configuration = $this->getAdapter()->getDriver()->getConnection()->getConnectionParameters();
        return $configuration['driver'];
    }
}
