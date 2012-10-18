<?php

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
