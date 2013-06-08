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
 * @package    Config
 * @subpackage Controller
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Config\Controller;

use Config\Form\UserLogin;
use Config\Form\User as UserForm;
use Config\Form\UserForgotPassword as UserForgotForm;
use Gc\Mvc\Controller\Action;
use Gc\User;
use Gc\User\Role;
use Zend\Http\Request;
use Zend\View\Model\ViewModel;
use Zend\Validator\Identical;
use DateTime;

/**
 * User controller
 *
 * @category   Gc_Application
 * @package    Config
 * @subpackage Controller
 */
class UserController extends Action
{
    /**
     * Contains information about acl
     *
     * @var array $aclPage
     */
    protected $aclPage = array('resource' => 'Config', 'permission' => 'user');

    /**
     * List all roles
     *
     * @return \Zend\View\Model\ViewModel|array
     */
    public function indexAction()
    {
        $userCollection = new User\Collection();

        return array('users' => $userCollection->getUsers());
    }

    /**
     * Login user
     *
     * @return \Zend\View\Model\ViewModel|array
     */
    public function loginAction()
    {
        if ($this->getAuth()->hasIdentity()) {
            $redirect = $this->getRouteMatch()->getParam('redirect');
            if (!empty($redirect)) {
                return $this->redirect()->toUrl(base64_decode($redirect));
            }

            return $this->redirect()->toRoute('admin');
        }

        $this->layout()->setTemplate('layouts/one-page.phtml');
        $loginForm = new UserLogin();

        $post = $this->getRequest()->getPost();
        if ($this->getRequest()->isPost() and $loginForm->setData($post->toArray()) and $loginForm->isValid()) {
            $userModel = new User\Model();
            $redirect  = $loginForm->getValue('redirect');
            if ($userId = $userModel->authenticate($post->get('login'), $post->get('password'))) {
                if (!empty($redirect)) {
                    return $this->redirect()->toUrl(base64_decode($redirect));
                }

                return $this->redirect()->toRoute('admin');
            }

            $this->flashMessenger()->addErrorMessage('Can not connect');
            return $this->redirect()->toRoute('config/user/login', array('redirect' => $redirect));
        }

        $loginForm->get('redirect')->setValue($this->getRouteMatch()->getParam('redirect'));

        return array('form' => $loginForm);
    }

    /**
     * Forgot password action
     *
     * @return \Zend\View\Model\ViewModel|array
     */
    public function forgotPasswordAction()
    {
        $this->layout()->setTemplate('layouts/one-page.phtml');
        $forgotPasswordForm = new UserForgotForm();
        $id                 = $this->getRouteMatch()->getParam('id');
        $key                = $this->getRouteMatch()->getParam('key');
        if (!empty($id) and !empty($key)) {
            $userModel = User\Model::fromId($id);
            if ($userModel->getRetrievePasswordKey() == $key
                and strtotime('-1 hour') < strtotime($userModel->getRetrieveUpdatedAt())) {
                $forgotPasswordForm->setAttribute(
                    'action',
                    $this->url()->fromRoute(
                        'config/user/forgot-password-key',
                        array(
                            'id' => $id,
                            'key' => $key
                        )
                    )
                );

                $forgotPasswordForm->initResetForm();
                if ($this->getRequest()->isPost()) {
                    $post = $this->getRequest()->getPost();
                    $forgotPasswordForm->getInputFilter()
                        ->get('password_confirm')
                        ->getValidatorChain()
                        ->addValidator(new Identical($post['password']));
                    $forgotPasswordForm->setData($post->toArray());
                    if ($forgotPasswordForm->isValid()) {
                        $userModel->setPassword($forgotPasswordForm->getValue('password'));
                        $userModel->setRetrievePasswordKey(null);
                        $userModel->setRetrieveUpdatedAt(null);
                        $userModel->save();
                    }

                    return $this->redirect()->toRoute('admin');
                }

                return array('form' => $forgotPasswordForm);
            }

            return $this->redirect()->toRoute('admin');
        } else {
            $forgotPasswordForm->setAttribute('action', $this->url()->fromRoute('config/user/forgot-password'));
            $forgotPasswordForm->initEmail();
            if ($this->getRequest()->isPost()) {
                $post = $this->getRequest()->getPost();
                $forgotPasswordForm->setData($post->toArray());
                if ($forgotPasswordForm->isValid()) {
                    $userModel = new User\Model();
                    if ($userModel->sendForgotPasswordEmail($forgotPasswordForm->getValue('email'))) {
                        return $this->redirect()->toRoute('admin');
                    }
                }
            }
        }

        return array('form' => $forgotPasswordForm);
    }

    /**
     * Logout action
     *
     * @return \Zend\View\Model\ViewModel|array
     */
    public function logoutAction()
    {
        $this->getAuth()->clearIdentity();
        return $this->redirect()->toRoute('admin');
    }

    /**
     * Create user
     *
     * @return \Zend\View\Model\ViewModel|array
     */
    public function createAction()
    {
        $form = new UserForm();
        $form->setAttribute('action', $this->url()->fromRoute('config/user/create'));
        $form->passwordRequired();
        $post = $this->getRequest()->getPost()->toArray();
        if ($this->getRequest()->isPost()) {
            $form->setData($post);
            $form->getInputFilter()
                ->get('password_confirm')
                ->getValidatorChain()
                ->addValidator(new Identical(empty($post['password']) ? null : $post['password']));

            if ($form->isValid()) {
                $userModel = new User\Model();
                $userModel->setData($post);
                $userModel->setPassword($post['password']);
                $userModel->save();
                $this->flashMessenger()->addSuccessMessage('User saved!');

                return $this->redirect()->toRoute('config/user/edit', array('id' => $userModel->getId()));
            }

            $this->useFlashMessenger();
            $this->flashMessenger()->addErrorMessage('User can not be saved');
        }

        return array('form' => $form);
    }

    /**
     * Delete user
     *
     * @return \Zend\View\Model\ViewModel|array
     */
    public function deleteAction()
    {
        $user = User\Model::fromId($this->getRouteMatch()->getParam('id'));
        if (!empty($user) and $user->getRole()->getName() !== Role\Model::PROTECTED_NAME and $user->delete()) {
            return $this->returnJson(array('success' => true, 'message' => 'The user has been deleted'));
        }

        return $this->returnJson(array('success' => false, 'message' => 'User does not exists'));
    }

    /**
     * Edit user
     *
     * @return \Zend\View\Model\ViewModel|array
     */
    public function editAction()
    {
        $userId    = $this->getRouteMatch()->getParam('id');
        $userModel = User\Model::fromId($userId);

        if (empty($userModel)) {
            $this->flashMessenger()->addErrorMessage("Can't edit this user");
            return $this->redirect()->toRoute('config/user');
        }

        $form = new UserForm();
        $form->setAttribute('action', $this->url()->fromRoute('config/user/edit', array('id' => $userId)));
        $form->loadValues($userModel);
        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost()->toArray();
            if (!empty($post['password'])) {
                $form->passwordRequired();
                $form->getInputFilter()
                    ->get('password_confirm')
                    ->getValidatorChain()
                    ->addValidator(new Identical($post['password']));
            }

            $form->setData($post);
            if ($form->isValid()) {
                $userModel->addData($post);
                if (!empty($post['password'])) {
                    $userModel->setPassword($post['password']);
                }

                $userModel->save();
                $this->flashMessenger()->addSuccessMessage('This user has been saved');
                return $this->redirect()->toRoute('config/user/edit', array('id' => $userId));
            }

            $this->flashMessenger()->addErrorMessage('User can not be saved');
        }

        return array('form' => $form);
    }

    /**
     * This action is used when user has no access to display one page
     *
     * @return \Zend\View\Model\ViewModel|array
     */
    public function forbiddenAction()
    {
        $this->getResponse()->setStatusCode(403);
        $this->getResponse()->isForbidden(true);
        $this->layout()->module = null;
    }
}
