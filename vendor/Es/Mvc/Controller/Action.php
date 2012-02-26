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
        $this->_construct();
        $this->init();
        return parent::execute($e);
    }

    public function init(){}

    protected function _construct()
    {
        $auth = $this->getAuth();
        $module = $this->getRouteMatch()->getParam('module');
        if(!$auth->hasIdentity() and $this->_routeMatch->getParam('action') != 'login' and $module != 'admin-user')
        {
            $this->redirect()->toRoute('login');
        }

        $this->layout()->module = $module;
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
    * @return SessionStorage
    */
    protected function getSession()
    {
        if($this->_session === NULL)
        {
            $this->_session = new SessionStorage($this->getAuth()->getStorage());
        }

        return $this->_session;
    }

    /**
    * @return AuthenticationService
    */
    protected function getAuth()
    {
        if($this->_auth === NULL)
        {
            $this->_auth = new AuthenticationService();
        }

        return $this->_auth;
    }
}
