<?php
namespace Es\Mvc\Controller;

use Zend\Mvc\Controller\ActionController,
    Zend\Session\Storage\SessionStorage,
    Zend\Mvc\MvcEvent;

class Action extends ActionController
{
    protected $_session = NULL;
    protected $_routeMatch = NULL;

    public function execute(MvcEvent $e)
    {
        $this->getRouteMatch();
        $this->init();
        return parent::execute($e);
    }

    public function init(){}

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
            $this->_session = new SessionStorage();
        }

        return $this->_session;
    }
}

