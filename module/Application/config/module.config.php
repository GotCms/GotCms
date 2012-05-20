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
    'display_exceptions'    => TRUE,
    'controller' => array(
        'classes' => array(
            'IndexController' => 'Application\Controller\IndexController'
        ),
    ),
    'view_manager' => array(
        'display_not_found_reason'  => true,
        'display_exceptions'        => true,
        'doctype'                   => 'HTML5',
        'not_found_template'        => 'error/404',
        'exception_template'        => 'error/index',
        'template_map' => array(
            'layout/layout'     => __DIR__ . '/../views/layouts/layout.phtml',
            'index/index'       => __DIR__ . '/../views/index/index.phtml',
            'error/404'         => __DIR__ . '/../views/error/404.phtml',
            'error/index'       => __DIR__ . '/../views/error/index.phtml',
        ),
        'template_path_stack' => array(
            'application' => __DIR__ . '/../views',
        ),
    ),
    'di' => array(
        'instance' => array(
           'Zend\Db\Adapter\Adapter' => array(
                'parameters' => array(
                    'driver' => array(
                        'driver' => 'pdo_pgsql',
                        'username' => 'got',
                        'password' => 'x8maoxfp;',
                        'database' => 'gotcms',
                        'hostname' => 'localhost'
                    ),
                ),
            ),
            //@TODO Check if it works
            'Zend\View\HelperLoader' => array(
                'parameters' => array(
                    'map' => array(
                        'jsQuoteEscape' => 'Gc\View\Helper\JsQuoteEscape'
                    )
                ),
            ),
            'Zend\Validator\ValidatorLoader' => array(
                'parameters' => array(
                    'map' => array(
                        'identifier' => 'Gc\Validator\Identifier'
                    )
                ),
            ),
        ),
    ),
);
