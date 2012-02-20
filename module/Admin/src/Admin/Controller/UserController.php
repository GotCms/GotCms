<?php

namespace Admin\Controller;

use Es\Mvc\Controller\Action,
    Application\Model\User,
    Admin\Form\UserLogin;

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
                $this->redirect()->toRoute('admin');
            }
            else
            {
                //ERROR
            }
        }

        return array('form' => $login_form);
    }

    public function logoutAction()
    {
        $this->getAuth()->getStorage()->clear();
        $this->getSession()->clear();
        $this->redirect()->toRoute('admin');
    }

    public function createAction()
    {

    }

    public function deleteAction()
    {

    }

    public function editAction()
    {

    }
}
