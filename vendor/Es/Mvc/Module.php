<?php

namespace Es\Mvc;

use Zend,
    Zend\View,
    Zend\Module\Manager,
    Zend\EventManager\StaticEventManager,
    Zend\Module\Consumer\AutoloaderProvider;

class Module implements AutoloaderProvider
{
    protected $_view;
    protected $_viewListener;
    protected $_directory;

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
        $config = include $this->_getDir() . '/config/module.config.php';
        $config['di']['instance']['Zend\Db\Adapter\Pgsql'] = Zend\Registry::get('database_connection');

        return $config;
    }

    public function initializeView($e)
    {
        $app          = $e->getParam('application');
        $locator      = $app->getLocator();
        $config       = $e->getParam('config');
        $view         = $this->getView($app);
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

        $namespace_layout_name = strtolower($this->_getNamespace()).'-layout';
        $layout = empty($config->$namespace_layout_name) ? $config->layout : $config->$namespace_layout_name;

        $class = $this->_getNamespace().'\View\Listener';

        $viewListener       = new $class($view, $layout);
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
        $url     = $view->plugin('url');
        $url->setRouter($app->getRouter());

        $view->plugin('headTitle')->setSeparator(' - ')
                                  ->setAutoEscape(false)
                                  ->append('ZF2 Skeleton Application');

        $basePath = $app->getRequest()->getBaseUrl();

        $view->plugin('headLink')->appendStylesheet($basePath . '/css/bootstrap.min.css');

        $html5js = '<script src="' . $basePath . 'js/html5.js"></script>';
        $view->plugin('placeHolder')->__invoke('html5js')->set($html5js);
        $favicon = '<link rel="shortcut icon" href="' . $basePath . 'images/favicon.ico">';
        $view->plugin('placeHolder')->__invoke('favicon')->set($favicon);

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
