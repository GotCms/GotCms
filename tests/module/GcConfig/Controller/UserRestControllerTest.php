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
use Gc\User;
use Gc\Registry;
use Zend\Json\Json;

/**
 * Test user rest api
 *
 * @group    ZfModules
 * @category Gc_Tests
 * @package  ZfModules
 */
class UserRestControllerTest extends AbstractRestControllerTestCase
{
    public function setUp()
    {
        $this->controller = new UserRestController;
        parent::setUp();
    }

    public function tearDown()
    {
        $collection = new User\Collection;
        foreach ($collection->getUsers() as $user) {
            $user->delete();
        }
    }

    /**
     * Test get users
     *
     * @return void
     */
    public function testGetListWithoutUsers()
    {
        $this->setUpRoute('admin/config/user');
        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertEquals(array(), $result->users);
    }

    /**
     * Test get users
     *
     * @return void
     */
    public function testGetListUsers()
    {
        $user = $this->createUser();

        $this->setUpRoute('admin/config/user');
        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertInternalType('array', $result->users);
        $this->assertEquals('Rambaud', $result->users[0]['lastname']);
        $this->assertEquals('Pierre', $result->users[0]['firstname']);
        $this->assertEquals('GoT', $result->users[0]['login']);
        $this->assertEquals('pierre.rambaud86@gmail.com', $result->users[0]['email']);
        $this->assertEquals(1, $result->users[0]['user_acl_role_id']);

    }


    /**
     * Test create user with invalid data
     *
     * @return void
     */
    public function testCreateUserWithInvalidData()
    {
        $this->setUpRoute('admin/config/user');
        $this->controller->setServiceLocator(Registry::get('Application')->getServiceManager());
        $this->request->setMethod('POST');
        $post = $this->request->getPost();
        $post->fromArray(
            array(
                'lastname' => '',
            )
        );

        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertEquals('Invalid data', $result->content);
        $this->assertEquals(
            array(
                'lastname' => array(
                    'isEmpty' => "Value is required and can't be empty",
                ),
                'firstname' =>  array(
                    'isEmpty' => "Value is required and can't be empty",
                ),
                'login' =>  array(
                    'isEmpty' => "Value is required and can't be empty",
                ),
                'email' =>  array(
                    'isEmpty' => "Value is required and can't be empty",
                    'emailAddressInvalid' => 'Invalid type given. String expected',
                ),
                'user_acl_role_id' =>  array(
                    'isEmpty' => "Value is required and can't be empty",
                ),
                'password' =>  array(
                    'isEmpty' => "Value is required and can't be empty",
                ),
                'password_confirm' =>  array(
                    'isEmpty' => "Value is required and can't be empty",
                    'missingToken' => 'No token was provided to match against',
                ),
            ),
            $result->errors
        );
    }

    /**
     * Test create user with valid data
     *
     * @return void
     */
    public function testCreateUserWithValidData()
    {
        $this->setUpRoute('admin/config/user');
        $this->controller->setServiceLocator(Registry::get('Application')->getServiceManager());
        $this->request->setMethod('POST');
        $post = $this->request->getPost();
        $post->fromArray(
            array(
                'email' => 'pierre.rambaud86@gmail.com',
                'firstname' => 'Pierre',
                'lastname' => 'Rambaud',
                'login' => 'GoT',
                'password' => 'test',
                'password_confirm' => 'test',
                'user_acl_role_id' => 1,
            )
        );

        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertEquals('Pierre', $result->firstname);
        $this->assertEquals('Rambaud', $result->lastname);
        $this->assertEquals('GoT', $result->login);
        $this->assertEquals('pierre.rambaud86@gmail.com', $result->email);
        $this->assertEquals(1, $result->user_acl_role_id);
        $this->assertNull($result->password);
    }

    /**
     * Test get with wrong id
     *
     * @return void
     */
    public function testGetWithWrongId()
    {
        $this->setUpRoute('admin/config/user');
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
        $user = $this->createUser();
        $this->setUpRoute('admin/config/user');
        $this->routeMatch->setParam('id', $user->getId());

        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertEquals('Pierre', $result->firstname);
        $this->assertEquals('Rambaud', $result->lastname);
        $this->assertEquals('GoT', $result->login);
        $this->assertEquals('pierre.rambaud86@gmail.com', $result->email);
        $this->assertEquals(1, $result->user_acl_role_id);
        $this->assertNull($result->password);
    }

    /**
     * Test delete user with wrong id
v     *
     * @return void
     */
    public function testDeleteUserWithWrongId()
    {
        $this->setUpRoute('admin/config/user');
        $this->request->setMethod('DELETE');
        $this->routeMatch->setParam('id', 1);

        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertEquals('Page not found', $result->content);
        $this->assertEquals('Page not found.', $result->message);
    }

    /**
     * Test delete user
     *
     * @return void
     */
    public function testDeleteUser()
    {
        $user = $this->createUser();
        $this->setUpRoute('admin/config/user');
        $this->request->setMethod('DELETE');
        $this->routeMatch->setParam('id', $user->getId());

        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertTrue($result->success);
        $this->assertEquals('This user has been deleted.', $result->content);
    }

    /**
     * Test update user without user
     *
     * @return void
     */
    public function testUpdateUserWithoutUser()
    {
        $this->setUpRoute('admin/config/user');
        $this->controller->setServiceLocator(Registry::get('Application')->getServiceManager());
        $this->request->setMethod('PUT');
        $this->routeMatch->setParam('id', 1);

        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertEquals('Page not found', $result->content);
        $this->assertEquals('Page not found.', $result->message);
    }

    /**
     * Test update user with invalid data
     *
     * @return void
     */
    public function testUpdateUserWithInvalidData()
    {
        $user = $this->createUser();
        $this->setUpRoute('admin/config/user');
        $this->controller->setServiceLocator(Registry::get('Application')->getServiceManager());
        $this->request->setMethod('PUT');
        $this->routeMatch->setParam('id', $user->getId());
        $this->request->getHeaders()->addHeaderLine('Content-Type: application/json');
        $this->request->setContent(
            json_encode(
                array(
                    'password' => 'test',
                )
            )
        );

        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertEquals('Invalid data', $result->content);
        $this->assertEquals(
            array(
                'password_confirm' =>  array(
                    'isEmpty' => "Value is required and can't be empty",
                    'notSame' => 'The two given tokens do not match',
                ),
            ),
            $result->errors
        );
    }

    /**
     * Test update user with valid data
     *
     * @return void
     */
    public function testUpdateUserWithValidData()
    {
        $user = $this->createUser();
        $this->setUpRoute('admin/config/user');
        $this->controller->setServiceLocator(Registry::get('Application')->getServiceManager());
        $this->request->setMethod('PUT');
        $this->routeMatch->setParam('id', $user->getId());
        $this->request->getHeaders()->addHeaderLine('Content-Type: application/json');
        $this->request->setContent(
            json_encode(
                array(
                    'email' => 'pierre.rambaud@got-cms.com',
                    'active' => true,
                    'password' => 'test',
                    'password_confirm' => 'test'
                )
            )
        );

        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertEquals('Pierre', $result->firstname);
        $this->assertEquals('Rambaud', $result->lastname);
        $this->assertEquals('GoT', $result->login);
        $this->assertEquals('pierre.rambaud@got-cms.com', $result->email);
        $this->assertTrue($result->active);
        $this->assertEquals(1, $result->user_acl_role_id);
        $this->assertNull($result->password);
    }

    public function createUser()
    {
        $user = User\Model::fromArray(
            array(
                'email' => 'pierre.rambaud86@gmail.com',
                'firstname' => 'Pierre',
                'lastname' => 'Rambaud',
                'login' => 'GoT',
                'password' => 'test',
                'password_confirm' => 'test',
                'user_acl_role_id' => 1,
            )
        );
        $user->save();

        return $user;
    }
}
