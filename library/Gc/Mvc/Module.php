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

use Application\Controller\IndexController as RenderController;
use Gc\Core\Config as CoreConfig;
use Gc\Layout;
use Gc\Session\SaveHandler\DbTableGateway as SessionTableGateway;
use Gc\Registry;
use Gc\Module\Collection as ModuleCollection;
use Zend\Db\Adapter\Adapter as DbAdapter;
use Zend\Db\TableGateway\Feature\GlobalAdapterFeature;
use Zend\Db\TableGateway\TableGateway;
use Zend\Config\Reader\Ini;
use Zend\EventManager\Event;
use Zend\EventManager\EventInterface;
use Zend\Mvc\I18n\Translator;
use Zend\ModuleManager\ModuleManager;
use Zend\Mvc\MvcEvent;
use Zend\Session\SessionManager;
use Zend\Session\Config\SessionConfig;
use Zend\Session\Container as SessionContainer;
use Zend\Session\SaveHandler\DbTableGatewayOptions;
use Zend\Validator\AbstractValidator;
use Zend\Uri\Http as Uri;

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

            if (Registry::isRegistered('Db')) {
                $translator->setLocale(CoreConfig::getValue('locale'));

                $event->getApplication()->getEventManager()->attach(
                    MvcEvent::EVENT_RENDER_ERROR,
                    array($this, 'prepareException')
                );
            }

            AbstractValidator::setDefaultTranslator(new Translator($translator));
            Registry::set('Translator', $translator);
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
            if (Registry::isRegistered('Db')) {
                if (isset($config['view_manager']['display_exceptions']) and CoreConfig::getValue('debug_is_active')) {
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
     * @param ModuleManager $moduleManager Module manager
     *
     * @return void
     */
    public function init(ModuleManager $moduleManager)
    {
        if (!Registry::isRegistered('Configuration')) {
            $configPaths = $moduleManager->getEvent()->getConfigListener()->getOptions()->getConfigGlobPaths();
            if (!empty($configPaths)) {
                $config = array();
                foreach ($configPaths as $path) {
                    foreach (glob(realpath(__DIR__ . '/../../../') . '/' . $path, GLOB_BRACE) as $filename) {
                        $config += include $filename;
                    }
                }

                if (!empty($config['db'])) {
                    $dbAdapter = new DbAdapter($config['db']);
                    GlobalAdapterFeature::setStaticAdapter($dbAdapter);

                    Registry::set('Configuration', $config);
                    Registry::set('Db', $dbAdapter);

                    $sessionConfig = new SessionConfig();
                    $sessionConfig->setStorageOption('gc_probability', 1);
                    $sessionConfig->setStorageOption('gc_divisor', 1);
                    $sessionConfig->setStorageOption('save_path', CoreConfig::getValue('session_path'));
                    $sessionConfig->setStorageOption('gc_maxlifetime', CoreConfig::getValue('session_lifetime'));
                    $sessionConfig->setStorageOption('cookie_path', CoreConfig::getValue('cookie_path'));
                    $sessionConfig->setStorageOption('cookie_domain', CoreConfig::getValue('cookie_domain'));
                    SessionContainer::setDefaultManager(new SessionManager($sessionConfig));

                    if (CoreConfig::getValue('session_handler') == CoreConfig::SESSION_DATABASE) {
                        $tablegatewayConfig = new DbTableGatewayOptions(
                            array(
                                'idColumn'   => 'id',
                                'nameColumn' => 'name',
                                'modifiedColumn' => 'updated_at',
                                'lifetimeColumn' => 'lifetime',
                                'dataColumn' => 'data',
                            )
                        );

                        $sessionTable = new SessionTableGateway(
                            new TableGateway('core_session', $dbAdapter),
                            $tablegatewayConfig
                        );

                        $sessionManager = SessionContainer::getDefaultManager();
                        $sessionManager->setSaveHandler($sessionTable)->start();
                    }

                    //Initialize Observers
                    $moduleCollection = new ModuleCollection();
                    $modules          = $moduleCollection->getModules();
                    foreach ($modules as $module) {
                        $className = sprintf('\\Modules\\%s\\Observer', $module->getName());
                        if (class_exists($className)) {
                            $object = new $className();
                            $object->init();
                        }
                    }

                    $sharedEvents = $moduleManager->getEventManager()->getSharedManager();
                    $sharedEvents->attach('Zend\Mvc\Application', MvcEvent::EVENT_ROUTE, array($this, 'checkSsl'), -10);
                }
            }
        }
    }

    /**
     * Initialize Render error event
     *
     * @param Event $event Event
     *
     * @return void
     */
    public function prepareException($event)
    {
        if ($event->getApplication()->getMvcEvent()->getRouteMatch()->getMatchedRouteName() === 'cms') {
            $layout = Layout\Model::fromId(CoreConfig::getValue('site_exception_layout'));
            if (!empty($layout)) {
                $templatePathStack = $event->getApplication()->getServiceManager()->get(
                    'Zend\View\Resolver\TemplatePathStack'
                );
                $templatePathStack->setUseStreamWrapper(true);
                file_put_contents($templatePathStack->resolve(RenderController::LAYOUT_NAME), $layout->getContent());
            }
        }
    }

    /**
     * Check if ssl is forced or not
     *
     * @param Zend\EventManager\EventInterface $event Mvc event
     *
     * @return null|Zend\Http\PhpEnvironment\Response
     */
    public function checkSsl(EventInterface $event)
    {
        $matchedRouteName = $event->getRouteMatch()->getMatchedRouteName();
        $request          = $event->getRequest();
        $uri              = $request->getUri();

        if ($matchedRouteName === 'cms') {
            if ($uri->getScheme() === 'https' or CoreConfig::getValue('force_frontend_ssl')) {
                $newUri = new Uri(CoreConfig::getValue('secure_frontend_base_path'));
                $newUri->setScheme('https');
            } else {
                $newUri = new Uri(CoreConfig::getValue('unsecure_frontend_base_path'));
            }
        } else {
            if ($uri->getScheme() === 'https' or CoreConfig::getValue('force_backend_ssl')) {
                $newUri = new Uri(CoreConfig::getValue('secure_backend_base_path'));
                $newUri->setScheme('https');
            } else {
                $newUri = new Uri(CoreConfig::getValue('unsecure_backend_base_path'));
            }
        }

        if (!empty($newUri) and $newUri->isValid() and
            ($newUri->getHost() != '' and $uri->getHost() != $newUri->getHost()) or
            ($newUri->getScheme() != '' and $uri->getScheme() != $newUri->getScheme())
        ) {
            $uri->setPort($newUri->getPort());
            if ($newUri->getHost() != '') {
                $uri->setHost($newUri->getHost());
            }

            if ($newUri->getScheme() != '') {
                $uri->setScheme($newUri->getScheme());
            }

            $response = $event->getResponse();
            $response->setStatusCode(302);
            $response->getHeaders()->addHeaderLine('Location', $request->getUri());
            $event->stopPropagation();

            return $response;
        }
    }
}
