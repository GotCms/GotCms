<?php
chdir(dirname(__DIR__));
require_once (getenv('ZF2_PATH') ?: 'vendor/ZendFramework/library') . '/Zend/Loader/AutoloaderFactory.php';

use Zend\Loader\AutoloaderFactory,
Zend\ServiceManager\ServiceManager,
Zend\Mvc\Service\ServiceManagerConfiguration;

// get application stack configuration
$configuration = include 'config/application.config.php';
// setup autoloader
AutoloaderFactory::factory(array('Zend\Loader\StandardAutoloader' => $configuration['autoloader']));


// setup service manager
$serviceManager = new ServiceManager(new ServiceManagerConfiguration($configuration['service_manager']));
$serviceManager->setService('ApplicationConfiguration', $configuration);
$serviceManager->get('ModuleManager')->loadModules();

// run application
$serviceManager->get('Application')->bootstrap()->run()->send();
