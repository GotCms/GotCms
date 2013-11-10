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
 * @category   Gc
 * @package    Library
 * @subpackage Test\PHPUnit\Controller
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Gc\Test\PHPUnit\Controller;

use Gc\User\Model as UserModel;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase as ZendAbstractHttpControllerTestCase;

/**
 * Exception is the base class for all Exceptions.
 *
 * @category   Gc
 * @package    Library
 * @subpackage Test\PHPUnit\Controller
 */
class AbstractHttpControllerTestCase extends ZendAbstractHttpControllerTestCase
{
    /**
     * User model
     *
     * @var Gc\User\Model
     */
    protected $user;

    /**
     * Initialize test
     *
     * @return void
     */
    public function init()
    {
        parent::setUp();

        $this->user = UserModel::fromArray(
            array(
                'lastname' => 'Test',
                'firstname' => 'Test',
                'email' => 'test@test.com',
                'login' => 'test-user-model',
                'user_acl_role_id' => 1,
            )
        );

        $this->user->setPassword('test-user-model-password');
        $this->user->save();

        $this->user->authenticate('test-user-model', 'test-user-model-password');
        $configuration = include GC_APPLICATION_PATH . '/config/application.config.php';

        $configuration['module_listener_options']['config_glob_paths'] = array(
            'tests/config/local.php',
        );

        $this->setApplicationConfig($configuration);
    }

    /**
     * Tear down
     *
     * @return void
     */
    public function tearDown()
    {
        $this->user->delete();
        unset($this->user);
    }
}
