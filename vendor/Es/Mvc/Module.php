<?php

namespace Es\Mvc;

use Zend,
    Zend\Config,
    Zend\View,
    Zend\Module\Manager,
    Zend\EventManager\Event,
    Zend\EventManager\StaticEventManager,
    Zend\Module\Consumer\AutoloaderProvider;

class Module implements AutoloaderProvider
{
    protected $_view;
    protected $_viewListener;
    protected $_directory;
    protected $_config;

    public function init(Manager $moduleManager)
    {
        $events = StaticEventManager::getInstance();
        $events->attach('bootstrap', 'bootstrap', array($this, 'initializeView'), 100);
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
            $routes = new Config\Ini($this->_getDir() . '/config/routes.ini');
            $routes = $routes->get($_SERVER['APPLICATION_ENV'])->toArray();
            if(empty($config['di']['instance']['Zend\Mvc\Router\RouteStack']))
            {
                $config['di']['instance']['Zend\Mvc\Router\RouteStack'] = array('parameters' => array('routes' => array()));
            }

            if(!empty($routes['routes']))
            {
                $config['di']['instance']['Zend\Mvc\Router\RouteStack']['parameters']['routes'] += $routes['routes'];
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
        //@TODO change to module.config.php
        Zend\Db\Table\AbstractTable::setDefaultAdapter($locator->get('Zend\Db\Adapter\Pdo\Pgsql'));
        $renderer     = $locator->get('Zend\View\Renderer\PhpRenderer');
        $renderer->plugin('url')->setRouter($app->getRouter());
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
