<?php
class Es_Controller_Action extends Zend_Controller_Action
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
            $this->_session = new Zend_Session();
        }

        return $this->_session;
    }
}

