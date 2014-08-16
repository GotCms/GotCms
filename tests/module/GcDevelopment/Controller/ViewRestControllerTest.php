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
use Gc\View;
use Gc\Registry;

/**
 * Test view rest api
 *
 * @group    ZfModules
 * @category Gc_Tests
 * @package  ZfModules
 */
class ViewRestControllerTest extends AbstractRestControllerTestCase
{
    public function setUp()
    {
        $this->controller = new ViewRestController;
        parent::setUp();
    }

    public function tearDown()
    {
        $collection = new View\Collection;
        foreach ($collection->getAll() as $view) {
            $view->delete();
        }
    }

    /**
     * Test get views
     *
     * @return void
     */
    public function testGetListWithoutViews()
    {
        $this->setUpRoute('development/view');
        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertEquals(array(), $result->views);
    }

    /**
     * Test get views
     *
     * @return void
     */
    public function testGetListWithViews()
    {
        $view = View\Model::fromArray(
            array(
                'name' => 'Test',
                'identifier' => 'test',
                'content' => 'This is content'
            )
        );
        $view->save();

        $view = View\Model::fromId($view->getId());

        $this->setUpRoute('development/view');
        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertEquals(array($view->toArray()), $result->views);
    }

    /**
     * Test get with wrong id
     *
     * @return void
     */
    public function testGetWithWrongId()
    {
        $this->setUpRoute('development/view');
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
    public function testGetWithView()
    {
        $view = View\Model::fromArray(
            array(
                'name' => 'Test',
                'identifier' => 'test',
                'content' => 'This is content'
            )
        );
        $view->save();

        $view = View\Model::fromId($view->getId());

        $this->setUpRoute('development/view');
        $this->routeMatch->setParam('id', $view->getId());

        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertEquals($view->toArray(), $result->view);
    }

    /**
     * Test create view with invalid data
     *
     * @return void
     */
    public function testCreateWithViewWithInvalidData()
    {
        $this->setUpRoute('development/view');
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
     * Test create view with valid data
     *
     * @return void
     */
    public function testCreateWithViewWithValidData()
    {
        $this->setUpRoute('development/view');
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
     * Test delete view with wrong id
     *
     * @return void
     */
    public function testDeleteViewWithWrongId()
    {
        $this->setUpRoute('development/view');
        $this->request->setMethod('DELETE');
        $this->routeMatch->setParam('id', 1);

        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertEquals('Page not found', $result->content);
        $this->assertEquals('Page not found.', $result->message);
    }

    /**
     * Test update view with wrong id
     *
     * @return void
     */
    public function testUpdateViewWithWrongId()
    {
        $this->setUpRoute('development/view');
        $this->request->setMethod('PUT');
        $this->routeMatch->setParam('id', 1);

        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertEquals('Page not found', $result->content);
        $this->assertEquals('Page not found.', $result->message);
    }

    /**
     * Test delete view
     *
     * @return void
     */
    public function testDeleteView()
    {
        $view = View\Model::fromArray(
            array(
                'name' => 'Test',
                'identifier' => 'test',
                'content' => 'This is content'
            )
        );
        $view->save();

        $this->setUpRoute('development/view');
        $this->request->setMethod('DELETE');
        $this->routeMatch->setParam('id', $view->getId());

        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertTrue($result->success);
        $this->assertEquals('This view has been deleted.', $result->content);
    }

    /**
     * Test update view with invalid data
     *
     * @return void
     */
    public function testUpdateWithViewWithInvalidData()
    {
        $view = View\Model::fromArray(
            array(
                'name' => 'Test',
                'identifier' => 'test',
                'content' => 'This is content'
            )
        );
        $view->save();

        $this->setUpRoute('development/view');
        $this->request->setMethod('PUT');
        $this->routeMatch->setParam('id', $view->getId());
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
     * Test update view with valid data
     *
     * @return void
     */
    public function testUpdateWithViewWithValidData()
    {

        $view = View\Model::fromArray(
            array(
                'name' => 'Test',
                'identifier' => 'test',
                'content' => 'This is content'
            )
        );
        $view->save();

        $this->setUpRoute('development/view');
        $this->request->setMethod('PUT');
        $this->routeMatch->setParam('id', $view->getId());
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
