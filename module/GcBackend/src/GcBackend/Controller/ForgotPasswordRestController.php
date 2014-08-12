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

use GcBackend\Filter\ForgotPassword as ForgotPasswordFilter;
use Gc\User;
use Gc\Mvc\Controller\RestAction;
use Zend\Validator\Identical;

/**
 * Index controller for admin module
 *
 * @category   Gc_Application
 * @package    GcBackend
 * @subpackage Controller
 */
class ForgotPasswordRestController extends RestAction
{
    /**
     * Demand a password reset
     *
     * @param array $data Data to used
     *
     * @return array
     */
    public function create($data)
    {
        $forgotPasswordFilter = new ForgotPasswordFilter();
        $forgotPasswordFilter->initEmail();
        $forgotPasswordFilter->setData($data);
        if ($forgotPasswordFilter->isValid()) {
            $userModel = new User\Model();
            $userModel->sendForgotPasswordEmail($forgotPasswordFilter->getValue('email'));

            return array(
                'content' => 'Message sent, you have one hour to change your password!',
            );
        }

        return array('content' => 'Invalid data', 'errors' => $forgotPasswordFilter->getMessages());
    }

    /**
     * Reset password
     *
     * @param integer $id   Identifier
     * @param array   $data Data to used
     *
     * @return array
     */
    public function update($id, $data)
    {
        $forgotPasswordFilter = new ForgotPasswordFilter();
        $key                  = $this->getRouteMatch()->getParam('key');
        if (!empty($id) and !empty($key)) {
            $userModel = User\Model::fromId($id);
            if ($userModel and $userModel->getRetrievePasswordKey() == $key
                and strtotime('-1 hour') < strtotime($userModel->getRetrieveUpdatedAt())) {
                $forgotPasswordFilter->initResetPassword();
                $forgotPasswordFilter->get('password_confirm')
                    ->getValidatorChain()
                    ->addValidator(new Identical($data['password']));
                $forgotPasswordFilter->setData($data);
                if ($forgotPasswordFilter->isValid()) {
                    $userModel->setPassword($forgotPasswordFilter->getValue('password'));
                    $userModel->setRetrievePasswordKey(null);
                    $userModel->setRetrieveUpdatedAt(null);
                    $userModel->save();
                    return array('content' => 'Password changed');
                }
            }
        }

        return array('content' => 'Invalid data', 'errors' => $forgotPasswordFilter->getMessages());
    }
}
