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
            if(empty($config['di']['instance']['Zend\Mvc\Router\SimpleRouteStack']))
            {
                var_dump($config['di']);
                $config['di']['instance']['Zend\Mvc\Router\SimpleRouteStack'] = array(
                    'instantiator' => array(
                        'Zend\Mvc\Router\Http\TreeRouteStack',
                        'factory'
                    ),
                    'parameters' => array(
                        'routes' => array()
                    )
                );
            }

            if(!empty($routes['routes']))
            {
                $config['di']['instance']['Zend\Mvc\Router\SimpleRouteStack']['parameters']['routes'] += $routes['routes'];
            }

            $this->_config = $config;
        }

        return $this->_config;
    }

    public function initializeView(Event $e)
    {
        $app          = $e->getParam('application');
        $locator      = $app->getLocator();
        $config       = $e->getParam('config');
        $view         = $this->getView($app);

        //@TODO change to module.config.php
        Zend\Db\Table\AbstractTable::setDefaultAdapter($locator->get('Zend\Db\Adapter\Pdo\Pgsql'));

        $viewListener = $this->getViewListener($view, $config);
        $app->events()->attachAggregate($viewListener);
        $events       = StaticEventManager::getInstance();
        $viewListener->registerStaticListeners($events, $locator);
    }

    protected function getViewListener($view, $config)
    {
        if ($this->_viewListener instanceof ListenerAggregate) {
            return $this->_viewListener;
        }

        $class = $this->_getNamespace().'\View\Listener';

        $viewListener = new $class($view, $config->layout);
        $viewListener->setDisplayExceptionsFlag($config->display_exceptions);

        $this->_viewListener = $viewListener;
        return $viewListener;
    }

    protected function getView($app)
    {
        if ($this->_view) {
            return $this->_view;
        }

        $locator = $app->getLocator();
        $view    = $locator->get('view');

        // Set up view helpers
        $view->plugin('url')->setRouter($app->getRouter());
        $view->doctype()->setDoctype('HTML5');

        $basePath = $app->getRequest()->getBasePath();
        $view->plugin('basePath')->setBasePath($basePath);

        $this->_view = $view;
        return $view;
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
