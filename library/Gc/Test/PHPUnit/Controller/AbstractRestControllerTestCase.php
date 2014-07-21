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
 * @subpackage Test\PHPUnit\Controller
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Gc\Test\PHPUnit\Controller;

use PHPUnit_Framework_TestCase as TestCase;
use Gc\Registry;
use Zend\Http\Response;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;

/**
 * AbstractRestControllerTestCase is the base class for the rest controller.
 *
 * @category   Gc
 * @package    Library
 * @subpackage Test\PHPUnit\Controller
 */
class AbstractRestControllerTestCase extends TestCase
{
    protected $controller;
    protected $emptyController;
    protected $request;
    protected $response;
    protected $routeMatch;
    protected $event;

    /**
     * Initialize events
     *
     * @return void
     */
    public function setUp()
    {
        $this->request  = new TestAsset\Request();
        $this->response = new Response();
        $this->event    = new MvcEvent;
        $this->controller->setEvent($this->event);
        $this->controller->setServiceLocator(Registry::get('Application')->getServiceManager());
    }


    /**
     * Set up route
     *
     * @param string $routeName Route name
     *
     * @return void
     */
    public function setUpRoute($routeName)
    {
        $this->routeMatch = new RouteMatch(array());
        $this->routeMatch->setMatchedRouteName($routeName);
        $this->event->setRouteMatch($this->routeMatch);
    }
}
