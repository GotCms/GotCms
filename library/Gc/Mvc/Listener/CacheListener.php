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
 * @subpackage Mvc\Listener
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Gc\Mvc\Listener;

use Zend\Http\PhpEnvironment\Request as HttpRequest;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\Mvc\MvcEvent;

/**
 * Document Listener
 *
 * @category   Gc
 * @package    Library
 * @subpackage Mvc\Listener
 */

class CacheListener extends AbstractListenerAggregate
{
    /**
     * Define if it's load from cache
     *
     * @var bool
     */
    protected $loadedFromCache = false;

    /**
     * Attach events
     *
     * @param EventManagerInterface $events Event manager
     *
     * @return void
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_ROUTE, array($this, 'onRoute'), -20);
        $this->listeners[] = $events->attach(MvcEvent::EVENT_FINISH, array($this, 'onFinish'), -100);
    }

    /**
     * Load the page contents from the cache and set the response.
     *
     * @param MvcEvent $e Mvc Event
     *
     * @return \Zend\Stdlib\ResponseInterface|void
     */
    public function onRoute(MvcEvent $e)
    {
        if ($this->cacheIsActive($e) === false) {
            return;
        }

        if (!$e->getRequest() instanceof HttpRequest) {
            return;
        }

        $serviceManager = $e->getApplication()->getServiceManager();
        $data           = $serviceManager->get('CacheService')->load($e);
        if ($data !== null) {
            $this->loadedFromCache = true;
            $response              = unserialize($data);

            return $response;
        }
    }

    /**
     * Save page contents to the cache
     *
     * @param MvcEvent $e Mvc Event
     *
     * @return void
     */
    public function onFinish(MvcEvent $e)
    {
        if ($this->cacheIsActive($e) === false) {
            return;
        }

        if (!$e->getRequest() instanceof HttpRequest || $this->loadedFromCache) {
            return;
        }

        $serviceManager = $e->getApplication()->getServiceManager();
        $serviceManager->get('CacheService')->save($e);
    }


    /**
     * Check if cache is active
     *
     * @param MvcEvent $e Mvc Event
     *
     * @return boolean
     */
    public function cacheIsActive(MvcEvent $e)
    {
        $serviceManager = $e->getApplication()->getServiceManager();
        $coreConfig     = $serviceManager->get('CoreConfig');
        if (!$coreConfig->getValue('cache_is_active')) {
            return false;
        }

        $isAdmin   = $serviceManager->get('Auth')->hasIdentity();
        $isPreview = ($isAdmin and $e->getRequest()->getQuery()->get('preview') === 'true');
        if ($isAdmin and $isPreview) {
            return false;
        }

        return true;
    }
}
