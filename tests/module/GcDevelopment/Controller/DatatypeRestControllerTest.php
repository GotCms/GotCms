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
use Gc\Datatype;
use Zend\Json\Json;

/**
 * Test datatype rest api
 *
 * @group    ZfModules
 * @category Gc_Tests
 * @package  ZfModules
 */
class DatatypeRestControllerTest extends AbstractRestControllerTestCase
{
    public function setUp()
    {
        $this->controller = new DatatypeRestController;
        parent::setUp();
    }

    public function tearDown()
    {
        $collection = new Datatype\Collection;
        foreach ($collection->getAll() as $datatype) {
            $datatype->delete();
        }
    }

    /**
     * Test get datatypes
     *
     * @return void
     */
    public function testGetListWithoutDatatypes()
    {
        $this->setUpRoute('development/datatype');
        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertEquals(array(), $result->datatypes);
    }

    /**
     * Test get datatypes
     *
     * @return void
     */
    public function testGetListWithDatatypes()
    {
        $datatype = Datatype\Model::fromArray(
            array(
                'name' => 'Test',
                'model' => 'Textstring',
            )
        );
        $datatype->save();

        $datatype = Datatype\Model::fromId($datatype->getId());

        $this->setUpRoute('development/datatype');
        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertInternalType('array', $result->datatypes);
        $this->assertEquals($datatype->getInfos(), $result->datatypes[0]['infos']);
        $this->assertEquals($datatype->toArray(), $result->datatypes[0]['datatype']);
        $this->assertInternalType('string', $result->datatypes[0]['prevalue_editor']);
    }


    /**
     * Test create datatype with invalid data
     *
     * @return void
     */
    public function testCreateWithDatatypeWithInvalidData()
    {
        $this->setUpRoute('development/datatype');
        $this->request->setMethod('POST');
        $post = $this->request->getPost();
        $post->fromArray(
            array(
                'name' => '',
            )
        );

        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertEquals('Invalid data', $result->content);
        $this->assertEquals(
            array(
                'name' => array(
                    'isEmpty' => "Value is required and can't be empty",
                ),
                'model' =>  array(
                    'isEmpty' => "Value is required and can't be empty",
                )
            ),
            $result->errors
        );
    }

    /**
     * Test create datatype with valid data
     *
     * @return void
     */
    public function testCreateWithDatatypeWithValidData()
    {
        $this->setUpRoute('development/datatype');
        $this->request->setMethod('POST');
        $post = $this->request->getPost();
        $post->fromArray(
            array(
                'name' => 'Test name',
                'model' => 'Textstring',
            )
        );

        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertEquals('Textstring', $result->datatype['model']);
        $this->assertEquals('Test name', $result->datatype['name']);
    }

    /**
     * Test get with wrong id
     *
     * @return void
     */
    public function testGetWithWrongId()
    {
        $this->setUpRoute('development/datatype');
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
    public function testGetWithGoodId()
    {
        $datatype = Datatype\Model::fromArray(
            array(
                'name' => 'Test',
                'model' => 'Textstring',
            )
        );
        $datatype->save();

        $datatype = Datatype\Model::fromId($datatype->getId());

        $this->setUpRoute('development/datatype');
        $this->routeMatch->setParam('id', $datatype->getId());

        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertEquals($datatype->getInfos(), $result->infos);
        $this->assertEquals($datatype->toArray(), $result->datatype);
        $this->assertInternalType('string', $result->prevalue_editor);
    }

    /**
     * Test update with wrong id
     *
     * @return void
     */
    public function testUpdateWithWrongId()
    {
        $this->setUpRoute('development/datatype');
        $this->routeMatch->setParam('id', 1);
        $this->request->setMethod('PUT');

        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertEquals('Page not found', $result->content);
        $this->assertEquals('Page not found.', $result->message);
    }

    /**
     * Test Update with good id and errors
     *
     * @return void
     */
    public function testUpdateWithGoodIdAndErrors()
    {
        $datatype = Datatype\Model::fromArray(
            array(
                'name' => 'Test',
                'model' => 'Textstring',
            )
        );
        $datatype->save();

        $datatype = Datatype\Model::fromId($datatype->getId());

        $this->setUpRoute('development/datatype');
        $this->routeMatch->setParam('id', $datatype->getId());
        $this->request->setMethod('PUT');
        $this->request->setContent(
            'name=&model='
        );

        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertEquals('Invalid data', $result->content);
        $this->assertEquals(
            array(
                'name' => array(
                    'isEmpty' => "Value is required and can't be empty",
                ),
                'model' =>  array(
                    'isEmpty' => "Value is required and can't be empty",
                )
            ),
            $result->errors
        );
    }


    /**
     * Test Update with good id
     *
     * @return void
     */
    public function testUpdateWithGoodId()
    {
        $datatype = Datatype\Model::fromArray(
            array(
                'name' => 'Test',
                'model' => 'Textstring',
            )
        );
        $datatype->save();

        $datatype = Datatype\Model::fromId($datatype->getId());

        $this->setUpRoute('development/datatype');
        $this->routeMatch->setParam('id', $datatype->getId());
        $this->request->setMethod('PUT');
        $post = $this->request->setContent(
            'name=Test+name&model=Textstring&length=50'
        );

        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertEquals($datatype->getInfos(), $result->infos);
        $this->assertEquals('Test name', $result->datatype['name']);
        $this->assertEquals('Textstring', $result->datatype['model']);
        $this->assertInternalType('string', $result->prevalue_editor);
        $this->assertContains(
            '<input type="text" name="length"',
            $result->prevalue_editor
        );
    }

    /**
     * Test Update with good id and change model
     *
     * @return void
     */
    public function testUpdateWithGoodIdAndChangeModel()
    {
        $datatype = Datatype\Model::fromArray(
            array(
                'name' => 'Test',
                'model' => 'Textstring',
            )
        );
        $datatype->save();

        $datatype = Datatype\Model::fromId($datatype->getId());

        $this->setUpRoute('development/datatype');
        $this->routeMatch->setParam('id', $datatype->getId());
        $this->request->setMethod('PUT');
        $post = $this->request->setContent(
            'name=Test+name&model=Textarea'
        );

        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertEquals($datatype->getInfos(), $result->infos);
        $this->assertEquals('Test name', $result->datatype['name']);
        $this->assertEquals('Textarea', $result->datatype['model']);
        $this->assertInternalType('string', $result->prevalue_editor);
        $this->assertContains(
            '<input type="text" name="cols"',
            $result->prevalue_editor
        );
    }


    /**
     * Test delete datatype with wrong id
     *
     * @return void
     */
    public function testDeleteDatatypeWithWrongId()
    {
        $this->setUpRoute('development/datatype');
        $this->request->setMethod('DELETE');
        $this->routeMatch->setParam('id', 1);

        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertEquals('Page not found', $result->content);
        $this->assertEquals('Page not found.', $result->message);
    }

    /**
     * Test delete datatype
     *
     * @return void
     */
    public function testDeleteDatatype()
    {
        $datatype = Datatype\Model::fromArray(
            array(
                'name' => 'Test',
                'model' => 'Textstring'
            )
        );
        $datatype->save();

        $this->setUpRoute('development/datatype');
        $this->request->setMethod('DELETE');
        $this->routeMatch->setParam('id', $datatype->getId());

        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertTrue($result->success);
        $this->assertEquals('This datatype has been deleted.', $result->content);
    }
}
