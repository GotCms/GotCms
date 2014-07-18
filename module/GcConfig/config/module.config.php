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
            'UserRest'    => 'GcConfig\Controller\User',
            'RoleRest'    => 'GcConfig\Controller\Role',
            'ConfigRest'  => 'GcConfig\Controller\Config',
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
                'type'    => 'Segment',
                'options' => array(
                    'route'    => '/admin/config[/:id]',
                    'defaults' => array(
                        'module'     => 'gcconfig',
                        'controller' => 'ConfigRest',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'user' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/admin/config/user[/:id]',
                            'defaults' => array(
                                'module'     => 'gcconfig',
                                'controller' => 'UserRest',
                            ),
                        ),
                    ),
                    'role' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/admin/role[/:id]',
                            'defaults' => array(
                                'module'     => 'gcconfig',
                                'controller' => 'UserRest',
                            ),
                        ),
                    ),
                ),
            ),

            'forgot-password' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/forgot-password',
                    'defaults' =>
                    array(
                        'module'     => 'gcconfig',
                        'controller' => 'UserRest',
                        'action'     => 'forgot-password',
                    ),
                ),
            ),
            'forgot-password-key' => array(
                'type'    => 'Segment',
                'options' => array(
                    'route'    => '/forgot-password/:id/:key',
                    'defaults' =>
                    array(
                        'module'     => 'gcconfig',
                        'controller' => 'UserRest',
                        'action'     => 'forgot-password',
                    ),
                ),
            ),
            'general' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/general',
                    'defaults' =>
                    array(
                        'module'     => 'gcconfig',
                        'controller' => 'ConfigRest',
                        'action'     => 'editGeneral',
                    ),
                ),
            ),
            'system' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/system',
                    'defaults' =>
                    array(
                        'module'     => 'gcconfig',
                        'controller' => 'ConfigRest',
                        'action'     => 'editSystem',
                    ),
                ),
            ),
            'server' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/server',
                    'defaults' =>
                    array(
                        'module'     => 'gcconfig',
                        'controller' => 'ConfigRest',
                        'action'     => 'editServer',
                    ),
                ),
            ),
            'cms-update' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/update',
                    'defaults' =>
                    array(
                        'module'     => 'gcconfig',
                        'controller' => 'ConfigRest',
                        'action'     => 'update',
                    ),
                ),
            ),
        ),
    )
);
