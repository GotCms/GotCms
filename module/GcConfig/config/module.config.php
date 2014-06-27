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
 * @category Gc
 * @package  Config
 * @author   Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license  GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link     http://www.got-cms.com
 */

return array(
    'display_exceptions'    => true,
    'controllers' => array(
        'invokables' => array(
            'ConfigController'  => 'GcConfig\Controller\IndexController',
            'UserController'    => 'GcConfig\Controller\UserController',
            'RoleController'    => 'GcConfig\Controller\RoleController',
            'RuleController'    => 'GcConfig\Controller\RuleController',
            'CmsController'     => 'GcConfig\Controller\CmsController',
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'config' => __DIR__ . '/../views',
        ),
    ),
    'router' => array(
        'routes' => array(
            'config' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/admin/config',
                    'defaults' =>
                    array (
                        'module'     => 'gcconfig',
                        'controller' => 'ConfigController',
                        'action'     => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes'  => array(
                    'user' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => '/user',
                            'defaults' =>
                            array (
                                'module'     => 'gcconfig',
                                'controller' => 'UserController',
                                'action'     => 'index',
                            ),
                        ),
                        'may_terminate' => true,
                        'child_routes'  => array(
                            'forbidden' => array(
                                'type'    => 'Literal',
                                'options' => array(
                                    'route'    => '/forbidden-access',
                                    'defaults' =>
                                    array (
                                        'module'     => 'gcconfig',
                                        'controller' => 'UserController',
                                        'action'     => 'forbidden',
                                    ),
                                ),
                            ),
                            'login' => array(
                                'type'    => 'Segment',
                                'options' => array(
                                    'route'    => '/login[/:redirect]',
                                    'defaults' =>
                                    array (
                                        'module'     => 'gcconfig',
                                        'controller' => 'UserController',
                                        'action'     => 'login',
                                    ),
                                ),
                            ),
                            'logout' => array(
                                'type'    => 'Literal',
                                'options' => array(
                                    'route'    => '/logout',
                                    'defaults' =>
                                    array (
                                        'module'     => 'gcconfig',
                                        'controller' => 'UserController',
                                        'action'     => 'logout',
                                    ),
                                ),
                            ),
                            'forgot-password' => array(
                                'type'    => 'Literal',
                                'options' => array(
                                    'route'    => '/forgot-password',
                                    'defaults' =>
                                    array (
                                        'module'     => 'gcconfig',
                                        'controller' => 'UserController',
                                        'action'     => 'forgot-password',
                                    ),
                                ),
                            ),
                            'forgot-password-key' => array(
                                'type'    => 'Segment',
                                'options' => array(
                                    'route'    => '/forgot-password/:id/:key',
                                    'defaults' =>
                                    array (
                                        'module'     => 'gcconfig',
                                        'controller' => 'UserController',
                                        'action'     => 'forgot-password',
                                    ),
                                ),
                            ),
                            'create' => array(
                                'type'    => 'Literal',
                                'options' => array(
                                    'route'    => '/create',
                                    'defaults' =>
                                    array (
                                        'module'     => 'gcconfig',
                                        'controller' => 'UserController',
                                        'action'     => 'create',
                                    ),
                                ),
                            ),
                            'edit' => array(
                                'type'    => 'Segment',
                                'options' => array(
                                    'route'    => '/edit/:id',
                                    'defaults' =>
                                    array (
                                        'module'     => 'gcconfig',
                                        'controller' => 'UserController',
                                        'action'     => 'edit',
                                    ),
                                ),
                            ),
                            'delete' => array(
                                'type'    => 'Segment',
                                'options' => array(
                                    'route'    => '/delete/:id',
                                    'defaults' =>
                                    array (
                                        'module'     => 'gcconfig',
                                        'controller' => 'UserController',
                                        'action'     => 'delete',
                                    ),
                                ),
                            ),
                            'role' => array(
                                'type'    => 'Literal',
                                'options' => array(
                                    'route'    => '/role',
                                    'defaults' =>
                                    array (
                                        'module'     => 'gcconfig',
                                        'controller' => 'RoleController',
                                        'action'     => 'index',
                                    ),
                                ),
                                'may_terminate' => true,
                                'child_routes'  => array(
                                    'create' => array(
                                        'type'    => 'Literal',
                                        'options' => array(
                                            'route'    => '/create',
                                            'defaults' =>
                                            array (
                                                'module'     => 'gcconfig',
                                                'controller' => 'RoleController',
                                                'action'     => 'create',
                                            ),
                                        ),
                                    ),
                                    'edit' => array(
                                        'type'    => 'Segment',
                                        'options' => array(
                                            'route'    => '/edit/:id',
                                            'defaults' =>
                                            array (
                                                'module'     => 'gcconfig',
                                                'controller' => 'RoleController',
                                                'action'     => 'edit',
                                            ),
                                        ),
                                    ),
                                    'delete' => array(
                                        'type'    => 'Segment',
                                        'options' => array(
                                            'route'    => '/delete/:id',
                                            'defaults' =>
                                            array (
                                                'module'     => 'gcconfig',
                                                'controller' => 'RoleController',
                                                'action'     => 'delete',
                                            ),
                                        ),
                                    ),
                                )
                            ),
                        )
                    ),
                    'general' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => '/general',
                            'defaults' =>
                            array (
                                'module'     => 'gcconfig',
                                'controller' => 'CmsController',
                                'action'     => 'editGeneral',
                            ),
                        ),
                    ),
                    'system' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => '/system',
                            'defaults' =>
                            array (
                                'module'     => 'gcconfig',
                                'controller' => 'CmsController',
                                'action'     => 'editSystem',
                            ),
                        ),
                    ),
                    'server' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => '/server',
                            'defaults' =>
                            array (
                                'module'     => 'gcconfig',
                                'controller' => 'CmsController',
                                'action'     => 'editServer',
                            ),
                        ),
                    ),
                    'cms-update' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => '/update',
                            'defaults' =>
                            array (
                                'module'     => 'gcconfig',
                                'controller' => 'CmsController',
                                'action'     => 'update',
                            ),
                        ),
                    ),
                ),
            ),
        ),
    )
);
