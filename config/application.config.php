<?php
/**
 * This source file is part of Got CMS.
 *
 * Got CMS is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Got CMS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License along
 * with Got CMS. If not, see <http://www.gnu.org/licenses/lgpl-3.0.html>.
 *
 * PHP Version >=5.3
 *
 * @category Gc
 * @package  Config
 * @author   Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license  GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link     http://www.got-cms.com
 */

return array(
    'modules' => array(
        'Application'
        , 'Admin'
        , 'Config'
        , 'Content'
        , 'Development'
        , 'Statistics'
    ),
    'module_listener_options' => array(
        'config_cache_enabled' => false
        , 'cache_dir' => 'data/cache'
        , 'module_paths' => array(
            './module'
            , './vendor'
        ),
    ),
    'service_manager' => array(
        'use_defaults' => true
        , 'factories' => array(
        ),
    ),
    'autoloader' => array(
        'namespaces' => array(
            'Gc'        => __DIR__ . '/../vendor/Gc',
            'Datatypes' => __DIR__ . '/../vendor/Datatypes'
        ),
    ),
);
