<?php
namespace Gc\Mvc\Controller;

use Gc\User\Model,
    Zend\Mvc\Controller\ActionController,
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

    public function init()
    {

    }

    protected function _construct()
    {
        $auth = $this->getAuth();
        $module = $this->getRouteMatch()->getParam('module');
        $route_name = $this->getRouteMatch()->getMatchedRouteName();
        if(!$auth->hasIdentity())
        {
            if($route_name != 'login' and $route_name != 'renderWebsite')
            {
                return $this->redirect()->toRoute('login');
            }
        }
        else
        {
            $user = Model::fromId($auth->getIdentity()->id);
            \Zend\Registry::set('user', $user);
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
