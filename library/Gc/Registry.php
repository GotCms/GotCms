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
 * @category Gc
 * @package  Library
 * @author   Pierre Rambaud (GoT) http://rambaudpierre.fr
 * @license  GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link     http://www.got-cms.com
 */

namespace Gc;

use ArrayObject;
use RuntimeException;

/**
 * Generic storage class helps to manage global data.
 *
 * @category Gc
 * @package  Library
 */
class Registry extends ArrayObject
{
    /**
     * Registry object provides storage for shared objects.
     *
     * @var \Gc\Registry
     */
    private static $registry = null;

    /**
     * Retrieves the default registry instance.
     *
     * @return \Gc\Registry
     */
    public static function getInstance()
    {
        if (self::$registry === null) {
            self::init();
        }

        return self::$registry;
    }

    /**
     * Set the default registry instance to a specified instance.
     *
     * @param Registry $registry An object instance of type Registry,
     *                           or a subclass.
     *
     * @return void
     * @throws RuntimeException if registry is already initialized.
     */
    public static function setInstance(Registry $registry)
    {
        if (self::$registry !== null) {
            throw new RuntimeException('Registry is already initialized');
        }

        self::$registry = $registry;
    }

    /**
     * Initialize the default registry instance.
     *
     * @return void
     */
    protected static function init()
    {
        self::setInstance(new self());
    }

    /**
     * Unset the default registry instance.
     * Primarily used in tearDown() in unit tests.
     *
     * @return void
     */
    public static function unsetInstance()
    {
        self::$registry = null;
    }

    /**
     * getter method, basically same as offsetGet().
     *
     * This method can be called from an object of type Zendregistry, or it
     * can be called statically.  In the latter case, it uses the default
     * static instance stored in the class.
     *
     * @param string $index - get the value associated with $index
     *
     * @return mixed
     * @throws RuntimeException if no entry is registerd for $index.
     */
    public static function get($index)
    {
        $instance = self::getInstance();

        if (!$instance->offsetExists($index)) {
            throw new RuntimeException("No entry is registered for key '$index'");
        }

        return $instance->offsetGet($index);
    }

    /**
     * setter method, basically same as offsetSet().
     *
     * This method can be called from an object of type Zendregistry, or it
     * can be called statically.  In the latter case, it uses the default
     * static instance stored in the class.
     *
     * @param string $index The location in the ArrayObject in which to store
     *                      the value.
     * @param mixed  $value The object to store in the ArrayObject.
     *
     * @return void
     */
    public static function set($index, $value)
    {
        $instance = self::getInstance();
        $instance->offsetSet($index, $value);
    }

    /**
     * Returns true if the $index is a named value in the registry,
     * or false if $index was not found in the registry.
     *
     * @param string $index Index
     *
     * @return boolean
     */
    public static function isRegistered($index)
    {
        if (self::$registry === null) {
            return false;
        }

        return self::$registry->offsetExists($index);
    }

    /**
     * Constructs a parent ArrayObject with default
     * ARRAY_AS_PROPS to allow acces as an object
     *
     * @param array   $array data array
     * @param integer $flags ArrayObject flags
     *
     * @return void
     */
    public function __construct($array = array(), $flags = parent::ARRAY_AS_PROPS)
    {
        parent::__construct($array, $flags);
    }

    /**
     * Returns true if the $index is a named value in the registry,
     * or false if $index was not found in the registry.
     *
     * @param string $index Index
     *
     * @return boolean
     */
    public function offsetExists($index)
    {
        return array_key_exists($index, $this);
    }
}
