<?php

use Gc\Core\Config as GcConfig,
    Gc\Registry,
    Zend\Db\Adapter\Adapter as DbAdapter,
    Zend\Db\TableGateway\TableGateway;

/*
 * Set error reporting to the level to which Es code must comply.
 */
error_reporting(E_ALL | E_STRICT);

/*
 * Determine the root, library, and tests directories of the framework
 * distribution.
 */
$gc_root        = realpath(dirname(__DIR__));
$zf_library     = $gc_root . '/vendor/ZendFramework/library';
$gc_library     = $gc_root . '/vendor';
$gc_tests       = $gc_root . '/tests';

$path = array(
    $gc_library,
    $zf_library,
    $gc_tests,
    get_include_path(),
);

set_include_path(implode(PATH_SEPARATOR, $path));
/**
 * Setup autoloading
 */

require_once $zf_library . '/Zend/Loader/AutoloaderFactory.php';
$app_config = include $gc_root . '/config/application.config.php';
Zend\Loader\AutoloaderFactory::factory(array('Zend\Loader\StandardAutoloader' => $app_config['autoloader']));


/*
 * Load the user-defined test configuration file, if it exists; otherwise, load
 * the default configuration.
 */
if (is_readable($gc_tests . DIRECTORY_SEPARATOR . 'TestConfiguration.php'))
{
    require_once $gc_tests . DIRECTORY_SEPARATOR . 'TestConfiguration.php';
}
else
{
    require_once $gc_tests . DIRECTORY_SEPARATOR . 'TestConfiguration.php.dist';
}

$config = array();
foreach(glob(realpath(__DIR__.'/../').'/config/autoload/{,*.}{global,local}.php', GLOB_BRACE) as $filename)
{
    $config += include_once($filename);
}

$config = array(
    'db' => array(
        'driver' => GC_DATABASE_DRIVER,
        'username' => GC_DATABASE_USERNAME,
        'password' => GC_DATABASE_PASSWORD,
        'database' => GC_DATABASE_DATABASE,
        'hostname' => GC_DATABASE_HOSTNAME,
    ),
);

try
{
    $db_adapter = new DbAdapter($config['db']);
}
catch(Exception $e)
{
    echo 'Can\'t connect to database exiting.' . PHP_EOL;
    exit;
}

\Zend\Db\TableGateway\Feature\GlobalAdapterFeature::setStaticAdapter($db_adapter);
Registry::set('Configuration', $config);
Registry::set('Db', $db_adapter);


/**
 * Start output buffering, if enabled
 */
if (defined('TESTS_ES_OB_ENABLED') && constant('TESTS_ES_OB_ENABLED'))
{
    ob_start();
}
/*
 * Unset global variables that are no longer needed.
 */
unset($gc_root, $gc_library, $gc_tests, $path);
