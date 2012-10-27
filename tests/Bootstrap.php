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
 * @subpackage  User
 * @author      Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license     GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link        http://www.got-cms.com
 */

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


require_once('prepare-database.php');


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
