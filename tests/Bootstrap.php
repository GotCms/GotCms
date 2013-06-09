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
require_once 'PHPUnit/Autoload.php';
chdir(dirname(__DIR__));
$gcRoot    = getcwd();
$zfLibrary = $gcRoot . '/vendor';
$gcLibrary = $gcRoot . '/library';
$gcTests   = $gcRoot . '/tests';

$path = array(
    $gcLibrary,
    $gcRoot . '/module',
    $zfLibrary,
    $gcTests,
    get_include_path(),
);

set_include_path(implode(PATH_SEPARATOR, $path));
define('GC_APPLICATION_PATH', $gcRoot);
define('GC_MEDIA_PATH', GC_APPLICATION_PATH . '/tests/media');
/**
 * Setup autoloading
 */

$zfPath = false;

if (getenv('ZF2_PATH')) { // Support for ZF2_PATH environment variable or git submodule
    $zfPath = getenv('ZF2_PATH');
} elseif (get_cfg_var('zf2_path')) { // Support for zf2_path directive value
    $zfPath = get_cfg_var('zf2_path');
} elseif (is_dir('vendor/Zend')) {
    $zfPath = 'vendor';
}

// Composer autoloading
if (file_exists($gcRoot . '/vendor/autoload.php')) {
    $loader = include $gcRoot . '/vendor/autoload.php';
}

if (!class_exists('Zend\Loader\AutoloaderFactory')) {
    throw new RuntimeException(
        'Unable to load ZF2. Run `php composer.phar install` ' .
        'or define a ZF2_PATH environment variable.'
    );
}

// Get application stack configuration
if ($zfPath) {
    // Get application stack configuration
    $configuration = include_once $gcRoot . '/config/application.config.php';
    if (isset($loader)) {
        $loader->add('Zend', $zfPath);
        foreach ($configuration['autoloader']['namespaces'] as $name => $path) {
            $loader->add($name, dirname($path));
        }

        $loader->register();
    } else {
        include $zfPath . '/Zend/Loader/AutoloaderFactory.php';
        Zend\Loader\AutoloaderFactory::factory(
            array(
                'Zend\Loader\StandardAutoloader' => $configuration['autoloader'],
            )
        );
    }
}

/*
 * Load the user-defined test configuration file, if it exists; otherwise, load
 * the default configuration.
 */
if (is_readable($gcTests . DIRECTORY_SEPARATOR . 'TestConfiguration.php')) {
    include_once $gcTests . DIRECTORY_SEPARATOR . 'TestConfiguration.php';
} else {
    include_once $gcTests . DIRECTORY_SEPARATOR . 'TestConfiguration.php.dist';
}

require_once 'config/prepare-database.php';
require_once 'config/override-php-functions.php';

// Run application
\Zend\Console\Console::overrideIsConsole(false);
$application = \Zend\Mvc\Application::init($configuration);
$application->getMvcEvent()->getRouter()->setRequestUri($application->getRequest()->getUri());
$application->getRequest()->setBasePath('http://got-cms.com');
\Gc\Registry::set('Application', $application);
//Remove all event observer
\Gc\Event\StaticEventManager::resetInstance();

/*
 * Unset global variables that are no longer needed.
 */
unset($gcRoot, $gcLibrary, $gcTests, $path);
