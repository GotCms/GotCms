<?php
class Login_ConnectController extends Es_Controller_Action
{
    /**
    *
    * @var Es_Model_DbTable_User
    */
    protected $_table;

    public function init()
    {
        $this->_table = new Es_Model_DbTable_User();
    }

    public function indexAction() {
        $this->view->title = "Log in";
        $request = $this->getRequest();

        if($request->isPost())
        {
            $f = new Zend_Filter_StripTags();
            $email = $f->filter($this->_request->getPost('email'));
            $password = $f->filter($this->_request->getPost('password'));

            if(empty($email) || empty($password))
            {
                $this->_redirect('login');
            }
            else
            {
                if ($this->_table->authenticate($email, $password))
                {
                    $this->_redirect('admin');
                }
                else
                {
                    $this->_helper->flashMessenger->setNamespace('error')->addMessage('Login failed.');
                    $this->_redirect('login');
                }
            }
        }
    }
}
