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
 * @category   Gc_Application
 * @package    GcModule
 * @subpackage Config
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */
return array(
    'controllers' => array(
        'invokables' => array(
            'ModuleController' => 'GcModule\Controller\IndexController',
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'module'  => __DIR__ . '/../views',
        ),
    ),
    'router' => array(
        'routes' => array(
            'module'     => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/admin/module',
                    'defaults' =>
                    array (
                        'module'     => 'gcmodule',
                        'controller' => 'ModuleController',
                        'action'     => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes'  => array(
                    'install' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => '/install',
                            'defaults' => array(
                                'module'     => 'gcmodule',
                                'controller' => 'ModuleController',
                                'action'     => 'install',
                            ),
                        ),
                    ),
                    'uninstall' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/uninstall/:id',
                            'defaults' => array(
                                'module'     => 'gcmodule',
                                'controller' => 'ModuleController',
                                'action'     => 'uninstall',
                            ),
                        ),
                    ),
                )
            ),
        ),
    )
);
