<?php
/**
 * This source file is part of Got CMS.
 *
 * Got CMS is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Got CMS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License along
 * with Got CMS. If not, see <http://www.gnu.org/licenses/lgpl-3.0.html>.
 *
 * PHP Version >=5.3
 *
 * @category    Gc
 * @package     Library
 * @subpackage  Core
 * @author      Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license     GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link        http://www.got-cms.com
 */

namespace Gc\Core;

use Gc\Db\AbstractTable,
    Zend\Db\Sql\Where;

class Config extends AbstractTable
{
    /**
     * @var string
     */
    protected $_name = 'core_config_data';

    /**
     * @var \Gc\Core\Config $_instance
     */
    static protected $_instance = NULL;

    /**
     * get instance of \Gc\Core\Config
     * @return \Gc\Core\Config
     */
    public static function getInstance()
    {
        if(empty(static::$_instance))
        {
            static::$_instance = new self();
        }

        return static::$_instance;
    }

    /**
     * Get config value
     * @param string $data
     * @param optional $field, database field, by default 'identifier'
     * @return string value
     */
    static function getValue($data, $field = 'identifier')
    {
        $instance = self::getInstance();
        $row = $instance->select(array($field => $data));
        $current = $row->current();
        if(!empty($current))
        {
            return $current['value'];
        }

        return NULL;
    }

    /**
     * Return all values from core_config_data
     * @return array
     */
    static function getValues()
    {
        $instance = self::getInstance();
        $rows = $instance->select();
        if(!empty($rows))
        {
            return $rows->toArray();
        }

        return NULL;
    }

    /**
     * Set config value
     * @param string $identifier
     * @param string $value
     * @return boolean
     */
    static function setValue($identifier, $value)
    {
        if(empty($identifier))
        {
            return FALSE;
        }

        $instance = self::getInstance();
        $row = $instance->select(array('identifier' => $identifier));
        if(!empty($row))
        {
            $where = new Where();
            return $instance->update(array('value' => $value), $where->equalTo('identifier', $identifier));
        }

        return FALSE;
    }
}
