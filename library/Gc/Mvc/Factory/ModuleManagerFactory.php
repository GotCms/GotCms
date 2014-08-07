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
 * @subpackage Mvc\Factory
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Gc\Mvc\Factory;

use Gc\Module\Collection as ModuleCollection;
use Zend\ModuleManager\Listener;
use Zend\ModuleManager\ModuleEvent;
use Zend\ModuleManager\ModuleManager;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\Loader\AutoloaderFactory;

/**
 * Create modules via service
 *
 * @category   Gc
 * @package    Library
 * @subpackage Mvc\Factory
 */
class ModuleManagerFactory implements FactoryInterface
{
    /**
     * Creates and returns the module manager
     *
     * Instantiates the default module listeners, providing them configuration
     * from the "module_listener_options" key of the ApplicationConfig
     * service. Also sets the default config glob path.
     *
     * Module manager is instantiated and provided with an EventManager, to which
     * the default listener aggregate is attached. The ModuleEvent is also created
     * and attached to the module manager.
     *
     * @param ServiceLocatorInterface $serviceLocator Service Manager
     *
     * @return ModuleManager
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $moduleCollection = new ModuleCollection();
        $modules          = $moduleCollection->getModules();
        $array            = array();
        $autoloader       = AutoloaderFactory::getRegisteredAutoloader(AutoloaderFactory::STANDARD_AUTOLOADER);

        foreach ($modules as $module) {
            $array[] = $module->getName();
            $path    = GC_APPLICATION_PATH . '/library/Modules/' . $module->getName();
            if (file_exists($path) === false) {
                $path = GC_APPLICATION_PATH . '/extensions/Modules/' . $module->getName();
            }

            $autoloader->registerNamespace(
                $module->getName(),
                $path
            );
        }

        $autoloader->register();

        $application   = $serviceLocator->get('Application');
        $configuration = $serviceLocator->get('ApplicationConfig');

        $configuration['module_listener_options']['module_paths'] = array(
            './library/Modules',
            './extensions/Modules'
        );

        $listenerOptions  = new Listener\ListenerOptions($configuration['module_listener_options']);
        $defaultListeners = new Listener\DefaultListenerAggregate($listenerOptions);
        $serviceListener  = new Listener\ServiceListener($serviceLocator);

        $this->prepareServices($serviceListener, $serviceLocator);

        $moduleManager = new ModuleManager($array, $application->getEventManager());
        $moduleManager->getEventManager()->attachAggregate($defaultListeners);
        $moduleManager->getEventManager()->attachAggregate($serviceListener);
        $moduleManager->loadModules();

        $config = $moduleManager->getEvent()->getConfigListener()->getMergedConfig(false);
        $this->prepareConfig($serviceLocator, $config);

        foreach ($moduleManager->getLoadedModules() as $module) {
            if (method_exists($module, 'onBootstrap')) {
                $module->onBootstrap($application->getMvcEvent());
            }
        }

        return $moduleManager;
    }

    /**
     * Prepare services
     *
     * @param Listener\ServiceListener $serviceListener Service Listener
     * @param ServiceManager           $serviceLocator  Service Manager
     *
     * @return void
     */
    protected function prepareServices($serviceListener, $serviceLocator)
    {
        $serviceListener->addServiceManager(
            $serviceLocator,
            'service_manager',
            'Zend\ModuleManager\Feature\ServiceProviderInterface',
            'getServiceConfig'
        );
        $serviceListener->addServiceManager(
            'ControllerLoader',
            'controllers',
            'Zend\ModuleManager\Feature\ControllerProviderInterface',
            'getControllerConfig'
        );
        $serviceListener->addServiceManager(
            'ControllerPluginManager',
            'controller_plugins',
            'Zend\ModuleManager\Feature\ControllerPluginProviderInterface',
            'getControllerPluginConfig'
        );
        $serviceListener->addServiceManager(
            'ViewHelperManager',
            'view_helpers',
            'Zend\ModuleManager\Feature\ViewHelperProviderInterface',
            'getViewHelperConfig'
        );
        $serviceListener->addServiceManager(
            'ValidatorManager',
            'validators',
            'Zend\ModuleManager\Feature\ValidatorProviderInterface',
            'getValidatorConfig'
        );
        $serviceListener->addServiceManager(
            'FilterManager',
            'filters',
            'Zend\ModuleManager\Feature\FilterProviderInterface',
            'getFilterConfig'
        );
        $serviceListener->addServiceManager(
            'FormElementManager',
            'form_elements',
            'Zend\ModuleManager\Feature\FormElementProviderInterface',
            'getFormElementConfig'
        );
        $serviceListener->addServiceManager(
            'RoutePluginManager',
            'route_manager',
            'Zend\ModuleManager\Feature\RouteProviderInterface',
            'getRouteConfig'
        );
        $serviceListener->addServiceManager(
            'SerializerAdapterManager',
            'serializers',
            'Zend\ModuleManager\Feature\SerializerProviderInterface',
            'getSerializerConfig'
        );
        $serviceListener->addServiceManager(
            'HydratorManager',
            'hydrators',
            'Zend\ModuleManager\Feature\HydratorProviderInterface',
            'getHydratorConfig'
        );
        $serviceListener->addServiceManager(
            'InputFilterManager',
            'input_filters',
            'Zend\ModuleManager\Feature\InputFilterProviderInterface',
            'getInputFilterConfig'
        );
    }

    /**
     * Prepare services
     *
     * @param ServiceManager $serviceLocator Service Manager
     * @param array          $config         Configuration
     *
     * @return void
     */
    protected function prepareConfig($serviceLocator, $config)
    {
        if (isset($config['router']['routes'])) {
            $router = $serviceLocator->get('Router');
            $routes = isset($config['router']['routes']) ? $config['router']['routes'] : array();
            $router->getRoute('module')->addRoutes($routes);
        }

        if (is_array($config) && isset($config['view_manager'])) {
            $viewManagerConfig = $config['view_manager'];
            $templatePathStack = $serviceLocator->get('ViewTemplatePathStack');
            $coreConfig        = $serviceLocator->get('CoreConfig');
            $templatePathStack->setUseStreamWrapper((bool) $coreConfig->getValue('stream_wrapper_is_active'));
            if (is_array($viewManagerConfig)) {
                if (isset($viewManagerConfig['template_path_stack'])) {
                    $templatePathStack->addPaths($viewManagerConfig['template_path_stack']);
                }
                if (isset($viewManagerConfig['default_template_suffix'])) {
                    $templatePathStack->setDefaultSuffix($viewManagerConfig['default_template_suffix']);
                }
            }
        }
    }
}
