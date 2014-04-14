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
 * @subpackage Event
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */


namespace Gc\Event;

use Zend\EventManager\SharedEventManager;
use Zend\EventManager\SharedEventManagerInterface;

/**
 * Static version of EventManager
 *
 * @category   Gc
 * @package    Library
 * @subpackage Event
 */
class StaticEventManager extends SharedEventManager
{
    /**
     * Retrieve StaticEventManager instance
     *
     * @var StaticEventManager
     */
    protected static $instance;

    /**
     * Retrieve instance
     *
     * @return StaticEventManager
     */
    public static function getInstance()
    {
        if (static::$instance === null) {
            static::setInstance(new static());
        }

        return static::$instance;
    }

    /**
     * Set the singleton to a specific SharedEventManagerInterface instance
     *
     * @param SharedEventManagerInterface $instance Event instance
     *
     * @return void
     */
    public static function setInstance(SharedEventManagerInterface $instance)
    {
        static::$instance = $instance;
    }

    /**
     * Is a singleton instance defined?
     *
     * @return bool
     */
    public static function hasInstance()
    {
        return (static::$instance instanceof SharedEventManagerInterface);
    }

    /**
     * Reset the singleton instance
     *
     * @return void
     */
    public static function resetInstance()
    {
        static::$instance = null;
    }

    /**
     * Retrieve event
     *
     * @param string $id Id
     *
     * @return \Zend\EventManager\EventManager
     */
    public function getEvent($id)
    {
        if (!array_key_exists($id, $this->identifiers)) {
            return false;
        }

        return $this->identifiers[$id];
    }

    /**
     * Trigger all listeners for a given event
     *
     * Can emulate triggerUntil() if the last argument provided is a callback.
     *
     * @param string            $id       Identifier(s) for event emitting component(s)
     * @param string            $event    Event
     * @param string|object     $target   Object calling emit, or symbol describing target (such as static method name)
     * @param array|ArrayAccess $argv     Array of arguments; typically, should be associative
     * @param null|callable     $callback Callback function
     *
     * @return \Zend\EventManager\ResponseCollection All listener return values
     * @throws \Zend\EventManager\Exception\InvalidCallbackException
     */
    public function trigger($id, $event, $target = null, $argv = array(), $callback = null)
    {
        $e = $this->getEvent($id);
        if (empty($e)) {
            return false;
        }

        return $e->trigger($event, $target, $argv, $callback);
    }
}
