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
use Gc\User\Collection as UserCollection;
use Gc\User\Model as UserModel;

/**
 * Test authentication rest api
 *
 * @group    ZfModules
 * @category Gc_Tests
 * @package  ZfModules
 */
class DeauthenticationRestControllerTest extends AbstractRestControllerTestCase
{
    public function setUp()
    {
        $this->controller = new DeauthenticationRestController;
        parent::setUp();
    }

    public function tearDown()
    {
        $collection = new UserCollection();
        foreach ($collection->getAll() as $user) {
            $user->delete();
        }

        $auth = $this->controller->getServiceLocator()->get('Auth');
        if ($auth->hasIdentity()) {
            $auth->clearIdentity();
        }
    }

    /**
     * Test Login without authentication
     *
     * @return void
     */
    public function testLogoutWithoutAuthentication()
    {
        $this->setUpRoute('backend/logout');
        $this->request->setMethod('POST');

        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertEquals('Page not found', $result->content);
        $this->assertEquals('Page not found.', $result->message);
    }

    /**
     * Test Logout
     *
     * @return void
     */
    public function testLogout()
    {
        $userModel = UserModel::fromArray(
            array(
                'lastname' => 'Test',
                'firstname' => 'Test',
                'email' => 'pierre.rambaud86@gmail.com',
                'login' => 'login-test',
                'user_acl_role_id' => 2,
                'active' => true
            )
        );

        $userModel->setPassword('password-test');
        $userModel->save();
        $userModel->authenticate('login-test', 'password-test');

        $this->setUpRoute('backend/logout');
        $this->request->setMethod('POST');

        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertTrue($result->success);
    }
}
