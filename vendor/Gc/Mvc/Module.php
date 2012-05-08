<?php

namespace Gc\Mvc;

use Zend,
    Zend\Config\Reader\Ini,
    Zend\View,
    Zend\Module\Manager,
    Zend\EventManager\Event,
    Zend\EventManager\StaticEventManager,
    Zend\Module\Consumer\AutoloaderProvider;

class Module implements AutoloaderProvider
{
    protected $_config;

    public function init(Manager $moduleManager)
    {
        $events = $moduleManager->events();
        $sharedEvents = $events->getSharedManager();
        $sharedEvents->attach('bootstrap', 'bootstrap', array($this, 'initializeView'), 100);
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                $this->_getDir() . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    $this->_getNamespace() => $this->_getDir() . '/src/' . $this->_getNamespace(),
                ),
            ),
        );
    }

    public function getConfig()
    {
        if(empty($this->_config))
        {
            $config = include $this->_getDir() . '/config/module.config.php';
            $ini = new Ini();
            $routes = $ini->fromFile($this->_getDir() . '/config/routes.ini');
            $routes = $routes['production'];
            if(empty($config['di']['instance']['Zend\Mvc\Router\RouteStackInterface']))
            {
                $config['di']['instance']['Zend\Mvc\Router\RouteStackInterface'] = array('parameters' => array('routes' => array()));
            }

            if(!empty($routes['routes']))
            {
                $config['di']['instance']['Zend\Mvc\Router\RouteStackInterface']['parameters']['routes'] += $routes['routes'];
            }

            $this->_config = $config;
        }

        return $this->_config;
    }

    public function initializeView(Event $e)
    {
        $app          = $e->getParam('application');
        $basePath     = $app->getRequest()->getBasePath();
        $locator      = $app->getLocator();
        $jsonStrategy = $locator->get('Zend\View\Strategy\JsonStrategy');
        $view         = $locator->get('Zend\View\View');
        $view->events()->attach($jsonStrategy, 100);
        //@TODO change to module.config.php
        Zend\Db\TableGateway\StaticAdapterTableGateway::setStaticAdapter($locator->get('Zend\Db\Adapter\Adapter'));
        $renderer     = $locator->get('Zend\View\Renderer\PhpRenderer');
        $renderer->doctype()->setDoctype('HTML5');
        $renderer->plugin('basePath')->setBasePath($basePath);
    }

    protected function _getDir()
    {
        return $this->_directory;
    }

    protected function _getNamespace()
    {
        return $this->_namespace;
    }
}
