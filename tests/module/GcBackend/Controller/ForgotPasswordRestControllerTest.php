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
class ForgotPasswordRestControllerTest extends AbstractRestControllerTestCase
{
    public function setUp()
    {
        $this->controller = new ForgotPasswordRestController;
        parent::setUp();
    }

    public function tearDown()
    {
        $collection = new UserCollection();
        foreach ($collection->getAll() as $user) {
            $user->delete();
        }
    }

    /**
     * Test ForgotPassword without credentials
     *
     * @return void
     */
    public function testForgotPasswordWithoutCredentials()
    {
        $this->setUpRoute('admin/password-reset');
        $this->request->setMethod('POST');
        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertEquals('Invalid data', $result->content);
        $this->assertEquals(
            array(
                'email' => array(
                    'isEmpty' => "Value is required and can't be empty"
                )
            ),
            $result->errors
        );
    }

    /**
     * Test ForgotPassword with good credentials
     *
     * @return void
     */
    public function testForgotPasswordWithWrongCredentials()
    {
        $this->setUpRoute('admin/password-reset');
        $this->request->setMethod('POST');
        $post = $this->request->getPost();
        $post->fromArray(
            array(
                'email' => 'test@test.com',
            )
        );
        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertEquals('Message sent, you have one hour to change your password!', $result->content);
    }

    /**
     * Test ForgotPassword with wrong credentials
     *
     * @return void
     */
    public function testForgotPasswordWithGoodCredentials()
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

        $this->setUpRoute('admin/password-reset');
        $this->request->setMethod('POST');
        $post = $this->request->getPost();
        $post->fromArray(
            array(
                'email' => $user->getEmail(),
            )
        );
        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertEquals('Message sent, you have one hour to change your password!', $result->content);
    }

    /**
     * Test ForgotPassword
     *
     * @return void
     */
    public function testResetPasswordWithoutKey()
    {
        $this->setUpRoute('admin/password-reset');
        $this->routeMatch->setParam('id', '100000');
        $this->request->setMethod('PUT');
        $post = $this->request->setContent(
            ''
        );

        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertEquals('Invalid data', $result->content);
        $this->assertEquals(array(), $result->errors);
    }

    /**
     * Test ForgotPassword
     *
     * @return void
     */
    public function testResetPasswordWithWrongKeyAndId()
    {
        $this->setUpRoute('admin/password-reset');
        $this->routeMatch->setParam('id', '1000');
        $this->routeMatch->setParam('key', 'abcde');
        $this->request->setMethod('PUT');
        $post = $this->request->setContent(
            ''
        );

        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertEquals('Invalid data', $result->content);
        $this->assertEquals(array(), $result->errors);
    }

    /**
     * Test ForgotPassword with good credentials
     *
     * @return void
     */
    public function testResetPasswordWithWrongPassword()
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
        $user->setRetrievePasswordKey('abcde');
        $user->setRetrieveUpdatedAt(date('Y-m-d H:i:s'));
        $user->save();

        $this->setUpRoute('admin/password-reset');
        $this->routeMatch->setParam('id', $user->getId());
        $this->routeMatch->setParam('key', 'abcde');
        $this->request->setMethod('PUT');
        $post = $this->request->setContent(
            'password=test2&password_confirm=azerty'
        );

        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertEquals('Invalid data', $result->content);
        $this->assertEquals(
            array(
                'password_confirm' => array(
                    'notSame' => 'The two given tokens do not match'
                )
            ),
            $result->errors
        );
    }

    /**
     * Test ForgotPassword with good credentials
     *
     * @return void
     */
    public function testResetPassword()
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
        $user->setRetrievePasswordKey('abcde');
        $user->setRetrieveUpdatedAt(date('Y-m-d H:i:s'));
        $user->save();

        $this->setUpRoute('admin/password-reset');
        $this->routeMatch->setParam('id', $user->getId());
        $this->routeMatch->setParam('key', 'abcde');
        $this->request->setMethod('PUT');
        $post = $this->request->setContent(
            'password=test2&password_confirm=test2'
        );

        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertEquals('Password changed', $result->content);
    }
}
