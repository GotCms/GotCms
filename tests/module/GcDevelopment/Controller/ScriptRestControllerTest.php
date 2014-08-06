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

namespace GcDevelopment\Controller;

use Gc\Test\PHPUnit\Controller\AbstractRestControllerTestCase;
use Gc\Script;
use Gc\Registry;

/**
 * Test script rest api
 *
 * @group    ZfModules
 * @category Gc_Tests
 * @package  ZfModules
 */
class ScriptRestControllerTest extends AbstractRestControllerTestCase
{
    public function setUp()
    {
        $this->controller = new ScriptRestController;
        parent::setUp();
    }

    public function tearDown()
    {
        $collection = new Script\Collection;
        foreach ($collection->getScripts() as $script) {
            $script->delete();
        }
    }

    /**
     * Test get scripts
     *
     * @return void
     */
    public function testGetListWithoutScripts()
    {
        $this->setUpRoute('admin/development/script');
        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertEquals(array(), $result->scripts);
    }

    /**
     * Test get scripts
     *
     * @return void
     */
    public function testGetListWithScripts()
    {
        $script = Script\Model::fromArray(
            array(
                'name' => 'Test',
                'identifier' => 'test',
                'content' => 'This is content'
            )
        );
        $script->save();

        $script = Script\Model::fromId($script->getId());

        $this->setUpRoute('admin/development/script');
        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertEquals(array($script->toArray()), $result->scripts);
    }

    /**
     * Test get with wrong id
     *
     * @return void
     */
    public function testGetWithWrongId()
    {
        $this->setUpRoute('admin/development/script');
        $this->routeMatch->setParam('id', 1);

        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertEquals('Page not found', $result->content);
        $this->assertEquals('Page not found.', $result->message);
    }

    /**
     * Test get with good id
     *
     * @return void
     */
    public function testGetWithScript()
    {
        $script = Script\Model::fromArray(
            array(
                'name' => 'Test',
                'identifier' => 'test',
                'content' => 'This is content'
            )
        );
        $script->save();

        $script = Script\Model::fromId($script->getId());

        $this->setUpRoute('admin/development/script');
        $this->routeMatch->setParam('id', $script->getId());

        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertEquals($script->toArray(), $result->script);
    }

    /**
     * Test create script with invalid data
     *
     * @return void
     */
    public function testCreateWithScriptWithInvalidData()
    {
        $this->setUpRoute('admin/development/script');
        $this->request->setMethod('POST');
        $post = $this->request->getPost();
        $post->fromArray(
            array(
                'identifier' => '',
            )
        );

        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertEquals('Invalid data', $result->content);
        $this->assertEquals(
            array(
                'name' => array(
                    'isEmpty' => "Value is required and can't be empty",
                ),
                'identifier' =>  array(
                    'isEmpty' => "Value is required and can't be empty",
                    'regexNotMatch' => "The input does not match against pattern '~^[a-zA-Z0-9._-]+$~'",
                )
            ),
            $result->errors
        );
    }

    /**
     * Test create script with valid data
     *
     * @return void
     */
    public function testCreateWithScriptWithValidData()
    {
        $this->setUpRoute('admin/development/script');
        $this->request->setMethod('POST');
        $post = $this->request->getPost();
        $post->fromArray(
            array(
                'name' => 'Test name',
                'identifier' => 'test-identifier',
            )
        );

        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertEquals('test-identifier', $result->identifier);
        $this->assertEquals('Test name', $result->name);
        $this->assertNull($result->description);
        $this->assertNotNull($result->created_at);
        $this->assertNotNull($result->updated_at);
    }

    /**
     * Test delete script with wrong id
     *
     * @return void
     */
    public function testDeleteScriptWithWrongId()
    {
        $this->setUpRoute('admin/development/script');
        $this->request->setMethod('DELETE');
        $this->routeMatch->setParam('id', 1);

        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertEquals('Page not found', $result->content);
        $this->assertEquals('Page not found.', $result->message);
    }

    /**
     * Test update script with wrong id
     *
     * @return void
     */
    public function testUpdateScriptWithWrongId()
    {
        $this->setUpRoute('admin/development/script');
        $this->request->setMethod('PUT');
        $this->routeMatch->setParam('id', 1);

        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertEquals('Page not found', $result->content);
        $this->assertEquals('Page not found.', $result->message);
    }

    /**
     * Test delete script
     *
     * @return void
     */
    public function testDeleteScript()
    {
        $script = Script\Model::fromArray(
            array(
                'name' => 'Test',
                'identifier' => 'test',
                'content' => 'This is content'
            )
        );
        $script->save();

        $this->setUpRoute('admin/development/script');
        $this->request->setMethod('DELETE');
        $this->routeMatch->setParam('id', $script->getId());

        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertTrue($result->success);
        $this->assertEquals('This script has been deleted.', $result->content);
    }

    /**
     * Test update script with invalid data
     *
     * @return void
     */
    public function testUpdateWithScriptWithInvalidData()
    {
        $script = Script\Model::fromArray(
            array(
                'name' => 'Test',
                'identifier' => 'test',
                'content' => 'This is content'
            )
        );
        $script->save();

        $this->setUpRoute('admin/development/script');
        $this->request->setMethod('PUT');
        $this->routeMatch->setParam('id', $script->getId());
        $this->request->getHeaders()->addHeaderLine('Content-Type: application/json');
        $this->request->setContent(
            json_encode(
                array(
                    'identifier' => '',
                )
            )
        );

        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertEquals('Invalid data', $result->content);
        $this->assertEquals(
            array(
                'name' => array(
                    'isEmpty' => "Value is required and can't be empty",
                ),
                'identifier' =>  array(
                    'isEmpty' => "Value is required and can't be empty",
                    'regexNotMatch' => "The input does not match against pattern '~^[a-zA-Z0-9._-]+$~'",
                )
            ),
            $result->errors
        );
    }

    /**
     * Test update script with valid data
     *
     * @return void
     */
    public function testUpdateWithScriptWithValidData()
    {

        $script = Script\Model::fromArray(
            array(
                'name' => 'Test',
                'identifier' => 'test',
                'content' => 'This is content'
            )
        );
        $script->save();

        $this->setUpRoute('admin/development/script');
        $this->request->setMethod('PUT');
        $this->routeMatch->setParam('id', $script->getId());
        $this->request->getHeaders()->addHeaderLine('Content-Type: application/json');
        $this->request->setContent(
            json_encode(
                array(
                    'name' => 'Test name',
                    'identifier' => 'test-identifier',
                )
            )
        );

        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertEquals('test-identifier', $result->identifier);
        $this->assertEquals('Test name', $result->name);
        $this->assertNull($result->description);
        $this->assertNotNull($result->created_at);
        $this->assertNotNull($result->updated_at);
    }
}
