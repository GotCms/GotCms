<?php
namespace Es\Mvc\Controller;

use Zend\Mvc\Controller\ActionController,
    Zend\Session;

class Action extends ActionController
{
    protected $_session = NULL;

    public function preDispatch()
    {
        Zend_Session::start();
        $auth = Zend_Auth::getInstance();
        /*if(!$auth->hasIdentity() and $this->getRequest()->getModuleName() != 'login')
        {
            return $this->_redirect('admin/login');
        }*/
    }

    /**
    *@return Zend_Session
    */
    protected function getSession()
    {
        if($this->_session === NULL)
        {
            $this->_session = new Session();
        }

        return $this->_session;
    }
}

