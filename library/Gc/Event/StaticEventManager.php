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
 * @subpackage  Event
 * @author      Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license     GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link        http://www.got-cms.com
 */


namespace Gc\Event;

use Zend\EventManager\SharedEventManager,
    Zend\EventManager\SharedEventManagerInterface;

/**
 * Static version of EventManager
 */
class StaticEventManager extends SharedEventManager
{
    /**
     * Retrieve StaticEventManager instance
     * @var StaticEventManager
     */
    protected static $_instance;

    /**
     * Singleton
     *
     * @return void
     */
    protected function __construct()
    {
    }

    /**
     * Retrieve instance
     *
     * @return StaticEventManager
     */
    public static function getInstance()
    {
        if(static::$_instance === NULL)
        {
            static::setInstance(new static());
        }

        return static::$_instance;
    }

    /**
     * Set the singleton to a specific SharedEventManagerInterface instance
     *
     * @param SharedEventManagerInterface $instance
     * @return void
     */
    public static function setInstance(SharedEventManagerInterface $instance)
    {
        static::$_instance = $instance;
    }

    /**
     * Is a singleton instance defined?
     *
     * @return bool
     */
    public static function hasInstance()
    {
        return (static::$_instance instanceof SharedEventManagerInterface);
    }

    /**
     * Reset the singleton instance
     *
     * @return void
     */
    public static function resetInstance()
    {
        static::$_instance = NULL;
    }

    /**
     * Retrieve event
     *
     * @param  string $id
     * @return \Zend\EventManager\EventManager
     */
    public function getEvent($id)
    {
        if(!array_key_exists($id, $this->identifiers))
        {
            return FALSE;
        }

        return $this->identifiers[$id];
    }

    /**
     * Trigger all listeners for a given event
     *
     * Can emulate triggerUntil() if the last argument provided is a callback.
     *
     * @param  string|array $id Identifier(s) for event emitting component(s)
     * @param  string $event
     * @param  string|object $target Object calling emit, or symbol describing target (such as static method name)
     * @param  array|ArrayAccess $argv Array of arguments; typically, should be associative
     * @param  null|callable $callback
     * @return \Zend\EventManager\ResponseCollection All listener return values
     * @throws \Zend\EventManager\Exception\InvalidCallbackException
     */
    public function trigger($id, $event, $target = NULL, $argv = array(), $callback = NULL)
    {
        $e = $this->getEvent($id);
        if(empty($e))
        {
            return FALSE;
        }

        return $e->trigger($event, $target, $argv, $callback);
    }
}
