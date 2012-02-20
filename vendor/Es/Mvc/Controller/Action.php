<?php
namespace Es\Mvc\Controller;

use Zend\Mvc\Controller\ActionController,
    Zend\Authentication\AuthenticationService,
    Zend\Mvc\MvcEvent,
    Zend\Session\Storage\SessionStorage;

class Action extends ActionController
{
    protected $_auth = NULL;
    protected $_routeMatch = NULL;
    protected $_session = NULL;

    public function execute(MvcEvent $e)
    {
        $this->getRouteMatch();
        $this->init();
        return parent::execute($e);
    }

    public function init()
    {
        $auth = $this->getAuth();
        if(!$auth->hasIdentity() and $this->_routeMatch->getParam('action') != 'login' and $this->_routeMatch->getParam('module') != 'admin-user')
        {
            $this->redirect()->toRoute('login');
        }
    }

    public function getRouteMatch()
    {
        if(empty($this->_routeMatch))
        {
            $this->_routeMatch = $this->getEvent()->getRouteMatch();
        }

        return $this->_routeMatch;
    }
    /**
    *@return Zend_Session
    */
    protected function getSession()
    {
        if($this->_session === NULL)
        {
            $this->_session = new SessionStorage($this->getAuth()->getStorage());
        }

        return $this->_session;
    }

    protected function getAuth()
    {
        if($this->_auth === NULL)
        {
            $this->_auth = new AuthenticationService();
        }

        return $this->_auth;
    }
}

