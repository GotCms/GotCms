<?php
/**
 * This source file is part of GotCms.
 *
 * GotCms is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * GotCms is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License along
 * with GotCms. If not, see <http://www.gnu.org/licenses/lgpl-3.0.html>.
 *
 * PHP Version >=5.3
 *
 * @category   Gc
 * @package    Library
 * @subpackage Mvc
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Gc\Mvc;

use Gc\Core\Config as GcConfig;
use Gc\Session\SaveHandler\DbTableGateway as SessionTableGateway;
use Gc\Registry;
use Gc\Module\Collection as ModuleCollection;
use Zend;
use Zend\Db\Adapter\Adapter as DbAdapter;
use Zend\Db\TableGateway\TableGateway;
use Zend\Config\Reader\Ini;
use Zend\EventManager\Event;
use Zend\I18n\Translator\Translator;
use Zend\ModuleManager\ModuleManager;
use Zend\Mvc\ModuleRouteListener;
use Zend\Session\Config\SessionConfig;
use Zend\Session\Container as SessionContainer;
use Zend\Session\SaveHandler\DbTableGatewayOptions;
use Zend\Session\SessionManager;

/**
 * Generic Module
 *
 * @category   Gc
 * @package    Library
 * @subpackage Mvc
 */
abstract class Module
{
    /**
     * Module directory path
     *
     * @var string
     */
    protected $directory = null;

    /**
     * Module namespace
     *
     * @var string
     */
    protected $namespace = null;

    /**
     * Module configuration
     *
     * @var array
     */
    protected $config;

    /**
     * On boostrap event
     *
     * @param Event $event Event
     *
     * @return void
     */
    public function onBootstrap(Event $event)
    {
        if (!Registry::isRegistered('Translator')) {
            $translator = $event->getApplication()->getServiceManager()->get('translator');
            $translator->addTranslationFilePattern(
                'phparray',
                GC_APPLICATION_PATH . '/data/translation/',
                '%s.php',
                'default'
            );

            if (Registry::isRegistered('Db')) {
                $translator->setLocale(GcConfig::getValue('locale'));
            }

            \Zend\Validator\AbstractValidator::setDefaultTranslator($translator);
            Registry::set('Translator', $translator);

            $uri       = '';
            $uri_class = $event->getRequest()->getUri();
            if ($uri_class->getScheme()) {
                $uri .= $uri_class->getScheme() . ':';
            }

            if ($uri_class->getHost() !== null) {
                $uri .= '//';
                $uri .= $uri_class->getHost();
                if ($uri_class->getPort() and $uri_class->getPort() != 80) {
                    $uri .= ':' . $uri_class->getPort();
                }
            }

            $event->getRequest()->setBasePath($uri);
        }
    }

    /**
     * Get autoloader config
     *
     * @return array
     */
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                $this->getDir() . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    $this->getNamespace() => $this->getDir() . '/src/' . $this->getNamespace(),
                ),
            ),
        );
    }

    /**
     * Get module configuration
     *
     * @return array
     */
    public function getConfig()
    {
        if (empty($this->config)) {
            $config = include $this->getDir() . '/config/module.config.php';
            $ini    = new Ini();
            $routes = $ini->fromFile($this->getDir() . '/config/routes.ini');
            $routes = $routes['production'];
            if (empty($config['router']['routes'])) {
                $config['router']['routes'] = array();
            }

            if (!empty($routes['routes'])) {
                $config['router']['routes'] += $routes['routes'];
            }

            if (Registry::isRegistered('Db')) {
                if (isset($config['view_manager']['display_exceptions']) and GcConfig::getValue('debug_is_active')) {
                    $config['view_manager']['display_not_found_reason'] = true;
                    $config['view_manager']['display_exceptions']       = true;
                }
            }

            $this->config = $config;
        }

        return $this->config;
    }

    /**
     * Get module dir
     *
     * @return string
     */
    protected function getDir()
    {
        return $this->directory;
    }

    /**
     * get module namespace
     *
     * @return string
     */
    protected function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * initiliaze database connexion for every modules
     *
     * @param ModuleManager $module_manager Module manager
     *
     * @return void
     */
    public function init(ModuleManager $module_manager)
    {
        if (!Registry::isRegistered('Configuration')) {
            $config_paths = $module_manager->getEvent()->getConfigListener()->getOptions()->getConfigGlobPaths();
            if (!empty($config_paths)) {
                $config = array();
                foreach ($config_paths as $path) {
                    foreach (glob(realpath(__DIR__ . '/../../../') . '/' . $path, GLOB_BRACE) as $filename) {
                        $config += include $filename;
                    }
                }

                if (!empty($config['db'])) {
                    $db_adapter = new DbAdapter($config['db']);
                    \Zend\Db\TableGateway\Feature\GlobalAdapterFeature::setStaticAdapter($db_adapter);

                    Registry::set('Configuration', $config);
                    Registry::set('Db', $db_adapter);

                    $session_manager = SessionContainer::getDefaultManager();
                    $session_config  = $session_manager->getConfig();
                    $session_config->setStorageOption('gc_maxlifetime', GcConfig::getValue('session_lifetime'));
                    $session_config->setStorageOption('cookie_path', GcConfig::getValue('cookie_path'));
                    $session_config->setStorageOption('cookie_domain', GcConfig::getValue('cookie_domain'));

                    if (GcConfig::getValue('session_handler') == GcConfig::SESSION_DATABASE) {
                        $tablegateway_config = new DbTableGatewayOptions(
                            array(
                                'idColumn'   => 'id',
                                'nameColumn' => 'name',
                                'modifiedColumn' => 'updated_at',
                                'lifetimeColumn' => 'lifetime',
                                'dataColumn' => 'data',
                            )
                        );

                        $session_table = new SessionTableGateway(
                            new TableGateway('core_session', $db_adapter),
                            $tablegateway_config
                        );
                        $session_manager->setSaveHandler($session_table)->start();
                    }

                    //Initialize Observers
                    $module_collection = new ModuleCollection();
                    $modules           = $module_collection->getModules();
                    foreach ($modules as $module) {
                        $class_name = sprintf('\\Modules\\%s\\Observer', $module->getName());
                        if (class_exists($class_name)) {
                            $object = new $class_name();
                            $object->init();
                        }
                    }
                }
            }
        }
    }
}
