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
define('GC_APPLICATION_PATH', $gc_root);
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
    $db_adapter->getDriver()->getConnection()->connect();
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
 * Install database
 */
$resource = $db_adapter->getDriver()->getConnection()->getResource();
$driver_name = str_replace('pdo_', '', $config['db']['driver']);
$resource->exec(file_get_contents(sprintf($gc_root . '/data/install/sql/database-%s.sql', $driver_name)));
$resource->exec(file_get_contents($gc_root . '/data/install/sql/data.sql'));
//Create role
$ini = new \Zend\Config\Reader\Ini();
$roles = $ini->fromFile($gc_root . '/data/install/scripts/roles.ini');

try
{
    foreach ($roles['role'] as $key=>$value)
    {
        $statement = $db_adapter->createStatement("INSERT INTO user_acl_role (name) VALUES ('" . $value . "')");
        $result = $statement->execute();
    }
}
catch (Exception $e)
{
    return $this->_returnJson(array('messages' => $e->getMessage()));
}

//resources
$ini = new \Zend\Config\Reader\Ini();
$resources = $ini->fromFile($gc_root . '/data/install/scripts/resources.ini');

foreach($resources as $key => $value)
{
    $statement = $db_adapter->createStatement("INSERT INTO user_acl_resource (resource) VALUES ('" . $key . "')");
    $result = $statement->execute();

    $statement = $db_adapter->createStatement("SELECT id FROM user_acl_resource WHERE resource =  '" . $key . "'");
    $result = $statement->execute();
    $lastInsertId = $result->current();
    $lastInsertId = $lastInsertId['id'];

    $permissions = array();
    foreach($value as $key2 => $value2)
    {
        if(!in_array($key2, $permissions))
        {
            $statement = $db_adapter->createStatement("INSERT INTO user_acl_permission (permission, user_acl_resource_id) VALUES ('".$key2."', '".$lastInsertId."')");
            $result = $statement->execute();
            $permissions[] = $key2;
        }
    }
}

foreach($resources as $key => $value)
{
    $statement = $db_adapter->createStatement("SELECT id FROM user_acl_resource WHERE resource =  '" . $key . "'");
    $result = $statement->execute();
    $lastResourceInsertId = $result->current();
    $lastResourceInsertId = $lastResourceInsertId['id'];

    foreach($value as $key2 => $value2)
    {
        $statement = $db_adapter->createStatement("SELECT id FROM user_acl_permission WHERE permission =  '" . $key2 . "' AND user_acl_resource_id = '" .$lastResourceInsertId . "'");
        $result = $statement->execute();
        $lastInsertId = $result->current();
        $lastInsertId = $lastInsertId['id'];

        $statement = $db_adapter->createStatement("SELECT id FROM user_acl_role WHERE name = '" . $value2 . "'");
        $result = $statement->execute();
        $role = $result->current();
        if(!empty($role['id']))
        {
            $statement = $db_adapter->createStatement("INSERT INTO user_acl (user_acl_role_id, user_acl_permission_id) VALUES ('".$role['id']."', " . $lastInsertId . ")");
            $result = $statement->execute();
        }
    }
}
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
