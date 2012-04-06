<?php

namespace Config\Controller;

use Es\Mvc\Controller\Action,
    Application\Model\User,
    Config\Form\UserLogin;

class UserController extends Action
{
    public function indexAction()
    {

    }

    public function loginAction()
    {
        $login_form = new UserLogin();
        if($this->getRequest()->isPost())
        {
            $post = $this->getRequest()->post();
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

    }

    public function aclAction()
    {

    }
}
