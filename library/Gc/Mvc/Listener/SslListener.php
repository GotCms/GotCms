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

use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventInterface;
use Zend\Mvc\MvcEvent;
use Zend\Uri\Http as Uri;

/**
 * Ssl Listener
 *
 * @category   Gc
 * @package    Library
 * @subpackage Mvc\Listener
 */
class SslListener extends AbstractListenerAggregate
{
    /**
     * Attach events
     *
     * @param EventManagerInterface $events Event manager
     *
     * @return void
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(
            MvcEvent::EVENT_ROUTE,
            array($this, 'check'),
            -10
        );
    }

    /**
     * Check if ssl is forced or not
     *
     * @param EventInterface $event Mvc event
     *
     * @return null|Zend\Http\PhpEnvironment\Response
     */
    public function check(EventInterface $event)
    {
        $coreConfig       = $event->getApplication()->getServiceManager()->get('CoreConfig');
        $matchedRouteName = $event->getRouteMatch()->getMatchedRouteName();
        $request          = $event->getRequest();
        $uri              = $request->getUri();

        if ($matchedRouteName === 'cms') {
            if ($uri->getScheme() === 'https' or $coreConfig->getValue('force_frontend_ssl')) {
                $newUri = new Uri($coreConfig->getValue('secure_frontend_base_path'));
                $newUri->setScheme('https');
            } else {
                $newUri = new Uri($coreConfig->getValue('unsecure_frontend_base_path'));
            }
        } else {
            if ($uri->getScheme() === 'https' or $coreConfig->getValue('force_backend_ssl')) {
                $newUri = new Uri($coreConfig->getValue('secure_backend_base_path'));
                $newUri->setScheme('https');
            } else {
                $newUri = new Uri($coreConfig->getValue('unsecure_backend_base_path'));
            }
        }

        if (!empty($newUri) and $newUri->isValid() and
            ($newUri->getHost() != '' and $uri->getHost() != $newUri->getHost()) or
            ($newUri->getScheme() != '' and $uri->getScheme() != $newUri->getScheme())
        ) {
            $uri->setPort($newUri->getPort());
            if ($newUri->getHost() != '') {
                $uri->setHost($newUri->getHost());
            }

            if ($newUri->getScheme() != '') {
                $uri->setScheme($newUri->getScheme());
            }

            $response = $event->getResponse();
            $response->setStatusCode(302);
            $response->getHeaders()->addHeaderLine('Location', $request->getUri());
            $event->stopPropagation();

            return $response;
        }
    }
}
