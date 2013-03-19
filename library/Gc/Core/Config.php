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
 * @subpackage Core
 * @author     Pierre Rambaud (GoT) http://rambaudpierre.fr
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Gc\Core;

use Gc\Db\AbstractTable;
use Zend\Db\Sql\Where;

/**
 * Get and set config data
 *
 * @category   Gc
 * @package    Library
 * @subpackage Core
 */
class Config extends AbstractTable
{
    /**
     * @const integer defined session from files
     */
    const SESSION_FILES = 0;

    /**
     * @const integer defined session from database
     */
    const SESSION_DATABASE = 1;

    /**
     * Table name
     *
     * @var string
     */
    protected $name = 'core_config_data';

    /**
     * Singleton for Config
     *
     * @var \Gc\Core\Config $instance
     */
    static protected $instance = null;

    /**
     * Get instance of \Gc\Core\Config
     *
     * @return \Gc\Core\Config
     */
    public static function getInstance()
    {
        if (empty(static::$instance)) {
            static::$instance = new self();
        }

        return static::$instance;
    }

    /**
     * Get config value
     *
     * @param string $data  Data
     * @param string $field Optional database field, by default 'identifier'
     *
     * @return string value
     */
    public static function getValue($data, $field = 'identifier')
    {
        $instance = self::getInstance();
        $row      = $instance->fetchRow($instance->select(array($field => $data)));
        if (!empty($row)) {
            return $row['value'];
        }

        return null;
    }

    /**
     * Return all values from core_config_data
     *
     * @return array
     */
    public static function getValues()
    {
        $instance = self::getInstance();
        $rows     = $instance->fetchAll($instance->select());
        if (!empty($rows)) {
            return $rows;
        }

        return null;
    }

    /**
     * Set config value
     *
     * @param string $identifier Identifier
     * @param string $value      Value
     *
     * @return boolean
     */
    public static function setValue($identifier, $value)
    {
        if (empty($identifier)) {
            return false;
        }

        $instance = self::getInstance();
        $row      = $instance->fetchRow($instance->select(array('identifier' => $identifier)));
        if (!empty($row)) {
            $where = new Where();
            return $instance->update(array('value' => $value), $where->equalTo('identifier', $identifier));
        }

        return false;
    }
}
