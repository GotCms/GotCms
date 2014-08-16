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
use Gc\Registry;
use Zend\Json\Json;

/**
 * Test user rest api
 *
 * @group    ZfModules
 * @category Gc_Tests
 * @package  ZfModules
 */
class ConfigRestControllerTest extends AbstractRestControllerTestCase
{
    public function setUp()
    {
        $this->controller = new ConfigRestController;
        parent::setUp();
    }

    /**
     * Test get server config
     *
     * @return void
     */
    public function testGetServerListConfigs()
    {
        $this->setUpRoute('config/server', array('type' => 'server'));
        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertInternalType('array', $result->configs);
    }

    /**
     * Test get general config
     *
     * @return void
     */
    public function testGetGeneralListConfigs()
    {
        $this->setUpRoute('config/general', array('type' => 'general'));
        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertInternalType('array', $result->configs);
    }

    /**
     * Test get system config
     *
     * @return void
     */
    public function testGetSystemListConfigs()
    {
        $this->setUpRoute('config/system', array('type' => 'system'));
        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertInternalType('array', $result->configs);
    }

    /**
     * Test get system config with wrong id
     *
     * @return void
     */
    public function testGetConfigWithWrongId()
    {
        $this->setUpRoute(
            'config/system',
            array(
                'type' => 'system',
                'id' => 1000
            )
        );
        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertEquals('Page not found', $result->content);
        $this->assertEquals('Page not found.', $result->message);
    }

    /**
     * Test get system config
     *
     * @return void
     */
    public function testGetConfig()
    {
        $this->setUpRoute(
            'config/system',
            array(
                'type' => 'system',
                'id' => 'session_handler'
            )
        );
        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertInternalType('array', $result->config);
        $this->assertEquals('7', $result->config['id']);
        $this->assertEquals('session_handler', $result->config['identifier']);
        $this->assertEquals('1', $result->config['value']);
    }

    /**
     * Test update config with wrong config id
     *
     * @return void
     */
    public function testUpdateConfigWithWrongConfigId()
    {
        $this->setUpRoute(
            'config/system',
            array(
                'type' => 'system',
                'id' => 1000
            )
        );
        $this->request->setMethod('PUT');

        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertEquals('Page not found', $result->content);
        $this->assertEquals('Page not found.', $result->message);
    }

    /**
     * Test update config with wrong config id
     *
     * @return void
     */
    public function testUpdateConfigWithInvalidData()
    {
        $this->setUpRoute(
            'config/system',
            array(
                'type' => 'system',
                'id' => 'cache_lifetime'
            )
        );
        $this->request->setMethod('PUT');
        $this->request->getHeaders()->addHeaderLine('Content-Type: application/json');
        $this->request->setContent(
            json_encode(
                array(
                    'cache_lifetime' => 'test',
                )
            )
        );

        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertEquals('Invalid data', $result->content);
        $this->assertEquals(
            array(
                'cache_lifetime' => array(
                    'notDigits' => 'The input must contain only digits'
                )
            ),
            $result->errors
        );
    }

    /**
     * Test update config with valid data
     *
     * @return void
     */
    public function testUpdateConfigWithValidData()
    {
        $this->setUpRoute(
            'config/system',
            array(
                'type' => 'system',
                'id' => 'cache_lifetime'
            )
        );
        $this->request->setMethod('PUT');
        $this->request->getHeaders()->addHeaderLine('Content-Type: application/json');
        $this->request->setContent(
            json_encode(
                array(
                    'cache_lifetime' => '7200',
                )
            )
        );

        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertInternalType('array', $result->configs);
        foreach ($result->configs as $config) {
            if ($config['identifier'] == 'cache_lifetime') {
                $this->assertEquals(7200, $config['value']);
                break;
            }
        }
    }
}
