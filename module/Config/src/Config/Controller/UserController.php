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
 * @category Controller
 * @package  Config\Controller
 * @author   Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license  GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link     http://www.got-cms.com
 */

namespace Config\Controller;

use Config\Form\UserLogin,
    Config\Form\User as UserForm,
    Config\Form\UserForgotPassword as UserForgotForm,
    Gc\Mvc\Controller\Action,
    Gc\User,
    Zend\Http\Request,
    Zend\View\Model\ViewModel;

class UserController extends Action
{
    /**
     * Contains information about acl
     * @var array $_acl_page
     */
    protected $_acl_page = array('resource' => 'Config', 'permission' => 'user');

    /**
     * List all roles
     * @return \Zend\View\Model\ViewModel|array
     */
    public function indexAction()
    {
        $user_collection = new User\Collection();

        return array('users' => $user_collection->getUsers());
    }

    /**
     * Login user
     * @return \Zend\View\Model\ViewModel|array
     */
    public function loginAction()
    {
        $this->layout()->setTemplate('layouts/one-page.phtml');
        $login_form = new UserLogin();

        $post = $this->getRequest()->getPost();
        if($this->getRequest()->isPost() and $login_form->setData($post->toArray()) and $login_form->isValid())
        {
            $user_model = new User\Model();
            $redirect = $login_form->getValue('redirect');
            if($user_id = $user_model->authenticate($post->get('login'), $post->get('password')))
            {
                if(!empty($redirect))
                {
                    return $this->redirect()->toUrl(base64_decode($redirect));
                }

                return $this->redirect()->toRoute('admin');
            }

            $this->flashMessenger()->setNamespace('error')->addMessage('Can not connect');
            return $this->redirect()->toRoute('userLogin', array('redirect' => $redirect));
        }

        $login_form->get('redirect')->setValue($this->getRouteMatch()->getParam('redirect'));

        return array('form' => $login_form);
    }

    /**
     * Forgot password action
     * @return \Zend\View\Model\ViewModel|array
     */
    public function forgotPasswordAction()
    {
        $this->layout()->setTemplate('layouts/one-page.phtml');
        $forgot_password_form = new UserForgotForm();
        $post = $this->getRequest()->getPost();
        if($this->getRequest()->isPost() and $forgot_password_form->isValid($post->toArray()))
        {
            $user_model = new User\Model();
            $user_model->sendForgotPasswordEmail($forgot_password_form->getValue('email'));
            //@TODO send mail to retrieve password
            $this->redirect()->toRoute('admin');
        }

        return array('form' => $forgot_password_form);
    }

    /**
     * Logout action
     * @return \Zend\View\Model\ViewModel|array
     */
    public function logoutAction()
    {
        $this->getSession()->getManager()->destroy();
        return $this->redirect()->toRoute('admin');
    }

    /**
     * Craete user
     * @return \Zend\View\Model\ViewModel|array
     */
    public function createAction()
    {
        $form = new UserForm();
        $form->setAttribute('action', $this->url()->fromRoute('userCreate'));
        $form->passwordRequired();
        $post = $this->getRequest()->getPost()->toArray();
        if($this->getRequest()->isPost())
        {
            $form->setData($post);
            $form->getInputFilter()->get('password_confirm')->getValidatorChain()->addValidator(new \Zend\Validator\Identical($post['password']));

            if($form->isValid())
            {
                $user_model = new User\Model();
                $user_model->setData($post);
                $user_model->setPassword($post['password']);
                $user_model->save();
                $this->flashMessenger()->setNamespace('success')->addMessage('User saved!');

                return $this->redirect()->toRoute('userEdit', array('id' => $user_model->getId()));
            }

            $this->flashMessenger()->setNamespace('error')->addMessage('User can not be saved!');
        }

        return array('form' => $form);
    }

    /**
     * Delete user
     * @return \Zend\View\Model\ViewModel|array
     */
    public function deleteAction()
    {
        $user = User\Model::fromId($this->getRouteMatch()->getParam('id'));
        if(empty($user))
        {
            if($user->delete())
            {
                return $this->_returnJson(array('success' => TRUE, 'message' => 'User deleted!'));
            }
        }

        return $this->_returnJson(array('success' => FALSE, 'message' => 'User does not exists!'));
    }

    /**
     * Edit user
     * @return \Zend\View\Model\ViewModel|array
     */
    public function editAction()
    {
        $user_id = $this->getRouteMatch()->getParam('id');
        $user_model = User\Model::fromId($user_id);

        $form = new UserForm();
        $form->setAttribute('action', $this->url()->fromRoute('userEdit', array('id' => $user_id)));
        $form->loadValues($user_model);
        $post = $this->getRequest()->getPost()->toArray();
        if($this->getRequest()->isPost())
        {
            if(!empty($post['password']))
            {
                $form->passwordRequired();
                $form->getInputFilter()->get('password_confirm')->getValidatorChain()->addValidator(new \Zend\Validator\Identical($post['password']));
            }

            $form->setData($post);
            if($form->isValid())
            {
                $user_model->addData($post);
                if(!empty($post['password']))
                {
                    $user_model->setPassword($post['password']);
                }

                $user_model->save();
                $this->flashMessenger()->setNamespace('success')->addMessage('User has been edited!');
                return $this->redirect()->toRoute('userEdit', array('id' => $user_id));
            }

            $this->flashMessenger()->setNamespace('error')->addMessage('User can not be saved!');
        }

        return array('form' => $form);
    }

    /**
     * This action is used when user has no access to display one page
     * @return \Zend\View\Model\ViewModel|array
     */
    public function forbiddenAction()
    {
        $this->getResponse()->setStatusCode(403);
        $this->getResponse()->isForbidden(TRUE);
        $this->layout()->module = NULL;
    }
}
