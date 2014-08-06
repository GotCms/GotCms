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

namespace GcConfig\Controller;

use Gc\Test\PHPUnit\Controller\AbstractRestControllerTestCase;
use Gc\Module\Model as ModuleModel;
use Gc\Version;
use Mockery;
use Zend\Json\Json;

/**
 * Test user rest api
 *
 * @group    ZfModules
 * @category Gc_Tests
 * @package  ZfModules
 */
class UpdateRestControllerTest extends AbstractRestControllerTestCase
{
    public function setUp()
    {
        $this->controller = new UpdateRestController;
        parent::setUp();

        $modules = $this->controller->getServiceLocator()->get('CustomModules');
        ModuleModel::install($modules, 'Blog');
    }

    public function tearDown()
    {
        $this->changeLatestVersion(Version::VERSION);
        $modules = $this->controller->getServiceLocator()->get('CustomModules');
        ModuleModel::uninstall($modules->getModule('Blog'), ModuleModel::fromName('Blog'));
    }

    /**
     * Test get server config
     *
     * @return void
     */
    public function testGetUpdateInfos()
    {
        $this->setUpRoute('admin/config/update');
        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertTrue($result->isLatest);
        $this->assertTrue($result->gitProject);
        $this->assertEquals(Version::VERSION, $result->latestVersion);
        $this->assertEquals(array(), $result->datatypesErrors);
        $this->assertEquals(array(), $result->modulesErrors);
    }

    /**
     * Test get server config
     *
     * @return void
     */
    public function testUpdateWithWrongAdapter()
    {
        $mock = $this->mockService();
        $this->setUpRoute('admin/config/update');
        $this->request->setMethod('POST');
        $post = $this->request->getPost();
        $post->fromArray(
            array(
                'adapter' => 'bzr'
            )
        );

        $mock->shouldReceive('load')->with('bzr')->once()->andReturn(false);
        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertEquals('Page not found', $result->content);
        $this->assertEquals('Page not found.', $result->message);
    }

    /**
     * Test get server config
     *
     * @return void
     */
    public function testUpdateWithAdapterAndFailedUpdate()
    {
        $this->changeLatestVersion('10.0.0');
        $mock = $this->mockService();
        $this->setUpRoute('admin/config/update');
        $this->request->setMethod('POST');
        $post = $this->request->getPost();
        $post->fromArray(
            array(
                'adapter' => 'git'
            )
        );

        $mock->shouldReceive('load')->with('git')->once()->andReturn(true);
        $mock->shouldReceive('update')->once()->andReturn(false);
        $mock->shouldReceive('getMessages')->once()->andReturn(array('error'));
        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertEquals(array('error'), $result->errors);
    }

    /**
     * Test get server config
     *
     * @return void
     */
    public function testUpdateWithAdapterAndFailedUpgrade()
    {
        $this->changeLatestVersion('10.0.0');
        $mock = $this->mockService();
        $this->setUpRoute('admin/config/update');
        $this->request->setMethod('POST');
        $post = $this->request->getPost();
        $post->fromArray(
            array(
                'adapter' => 'git'
            )
        );

        $mock->shouldReceive('load')->with('git')->once()->andReturn(true);
        $mock->shouldReceive('update')->once()->andReturn(true);
        $mock->shouldReceive('upgrade')->once()->andReturn(false);
        $mock->shouldReceive('getMessages')->once()->andReturn(array('error'));
        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertEquals(array('error'), $result->errors);
    }

    /**
     * Test get server config
     *
     * @return void
     */
    public function testUpdateWithAdapterAndFailedUpdateDatabase()
    {
        $this->changeLatestVersion('10.0.0');
        $mock = $this->mockService();
        $this->setUpRoute('admin/config/update');
        $this->request->setMethod('POST');
        $post = $this->request->getPost();
        $post->fromArray(
            array(
                'adapter' => 'git'
            )
        );

        $mock->shouldReceive('load')->with('git')->once()->andReturn(true);
        $mock->shouldReceive('update')->once()->andReturn(true);
        $mock->shouldReceive('upgrade')->once()->andReturn(true);
        $mock->shouldReceive('updateDatabase')->once()->andReturn(false);
        $mock->shouldReceive('rollback')->once()->andReturn(true);
        $mock->shouldReceive('getMessages')->once()->andReturn(array('error'));
        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertEquals(array('error'), $result->errors);
    }

    /**
     * Test get server config
     *
     * @return void
     */
    public function testUpdateWithAdapter()
    {
        $this->changeLatestVersion('10.0.0');
        $mock = $this->mockService();
        $this->setUpRoute('admin/config/update');
        $this->request->setMethod('POST');
        $post = $this->request->getPost();
        $post->fromArray(
            array(
                'adapter' => 'git'
            )
        );

        $mock->shouldReceive('load')->with('git')->once()->andReturn(true);
        $mock->shouldReceive('update')->once()->andReturn(true);
        $mock->shouldReceive('upgrade')->once()->andReturn(true);
        $mock->shouldReceive('updateDatabase')->once()->andReturn(true);
        $mock->shouldReceive('executeScripts')->once()->andReturn(true);
        $mock->shouldReceive('getMessages')->once()->andReturn(array('cool'));
        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertEquals(array('cool'), $result->messages);
        $this->assertEquals('Cms update to 10.0.0', $result->content);
    }

    /**
     * Retrieve mock service for Core Updater
     *
     * @return mixed
     */
    protected function mockService()
    {
        $service = Mockery::mock('Gc\Core\Updater');
        $this->controller->getServiceLocator()->setService('CoreUpdater', $service);
        return $service;
    }

    /**
     * Retrieve latest version
     *
     * @return mixed
     */
    protected function changeLatestVersion($version)
    {
        $class    = new \ReflectionClass('Gc\Version');
        $property = $class->getProperty('latestVersion');
        $property->setAccessible(true);
        $property->setValue($version);
    }
}
