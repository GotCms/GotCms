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
 * @category Gc_Test
 * @package  Bootstrap
 * @author   Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license  GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link     http://www.got-cms.com
 */
namespace Gc;

/*
 * Set error reporting to the level to which Es code must comply.
 */
error_reporting(E_ALL | E_STRICT);

/*
 * Determine the root, library, and tests directories of the framework
 * distribution.
 */

chdir(dirname(__DIR__));
$gc_root    = getcwd();
$zf_library = $gc_root . '/vendor';
$gc_library = $gc_root . '/library';
$gc_tests   = $gc_root . '/tests';

$path = array(
    $gc_library,
    $gc_root . '/module',
    $zf_library,
    $gc_tests,
    get_include_path(),
);

set_include_path(implode(PATH_SEPARATOR, $path));
define('GC_APPLICATION_PATH', $gc_root);
define('GC_MEDIA_PATH', GC_APPLICATION_PATH . '/tests/media');
/**
 * Setup autoloading
 */

// Composer autoloading
if (file_exists($gc_root . '/vendor/autoload.php')) {
    $loader = include $gc_root . '/vendor/autoload.php';
}

// Support for ZF2_PATH environment variable or git submodule

if ($zf2_path = getenv('ZF2_PATH') ?: (is_dir($zf_library) ? $zf_library : false)) {
    // Get application stack configuration
    $configuration = require_once $gc_root . '/config/application.config.php';
    if (isset($loader)) {
        $loader->add('Zend', $zf2_path . '/Zend');
    } else {
        require_once $zf_library . '/Zend/Loader/AutoloaderFactory.php';
        \Zend\Loader\AutoloaderFactory::factory(
            array(
                'Zend\Loader\StandardAutoloader' => $configuration['autoloader'],
            )
        );
    }
}

if (!class_exists('Zend\Loader\AutoloaderFactory')) {
    throw new RuntimeException(
        'Unable to load ZF2. Run `php composer.phar install` or define a ZF2_PATH environment variable.'
    );
}


// Run application
\Zend\Console\Console::overrideIsConsole(false);
$application = \Zend\Mvc\Application::init($configuration);
$application->getMvcEvent()->getRouter()->setRequestUri($application->getRequest()->getUri());
$application->getRequest()->setBasePath('http://gotcms.com');
\Gc\Registry::set('Application', $application);
//Remove all event observer
\Gc\Event\StaticEventManager::resetInstance();
/*
 * Load the user-defined test configuration file, if it exists; otherwise, load
 * the default configuration.
 */
if (is_readable($gc_tests . DIRECTORY_SEPARATOR . 'TestConfiguration.php')) {
    require_once $gc_tests . DIRECTORY_SEPARATOR . 'TestConfiguration.php';
} else {
    require_once $gc_tests . DIRECTORY_SEPARATOR . 'TestConfiguration.php.dist';
}


require_once 'prepare-database.php';

/*
 * Unset global variables that are no longer needed.
 */
unset($gc_root, $gc_library, $gc_tests, $path);

require_once 'override-php-functions.php';
