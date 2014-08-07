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
use Gc\User\Role;
use Gc\Registry;
use Zend\Json\Json;

/**
 * Test role rest api
 *
 * @group    ZfModules
 * @category Gc_Tests
 * @package  ZfModules
 */
class RoleRestControllerTest extends AbstractRestControllerTestCase
{
    public function setUp()
    {
        $this->controller = new RoleRestController;
        parent::setUp();
    }

    public function tearDown()
    {
        $collection = new Role\Collection;
        foreach ($collection->getAll() as $role) {
            if ($role->getName() != Role\Model::PROTECTED_NAME) {
                $role->delete();
            }
        }
    }

    /**
     * Test get roles
     *
     * @return void
     */
    public function testGetListRoles()
    {
        $this->createRole();
        $this->setUpRoute('admin/config/user/role');
        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertInternalType('array', $result->roles);
        $this->assertEquals('Administrator', $result->roles[0]['name']);
        $this->assertNull($result->roles[0]['description']);
    }


    /**
     * Test create role with invalid data
     *
     * @return void
     */
    public function testCreateUserWithInvalidData()
    {
        $this->setUpRoute('admin/config/user/role');
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
            ),
            $result->errors
        );
    }

    /**
     * Test create role with valid data
     *
     * @return void
     */
    public function testCreateUserWithValidData()
    {
        $this->setUpRoute('admin/config/user/role');
        $this->request->setMethod('POST');
        $post = $this->request->getPost();
        $post->fromArray(
            array(
                'name' => 'Developper',
                'description' => 'Developper role',
            )
        );

        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertEquals('Developper', $result->name);
        $this->assertEquals('Developper role', $result->description);
    }

    /**
     * Test get with wrong id
     *
     * @return void
     */
    public function testGetWithWrongId()
    {
        $this->setUpRoute('admin/config/user/role');
        $this->routeMatch->setParam('id', 1000);

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
        $role = $this->createRole();
        $this->setUpRoute('admin/config/user/role');
        $this->routeMatch->setParam('id', $role->getId());

        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertEquals($role->getName(), $result->name);
        $this->assertEquals($role->getDescription(), $result->description);
    }

    /**
     * Test delete role with wrong id
v     *
     * @return void
     */
    public function testDeleteRoleWithWrongId()
    {
        $this->setUpRoute('admin/config/user/role');
        $this->request->setMethod('DELETE');
        $this->routeMatch->setParam('id', 1000);

        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertEquals('Page not found', $result->content);
        $this->assertEquals('Page not found.', $result->message);
    }

    /**
     * Test delete role
     *
     * @return void
     */
    public function testDeleteRole()
    {
        $role = $this->createRole();
        $this->setUpRoute('admin/config/user/role');
        $this->request->setMethod('DELETE');
        $this->routeMatch->setParam('id', $role->getId());

        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertTrue($result->success);
        $this->assertEquals('This role has been deleted.', $result->content);
    }

    /**
     * Test update role without role
     *
     * @return void
     */
    public function testUpdateRoleWithoutUser()
    {
        $this->setUpRoute('admin/config/user/role');
        $this->request->setMethod('PUT');
        $this->routeMatch->setParam('id', 1000);

        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertEquals('Page not found', $result->content);
        $this->assertEquals('Page not found.', $result->message);
    }

    /**
     * Test update role with invalid data
     *
     * @return void
     */
    public function testUpdateRoleWithInvalidData()
    {
        $role = $this->createRole();
        $this->setUpRoute('admin/config/user/role');
        $this->request->setMethod('PUT');
        $this->routeMatch->setParam('id', $role->getId());
        $this->request->getHeaders()->addHeaderLine('Content-Type: application/json');
        $this->request->setContent(
            json_encode(
                array(
                    'name' => '',
                )
            )
        );

        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertEquals('Invalid data', $result->content);
        $this->assertEquals(
            array(
                'name' =>  array(
                    'isEmpty' => "Value is required and can't be empty",
                ),
            ),
            $result->errors
        );
    }

    /**
     * Test update role with valid data
     *
     * @return void
     */
    public function testUpdateRoleWithValidData()
    {
        $role = $this->createRole();
        $this->setUpRoute('admin/config/user/role');
        $this->request->setMethod('PUT');
        $this->routeMatch->setParam('id', $role->getId());
        $this->request->getHeaders()->addHeaderLine('Content-Type: application/json');
        $this->request->setContent(
            json_encode(
                array(
                    'name' => 'Developper',
                    'description' => 'New description for developper role',
                )
            )
        );

        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertEquals('Developper', $result->name);
        $this->assertEquals('New description for developper role', $result->description);
    }

    public function createRole()
    {
        $role = Role\Model::fromArray(
            array(
                'name' => 'Developper',
                'description' => 'Description for developper',
            )
        );
        $role->save();

        return $role;
    }
}
