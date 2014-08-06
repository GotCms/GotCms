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

use Gc\Core\Updater;

return array(
    'service_manager' => array(
        'factories' => array(
            'CoreUpdater' => function () {
                return new Updater();
            }
        )
    ),
    'controllers' => array(
        'invokables' => array(
            'ConfigController' => 'GcConfig\Controller\IndexController',
            'UserRest'         => 'GcConfig\Controller\UserRestController',
            'ConfigRest'       => 'GcConfig\Controller\ConfigRestController',
            'RoleRest'         => 'GcConfig\Controller\RoleRestController',
            'UpdateRest'       => 'GcConfig\Controller\UpdateRestController',
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
                    ),
                ),
                'may_terminate' => true,
                'child_routes'  => array(
                    'user' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => '/user[/:id]',
                            'defaults' => array(
                                'module'     => 'gcconfig',
                                'controller' => 'UserRest',
                            ),
                        ),
                        'may_terminate' => true,
                        'child_routes'  => array(
                            'forgot-password' => array(
                                'type'    => 'Literal',
                                'options' => array(
                                    'route'    => '/forgot-password',
                                    'defaults' => array(
                                        'module'     => 'gcconfig',
                                        'controller' => 'UserRestController',
                                        'action'     => 'forgot-password',
                                    ),
                                ),
                            ),
                            'forgot-password-key' => array(
                                'type'    => 'Segment',
                                'options' => array(
                                    'route'    => '/forgot-password/:id/:key',
                                    'defaults' => array(
                                        'module'     => 'gcconfig',
                                        'controller' => 'UserRestController',
                                        'action'     => 'forgot-password',
                                    ),
                                ),
                            ),
                            'role' => array(
                                'type'    => 'Literal',
                                'options' => array(
                                    'route'    => '/role[/:id]',
                                    'defaults' => array(
                                        'module'     => 'gcconfig',
                                        'controller' => 'RoleRest',
                                    ),
                                ),
                            ),
                        )
                    ),
                    'general' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/general[/:id]',
                            'defaults' => array(
                                'module'     => 'gcconfig',
                                'controller' => 'ConfigRest',
                                'type'       => 'general',
                            ),
                        ),
                    ),
                    'system' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/system[/:id]',
                            'defaults' => array(
                                'module'     => 'gcconfig',
                                'controller' => 'ConfigRest',
                                'type'       => 'system',
                            ),
                        ),
                    ),
                    'server' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/server[/:id]',
                            'defaults' => array(
                                'module'     => 'gcconfig',
                                'controller' => 'ConfigRest',
                                'type'       => 'server',
                            ),
                        ),
                    ),
                    'cms-update' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => '/update',
                            'defaults' => array(
                                'module'     => 'gcconfig',
                                'controller' => 'UpdateRest',
                            ),
                        ),
                    ),
                ),
            ),
        ),
    )
);
