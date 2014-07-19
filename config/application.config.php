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
 * @category Gc_Application
 * @package  Config
 * @author   Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license  GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link     http://www.got-cms.com
 */

$modules = array(
    'AssetManager',
    /* 'GcFrontend', */
    'GcBackend',
    /* 'GcConfig', */
    /* 'GcContent', */
    'GcDevelopment',
    /* 'GcModule', */
    /* 'GcStatistics', */
);

$path       = GC_APPLICATION_PATH . '/extensions/ZfModules/';
$extensions = new DirectoryIterator($path);
foreach ($extensions as $dir) {
    if ($dir->isDot() or !$dir->isDir()) {
        continue;
    }

    $modules[] = $dir->getFileName();
}

return array(
    'modules' => $modules,
    'module_listener_options' => array(
        'config_glob_paths'    => array(
            'config/autoload/{,*.}{local,global}.php',
        ),
        'config_cache_enabled' => false,
        'cache_dir' => 'data/cache',
        'module_paths' => array(
            './module',
            './vendor',
            './extensions/ZfModules',
        ),
    ),
    'service_manager' => array(
        'use_defaults' => true,
        'factories' => array(),
    ),
    'autoloader' => array(
        'namespaces' => array(
            'Assetic'  => __DIR__ . '/../vendor/Assetic',
            'elFinder'  => __DIR__ . '/../vendor/elFinder',
            'Parsedown' => __DIR__ . '/../vendor/Parsedown',
            'Datatypes' => __DIR__ . '/../library/Datatypes',
            'Gc'        => __DIR__ . '/../library/Gc',
            __DIR__ . '/../extensions/Datatypes',
        ),
        'autoregister_zf' => true,
    ),
);
