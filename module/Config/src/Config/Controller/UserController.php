<?php

namespace Config\Controller;

use Gc\Mvc\Controller\Action,
    Gc\User,
    Config\Form\UserLogin,
    Config\Form\User as UserForm;

class UserController extends Action
{
    public function indexAction()
    {

    }

    public function loginAction()
    {
        $login_form = new UserLogin();
        $post = $this->getRequest()->post();
        if($this->getRequest()->isPost() and $login_form->isValid($post->toArray()))
        {
            $user_table = new User\Model();
            if($user_id = $user_table->authenticate($post->get('email'), $post->get('password')))
            {
                return $this->redirect()->toRoute('admin');
            }

            $this->flashMessenger()->setNamespace('error')->addMessage('Can not connect');
        }

        return array('form' => $login_form);
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
        if($this->getRequest()->isPost())
        {
            $user_id = $this->getRequest()->post()->get('id');
            if(!empty($user_id) and $this->getAuth()->getId() == $user_id)
            {
                User\Model::fromId($user_id)->delete();
                $this->flashMessenger()->setNamespace('success')->addMessage('User deleted');
            }
        }

        return $this->redirect()->toRoute('admin');
    }

    public function editAction()
    {
        $user_id = $this->getRouteMatch()->getParam('id');
        $user_model = User\Model::fromId($user_id);

        $form = new UserForm();
        $post = $this->getRequest()->post()->toArray();
        if($this->getRequest()->isPost() and $form->isValid($post))
        {
            $user_table = new User\Model();
            $user_table->setData($post);
            $user_table->save();

            $this->flashMessenger()->setNamespace('error')->addMessage('Can not connect');
        }

        return array('form' => $form);
    }

    public function aclAction()
    {

    }
}
