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
 * @subpackage Mvc\Service
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Gc\Mvc\Service;

use Zend\Mvc\MvcEvent;
use Gc\Event\CacheEvent;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\Cache\Storage\StorageInterface;
use Zend\Http\PhpEnvironment\Request as HttpRequest;

/**
 * Cache service
 *
 * @category   Gc
 * @package    Library
 * @subpackage Mvc\Service
 */
class CacheService implements EventManagerAwareInterface
{
    /**
     * Static event manager
     *
     * @var EventManagerInterface
     */
    protected $eventManager;

    /**
     * Cache storage adapter
     *
     * @var StorageInterface
     */
    private $cacheStorage;

    /**
     * Constructor
     *
     * @param \Zend\Cache\Storage\StorageInterface $cacheStorage Cache storage
     *
     * @return void
     */
    public function __construct(StorageInterface $cacheStorage)
    {
        $this->cacheStorage = $cacheStorage;
    }

    /**
     * Check if a page is saved in the cache and return contents.
     * Return null when no item is found.
     *
     * @param MvcEvent $e Mvc Event
     *
     * @return mixed
     */
    public function load(MvcEvent $e)
    {
        $id = $this->createId($e->getRequest());
        if (!$this->cacheStorage->hasItem($id)) {
            return null;
        };

        $event = new CacheEvent(CacheEvent::EVENT_LOAD, $this);
        $event->setCacheKey($id);
        $this->getEventManager()->trigger($event);
        if ($event->getAbort()) {
            return null;
        }

        return $this->cacheStorage->getItem($id);
    }

    /**
     * Save the page contents to the cache storage.
     *
     * @param MvcEvent $e Mvc Event
     *
     * @return void
     */
    public function save(MvcEvent $e)
    {
        if (!$this->shouldCacheRequest($e)) {
            return;
        }

        $id   = $this->createId($e->getRequest());
        $item = serialize($e->getResponse());
        $this->cacheStorage->setItem($id, $item);
        $this->getEventManager()->trigger(new CacheEvent(CacheEvent::EVENT_SAVE, $this));
    }

    /**
     * Determine if we should cache the current request
     *
     * @param MvcEvent $e Mvc Event
     *
     * @return bool
     */
    protected function shouldCacheRequest(MvcEvent $e)
    {
        // Early break if page should not be cached
        $event = new CacheEvent(CacheEvent::EVENT_SHOULDCACHE, $this);
        $this->getEventManager()->trigger($event);
        if ($event->getAbort()) {
            return false;
        }

        if ($this->shouldCache($e)) {
            return true;
        }

        return false;
    }

    /**
     * Determine the page to save from the request
     *
     * @param HttpRequest $request Http Request
     *
     * @throws \RuntimeException
     * @return string
     */
    protected function createId(HttpRequest $request)
    {
        return md5($request->getRequestUri());
    }

    /**
     * Inject an EventManager instance
     *
     * @param EventManagerInterface $eventManager Event Manager
     *
     * @return self
     */
    public function setEventManager(EventManagerInterface $eventManager)
    {
        $eventManager->setIdentifiers(
            array(
                __CLASS__,
                get_called_class()
            )
        );

        $this->eventManager = $eventManager;
        return $this;
    }

    /**
     * Retrieve the event manager
     *
     * Lazy-loads an EventManager instance if none registered.
     *
     * @return EventManagerInterface
     */
    public function getEventManager()
    {
        if (!$this->eventManager instanceof EventManagerInterface) {
            $this->setEventManager(new EventManager());
        }

        return $this->eventManager;
    }
    /**
     * Define if the page should be cached
     *
     * @param MvcEvent $event Event
     *
     * @return boolean
     */
    protected function shouldCache(MvcEvent $event)
    {
        $routeMatch = $event->getRouteMatch();
        if ($routeMatch === null) {
            return false;
        }

        $routeName = $event->getRouteMatch()->getMatchedRouteName();
        $response  = $event->getResponse();
        $request   = $event->getRequest();
        if ($routeName != 'cms' or $request->getMethod() != 'GET' or $response->getStatusCode() != 200) {
            return false;
        }

        $application    = $event->getApplication();
        $serviceManager = $application->getServiceManager();
        if ($serviceManager->has('CurrentDocument')
            and $serviceManager->get('CurrentDocument')->canBeCached() === false
        ) {
            return false;
        }

        return true;
    }
}
