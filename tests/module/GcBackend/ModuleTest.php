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
 * @category Gc_Tests
 * @package  ZfModules
 * @author   Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license  GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link     http://www.got-cms.com
 */

namespace GcBackend;

use Gc\Registry;
use Gc\Core\Config as CoreConfig;
use Gc\Layout\Model as LayoutModel;
use Zend\Db\TableGateway\Feature\GlobalAdapterFeature;
use Zend\Mvc\Router\RouteMatch;
use Zend\Mvc\MvcEvent;

/**
 * Test Gc Backend Module
 *
 * @group    ZfModules
 * @category Gc_Tests
 * @package  ZfModules
 */
class ModuleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Install
     */
    protected $object;

    /**
     * @var CoreConfig
     */
    protected $config;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->object = new Module;
        $this->config = Registry::get('Application')->getServiceManager()->get('CoreConfig');
        $this->config->setValue('locale', 'en_GB');
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    protected function tearDown()
    {
        unset($this->object);
    }

    /**
     * Test
     *
     * @return void
     */
    public function testOnBootstrap()
    {
        $oldAdapter = GlobalAdapterFeature::getStaticAdapter();
        $this->config->setValue('debug_is_active', 1);
        $this->config->setValue('session_lifetime', 3600);
        $this->config->setValue('cookie_domain', 'got-cms.com');
        $this->config->setValue('session_handler', CoreConfig::SESSION_DATABASE);

        $this->assertNull($this->object->onBootstrap(Registry::get('Application')->getMvcEvent()));

        GlobalAdapterFeature::setStaticAdapter($oldAdapter);
    }

    /**
     * Test on render error without error
     *
     * @return void
     */
    public function testOnRenderErrorWithoutError()
    {
        $this->assertNull($this->object->onRenderError(Registry::get('Application')->getMvcEvent()));
    }

    /**
     * Test on dispatch error without error
     *
     * @return void
     */
    public function testOnDispatchErrorWithoutError()
    {
        $this->assertNull($this->object->onDispatchError(Registry::get('Application')->getMvcEvent()));
    }

    /**
     * Test on dispatch error without exception
     *
     * @return void
     */
    public function testOnDispatchErrorWithoutException()
    {
        $mvcEvent = new MvcEvent();
        $mvcEvent->setParam('error', 'Error spotted');

        $result = $this->object->onDispatchError($mvcEvent);
        $this->assertInstanceOf('Zend\View\Model\JsonModel', $result);
        $this->assertEquals(
            array(
                array(
                    'message' => 'An error occurred during execution; please try again later.',
                    'error' => 'Error spotted',
                    'exception' => array()
                ),
            ),
            $result->errors
        );
    }

    /**
     * Test on dispatch error with exception
     *
     * @return void
     */
    public function testOnDispatchErrorWithException()
    {
        $mvcEvent = new MvcEvent();
        $exception = new \Exception('Exception spotted');
        $mvcEvent->setParam('exception', $exception);
        $mvcEvent->setParam('error', 'Error spotted');

        $result = $this->object->onDispatchError($mvcEvent);
        $this->assertInstanceOf('Zend\View\Model\JsonModel', $result);
        $this->assertEquals(
            array(
                array(
                    'message' => 'An error occurred during execution; please try again later.',
                    'error' => 'Error spotted',
                    'exception' => array(
                        'class' => get_class($exception),
                        'file' => $exception->getFile(),
                        'line' => $exception->getLine(),
                        'message' => $exception->getMessage(),
                        'stacktrace' => $exception->getTraceAsString()
                    )
                ),
            ),
            $result->errors
        );
    }

    /**
     * Test on dispatch error with route not found
     *
     * @return void
     */
    public function testOnDispatchErrorWithRouteNotFound()
    {
        $mvcEvent = new MvcEvent();
        $mvcEvent->setParam('error', 'error-router-no-match');

        $result = $this->object->onDispatchError($mvcEvent);
        $this->assertInstanceOf('Zend\View\Model\JsonModel', $result);
        $this->assertEquals(
            array(
                array(
                    'message' => 'Resource not found.',
                    'error' => 'error-router-no-match',
                    'exception' => array()
                ),
            ),
            $result->errors
        );
    }
}
