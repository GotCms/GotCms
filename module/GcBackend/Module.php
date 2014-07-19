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
 * @category   Gc_Application
 * @package    GcBackend
 * @subpackage Module
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */
namespace GcBackend;

use Gc\Mvc;
use Gc\Mvc\View\CreateJsonModelListener;
use Gc\Core\Config as CoreConfig;
use Gc\Session\SaveHandler\DbTableGateway as SessionTableGateway;
use Zend\Db\TableGateway\Feature\GlobalAdapterFeature;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter as DbAdapter;
use Zend\EventManager\EventInterface;
use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\ServiceManager;
use Zend\Session\SessionManager;
use Zend\Session\Config\SessionConfig;
use Zend\Session\Container as SessionContainer;
use Zend\Session\SaveHandler\DbTableGatewayOptions;
use Zend\View\Model\JsonModel;
use Zend\Validator\AbstractValidator;
/**
 * Admin module
 *
 * @category   Gc_Application
 * @package    GcBackend
 * @subpackage Module
 */
class Module extends Mvc\Module
{
    /**
     * Module directory path
     */
    protected $directory = __DIR__;

    /**
     * Module namespace
     */
    protected $namespace = __NAMESPACE__;

    public function onBootstrap(MvcEvent $e)
    {
        $application    = $e->getApplication();
        $config         = $application->getConfig();
        $eventManager   = $application->getEventManager();
        $serviceManager = $application->getServiceManager();
        if (isset($config['db'])) {
            $dbAdapter = $this->initDatabase($config);
            $this->initTranslator($serviceManager);
            $this->initSession($serviceManager, $dbAdapter);
            $eventManager->attach(MvcEvent::EVENT_DISPATCH_ERROR, array($this, 'onDispatchError'), 0);
            $eventManager->attach(MvcEvent::EVENT_RENDER_ERROR, array($this, 'onRenderError'), 0);

            $createJsonModelListener = new CreateJsonModelListener();
            $sharedEvents            = $eventManager->getSharedManager();
            $sharedEvents->attach('Zend\Stdlib\DispatchableInterface', MvcEvent::EVENT_DISPATCH, array($createJsonModelListener, 'createJsonModelFromArray'), -70);
            $sharedEvents->attach('Zend\Stdlib\DispatchableInterface', MvcEvent::EVENT_DISPATCH, array($createJsonModelListener, 'createJsonModelFromNull'), -70);
        }
    }

    public function onDispatchError(MvcEvent $e)
    {
        return $this->getJsonModelError($e);
    }

    public function onRenderError(MvcEvent $e)
    {
        return $this->getJsonModelError($e);
    }

    public function getJsonModelError(MvcEvent $e)
    {
        $error = $e->getError();
        if (!$error) {
            return;
        }

        $response = $e->getResponse();
        $exception = $e->getParam('exception');
        $exceptionJson = array();
        if ($exception) {
            $exceptionJson = array(
                'class' => get_class($exception),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'message' => $exception->getMessage(),
                'stacktrace' => $exception->getTraceAsString()
            );
        }

        $errorJson = array(
            'message' => 'An error occurred during execution; please try again later.',
            'error' => $error,
            'exception' => $exceptionJson,
        );
        if ($error == 'error-router-no-match') {
            $errorJson['message'] = 'Resource not found.';
        }

        $model = new JsonModel(array('errors' => array($errorJson)));
        $e->setResult($model);

        return $model;
    }

    /**
     * Initialize database
     *
     * @param array $config Configuration
     *
     * @return DbAdapter
     */
    public function initDatabase(array $config)
    {
        $dbAdapter = new DbAdapter($config['db']);
        GlobalAdapterFeature::setStaticAdapter($dbAdapter);

        return $dbAdapter;
    }

    /**
     * Initialize Session data
     *
     * @param ServiceManager $serviceManager Service manager
     * @param DbAdapter      $dbAdapter      Database adapter
     *
     * @return void
     */
    public function initSession(ServiceManager $serviceManager, DbAdapter $dbAdapter)
    {
        $coreConfig    = $serviceManager->get('CoreConfig');
        $sessionConfig = new SessionConfig();
        $sessionConfig->setStorageOption('gc_probability', 1);
        $sessionConfig->setStorageOption('gc_divisor', 100);
        $sessionConfig->setStorageOption('save_path', $coreConfig->getValue('session_path'));
        $sessionConfig->setStorageOption('gc_maxlifetime', $coreConfig->getValue('session_lifetime'));
        $sessionConfig->setStorageOption('cookie_path', $coreConfig->getValue('cookie_path'));
        $sessionConfig->setStorageOption('cookie_domain', $coreConfig->getValue('cookie_domain'));
        $sessionManager = new SessionManager($sessionConfig);
        SessionContainer::setDefaultManager($sessionManager);

        if ($coreConfig->getValue('session_handler') == CoreConfig::SESSION_DATABASE) {
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

            $sessionManager->setSaveHandler($sessionTable);
        }

        $sessionManager->start();
    }

    /**
     * Initialize translator data
     *
     * @param ServiceManager $serviceManager Service manager
     *
     * @return void
     */
    public function initTranslator(ServiceManager $serviceManager)
    {
        $coreConfig = $serviceManager->get('CoreConfig');
        $translator = $serviceManager->get('MvcTranslator');
        $locale     = $coreConfig->getValue('locale');
        if (!empty($locale)) {
            $translator->setLocale($locale);
        }

        AbstractValidator::setDefaultTranslator($translator);
    }
}
