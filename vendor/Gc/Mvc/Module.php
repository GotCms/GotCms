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
 * @category    Gc
 * @package     Library
 * @subpackage  Mvc
 * @author      Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license     GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link        http://www.got-cms.com
 */

namespace Gc\Mvc;

use Gc\Core\Config as GcConfig,
    Gc\Session\SaveHandler\DbTableGateway as SessionTableGateway,
    Gc\Registry,
    Zend,
    Zend\Db\Adapter\Adapter as DbAdapter,
    Zend\Db\TableGateway\TableGateway,
    Zend\Config\Reader\Ini,
    Zend\EventManager\Event,
    Zend\I18n\Translator\Translator,
    Zend\ModuleManager\ModuleManager,
    Zend\Mvc\ModuleRouteListener,
    Zend\Session\Config\SessionConfig,
    Zend\Session\Container as SessionContainer,
    Zend\Session\SaveHandler\DbTableGatewayOptions,
    Zend\Session\SessionManager;
/**
 * Generic Module
 */
class Module
{
    /**
     * Module configuration
     * @var array
     */
    protected $_config;

    /**
     * On boostrap event
     * @param Event $e
     * @return void
     */
    public function onBootstrap(Event $e)
    {
        if(!Registry::isRegistered('Translator'))
        {
            $translator = $e->getApplication()->getServiceManager()->get('translator');
            $translator->addTranslationFilePattern('phparray', GC_APPLICATION_PATH . '/data/translate/', '%s.php', 'default');

            if(Registry::isRegistered('Db'))
            {
                $translator->setLocale(GcConfig::getValue('locale'));
            }

            \Zend\Validator\AbstractValidator::setDefaultTranslator($translator);
            Registry::set('Translator', $translator);
        }
    }

    /**
     * Get autoloader config
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
            if(empty($config['router']['routes']))
            {
                $config['router']['routes'] = array();
            }

            if(!empty($routes['routes']))
            {
                $config['router']['routes'] += $routes['routes'];
            }

            $this->_config = $config;
        }

        return $this->_config;
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

    /**
     * initiliaze database connexion for every modules
     * @param ModuleManager $module_manager
     * @return void
     */
    public function init(ModuleManager $module_manager)
    {
        if(!Registry::isRegistered('Configuration'))
        {
            $config_paths = $module_manager->getEvent()->getConfigListener()->getOptions()->getConfigGlobPaths();
            if(!empty($config_paths))
            {
                $config = array();
                foreach($config_paths as $path)
                {
                    foreach(glob(realpath(__DIR__.'/../../../').'/'.$path, GLOB_BRACE) as $filename)
                    {
                        $config += include_once($filename);
                    }
                }

                if(!empty($config['db']))
                {
                    $db_adapter = new DbAdapter($config['db']);
                    \Zend\Db\TableGateway\Feature\GlobalAdapterFeature::setStaticAdapter($db_adapter);

                    Registry::set('Configuration', $config);
                    Registry::set('Db', $db_adapter);

                    $session_handler = GcConfig::getValue('session_handler');
                    $session_manager = SessionContainer::getDefaultManager();
                    $session_config = $session_manager->getConfig();
                    $session_config->setStorageOption('gc_maxlifetime', GcConfig::getValue('session_lifetime'));
                    $session_config->setStorageOption('cookie_path', GcConfig::getValue('cookie_path'));
                    $session_config->setStorageOption('cookie_domain', GcConfig::getValue('cookie_domain'));

                    if($session_handler == GcConfig::SESSION_DATABASE)
                    {
                        $tablegateway_config =  new DbTableGatewayOptions(array(
                            'idColumn'   => 'id',
                            'nameColumn' => 'name',
                            'modifiedColumn' => 'updated_at',
                            'lifetimeColumn' => 'lifetime',
                            'dataColumn' => 'data',
                        ));

                        $session_table = new SessionTableGateway(new TableGateway('core_session', $db_adapter), $tablegateway_config);
                        $session_manager->setSaveHandler($session_table)->start();
                    }
                }
            }
        }
    }
}
