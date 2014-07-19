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
 * @category   Gc_Application
 * @package    GcBackend
 * @subpackage Controller
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace GcBackend\Controller;

use Gc\Mvc\Controller\RestAction;
use Gc\User;
use GcBackend\Filter;

/**
 * Index controller for admin module
 *
 * @category   Gc_Application
 * @package    GcBackend
 * @subpackage Controller
 */
class AuthenticationRestController extends RestAction
{
    /**
     * Authenticate user
     *
     * @param array $data Data returns
     *
     * @return array
     */
    public function create($data)
    {
        $auth = $this->getServiceLocator()->get('Auth');
        if ($auth->hasIdentity()) {
            return $auth->getIdentity()->toArray();
        }

        $loginFilter = new Filter\UserLogin();
        if ($loginFilter->setData($data) and $loginFilter->isValid()) {
            $userModel = new User\Model();
            if ($userModel->authenticate($loginFilter->getValue('login'), $loginFilter->getValue('password'))) {
                return $userModel->toArray();
            }
        }

        return array('content' => 'Can not connect');
    }
}
