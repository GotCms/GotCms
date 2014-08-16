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
use Gc\Layout;
use Gc\Registry;

/**
 * Test layout rest api
 *
 * @group    ZfModules
 * @category Gc_Tests
 * @package  ZfModules
 */
class LayoutRestControllerTest extends AbstractRestControllerTestCase
{
    public function setUp()
    {
        $this->controller = new LayoutRestController;
        parent::setUp();
    }

    public function tearDown()
    {
        $collection = new Layout\Collection;
        foreach ($collection->getAll() as $layout) {
            $layout->delete();
        }
    }

    /**
     * Test get layouts
     *
     * @return void
     */
    public function testGetListWithoutLayouts()
    {
        $this->setUpRoute('development/layout');
        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertEquals(array(), $result->layouts);
    }

    /**
     * Test get layouts
     *
     * @return void
     */
    public function testGetListWithLayouts()
    {
        $layout = Layout\Model::fromArray(
            array(
                'name' => 'Test',
                'identifier' => 'test',
                'content' => 'This is content'
            )
        );
        $layout->save();

        $layout = Layout\Model::fromId($layout->getId());

        $this->setUpRoute('development/layout');
        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertEquals(array($layout->toArray()), $result->layouts);
    }

    /**
     * Test get with wrong id
     *
     * @return void
     */
    public function testGetWithWrongId()
    {
        $this->setUpRoute('development/layout');
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
    public function testGetWithLayout()
    {
        $layout = Layout\Model::fromArray(
            array(
                'name' => 'Test',
                'identifier' => 'test',
                'content' => 'This is content'
            )
        );
        $layout->save();

        $layout = Layout\Model::fromId($layout->getId());

        $this->setUpRoute('development/layout');
        $this->routeMatch->setParam('id', $layout->getId());

        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertEquals($layout->toArray(), $result->layout);
    }

    /**
     * Test create layout with invalid data
     *
     * @return void
     */
    public function testCreateWithLayoutWithInvalidData()
    {
        $this->setUpRoute('development/layout');
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
     * Test create layout with valid data
     *
     * @return void
     */
    public function testCreateWithLayoutWithValidData()
    {
        $this->setUpRoute('development/layout');
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
     * Test delete layout with wrong id
     *
     * @return void
     */
    public function testDeleteLayoutWithWrongId()
    {
        $this->setUpRoute('development/layout');
        $this->request->setMethod('DELETE');
        $this->routeMatch->setParam('id', 1);

        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertEquals('Page not found', $result->content);
        $this->assertEquals('Page not found.', $result->message);
    }

    /**
     * Test update layout with wrong id
     *
     * @return void
     */
    public function testUpdateLayoutWithWrongId()
    {
        $this->setUpRoute('development/layout');
        $this->request->setMethod('PUT');
        $this->routeMatch->setParam('id', 1);

        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertEquals('Page not found', $result->content);
        $this->assertEquals('Page not found.', $result->message);
    }

    /**
     * Test delete layout
     *
     * @return void
     */
    public function testDeleteLayout()
    {
        $layout = Layout\Model::fromArray(
            array(
                'name' => 'Test',
                'identifier' => 'test',
                'content' => 'This is content'
            )
        );
        $layout->save();

        $this->setUpRoute('development/layout');
        $this->request->setMethod('DELETE');
        $this->routeMatch->setParam('id', $layout->getId());

        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertTrue($result->success);
        $this->assertEquals('This layout has been deleted.', $result->content);
    }

    /**
     * Test update layout with invalid data
     *
     * @return void
     */
    public function testUpdateWithLayoutWithInvalidData()
    {
        $layout = Layout\Model::fromArray(
            array(
                'name' => 'Test',
                'identifier' => 'test',
                'content' => 'This is content'
            )
        );
        $layout->save();

        $this->setUpRoute('development/layout');
        $this->request->setMethod('PUT');
        $this->routeMatch->setParam('id', $layout->getId());
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
     * Test update layout with valid data
     *
     * @return void
     */
    public function testUpdateWithLayoutWithValidData()
    {

        $layout = Layout\Model::fromArray(
            array(
                'name' => 'Test',
                'identifier' => 'test',
                'content' => 'This is content'
            )
        );
        $layout->save();

        $this->setUpRoute('development/layout');
        $this->request->setMethod('PUT');
        $this->routeMatch->setParam('id', $layout->getId());
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
