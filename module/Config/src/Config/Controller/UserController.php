<?php

namespace Config\Controller;

use Gc\Mvc\Controller\Action,
    Gc\User,
    Config\Form\UserLogin,
    Config\Form\User as UserForm,
    Config\Form\UserForgotPassword as UserForgotForm,
    Zend\View\Model\ViewModel;

class UserController extends Action
{
    public function indexAction()
    {
        $user_collection = new User\Collection();

        return array('users' => $user_collection->getUsers());
    }

    public function loginAction()
    {
        $this->layout()->setTemplate('layouts/one-page.phtml');
        $login_form = new UserLogin();
        $login_form->setView($this->getLocator()->get('view'));
        $post = $this->getRequest()->post();
        if($this->getRequest()->isPost() and $login_form->isValid($post->toArray()))
        {
            $user_model = new User\Model();
            if($user_id = $user_model->authenticate($post->get('login'), $post->get('password')))
            {
                return $this->redirect()->toRoute('admin');
            }

            $this->flashMessenger()->setNamespace('error')->addMessage('Can not connect');
        }

        return array('form' => $login_form);
    }

    public function forgotPasswordAction()
    {
        $this->layout()->setTemplate('layouts/one-page.phtml');
        $forgot_password_form = new UserForgotForm();
        $forgot_password_form->setView($this->getLocator()->get('view'));
        $post = $this->getRequest()->post();
        if($this->getRequest()->isPost() and $forgot_password_form->isValid($post->toArray()))
        {
            $user_model = new User\Model();
            $user_model->sendForgotPasswordEmail($forgot_password_form->getValue('email'));
            //@TODO send mail to retrieve password
            $this->redirect()->toRoute('admin');
        }

        return array('form' => $forgot_password_form);
    }

    public function logoutAction()
    {
        $this->getAuth()->getStorage()->clear();
        $this->getSession()->clear();
        return $this->redirect()->toRoute('admin');
    }

    public function createAction()
    {
        $form = new UserForm();
        $form->setAction($this->url()->fromRoute('userCreate'));
        $form->passwordRequired();
        $post = $this->getRequest()->post()->toArray();
        if($this->getRequest()->isPost() and $form->isValid($post))
        {
            $user_model = new User\Model();
            $user_model->setData($post);
            $user_model->save();

            $this->flashMessenger()->setNamespace('error')->addMessage('Can not connect');
        }

        return array('form' => $form);
    }

    public function deleteAction()
    {
        $user_id = $this->getRouteMatch()->getParam('id');
        if(!empty($user_id))
        {
            User\Model::fromId($user_id)->delete();
            $this->flashMessenger()->setNamespace('success')->addMessage('User deleted');
        }

        return $this->redirect()->toRoute('userList');
    }

    public function editAction()
    {
        $user_id = $this->getRouteMatch()->getParam('id');
        $user_model = User\Model::fromId($user_id);

        $form = new UserForm();
        $form->setAction($this->url()->fromRoute('userEdit', array('id' => $user_id)));
        $form->loadValues($user_model);
        $post = $this->getRequest()->post()->toArray();

        if(!empty($post['password']))
        {
            $form->passwordRequired();
        }

        if($this->getRequest()->isPost() and $form->isValid($post))
        {

            $user_model->addData($post);
            $user_model->save();

            $this->flashMessenger()->setNamespace('error')->addMessage('Can not connect');
        }

        return array('form' => $form);
    }
}
