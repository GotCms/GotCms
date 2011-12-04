<?php
class Es_Controller_Action extends Zend_Controller_Action
{
    protected $_session = NULL;
    protected $_db;

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

    /**
    * @return Zend_Db_Adapter_Abstract
    */
    public function getAdapter()
    {
        if(empty($this->_db))
        {
            $this->_db = Zend_Registry::get('Zend_Db');
        }

        return $this->_db;
    }
}

