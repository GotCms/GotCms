<?php
/**
 * This source file is part of Got CMS.
 *
 * Got CMS is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Got CMS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License along
 * with Got CMS. If not, see <http://www.gnu.org/licenses/lgpl-3.0.html>.
 *
 * PHP Version >=5.3
 *
 * @category    Gc
 * @package     Library
 * @subpackage  Mvc
 * @author      Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license     GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link        http://www.got-cms.com
 */

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
    /**
     * @var array
     */
    protected $_config;

    /**
     * Initialize module
     * @param \Zend\Module\Manager $moduleManager
     * @return void
     */
    public function init(Manager $moduleManager)
    {
        $events = $moduleManager->events();
        $sharedEvents = $events->getSharedManager();
        $sharedEvents->attach('bootstrap', 'bootstrap', array($this, 'initializeView'), 100);
    }

    /**
     * get autoloader config
     * @return array
     */
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

    /**
     * Get module configuration
     * @return array
     */
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

    /**
     * Initialize view
     * @param \Zend\EventManager\Event $e
     * @return void
     */
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

    /**
     * Get module dir
     * @return string
     */
    protected function _getDir()
    {
        return $this->_directory;
    }

    /**
     * get module namespace
     * @return string
     */
    protected function _getNamespace()
    {
        return $this->_namespace;
    }
}
