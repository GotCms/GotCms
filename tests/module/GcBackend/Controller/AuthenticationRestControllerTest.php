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

namespace GcBackend\Controller;

use Gc\Test\PHPUnit\Controller\AbstractRestControllerTestCase;
use Gc\User\Model as UserModel;

/**
 * Test authentication rest api
 *
 * @group    ZfModules
 * @category Gc_Tests
 * @package  ZfModules
 */
class AuthenticationRestControllerTest extends AbstractRestControllerTestCase
{
    public function setUp()
    {
        $this->controller = new AuthenticationRestController;
        parent::setUp();
    }

    /**
     * Test Login without credentials
     *
     * @return void
     */
    public function testLoginWithoutCredentials()
    {
        $this->setUpRoute('admin/login');
        $this->request->setMethod('POST');
        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertEquals('Can not connect', $result->content);
    }

    /**
     * Test Login with wrong credentials
     *
     * @return void
     */
    public function testLoginWithWrongCredentials()
    {
        $this->setUpRoute('admin/login');
        $this->request->setMethod('POST');
        $post = $this->request->getPost();
        $post->fromArray(
            array(
                'login' => 'test',
                'password' => 'test'
            )
        );
        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertEquals('Can not connect', $result->content);
    }

    /**
     * Test Login with good credentials
     *
     * @return void
     */
    public function testLoginWithGoodCredentials()
    {
        $user = UserModel::fromArray(
            array(
                'lastname' => 'User test',
                'firstname' => 'User test',
                'email' => 'pierre.rambaud86@gmail.com',
                'login' => 'test',
                'user_acl_role_id' => 1,
                'active' => 1
            )
        );
        $user->setPassword('test');
        $user->save();

        $this->setUpRoute('admin/login');
        $this->request->setMethod('POST');
        $post = $this->request->getPost();
        $post->fromArray(
            array(
                'login' => 'test',
                'password' => 'test'
            )
        );
        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertEquals($user->getLastname(), $result->lastname);
        $this->assertEquals($user->getFirstname(), $result->firstname);
        $this->assertEquals($user->getLogin(), $result->login);
        $this->assertEquals((bool) $user->getActive(), (bool) $result->active);

        //Test with already an identity should return same result
        $this->setUpRoute('admin/login');
        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertEquals($user->getLastname(), $result->lastname);
        $this->assertEquals($user->getFirstname(), $result->firstname);
        $this->assertEquals($user->getLogin(), $result->login);
        $this->assertEquals((bool) $user->getActive(), (bool) $result->active);

        $user->delete();
    }
}
