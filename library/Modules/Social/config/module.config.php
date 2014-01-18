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
 * @package    Application
 * @subpackage Config
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

return array(
    'controllers' => array(
        'invokables' => array(
            'SocialController' => 'Social\Controller\IndexController',
            'AddThisController' => 'Social\Controller\AddThisController',
        ),
    ),
    'service_manager' => array(
        'factories' => array(
            'AddThisModel' => function ($sm) {
                return new \Social\Model\AddThis($sm->get('CoreConfig'));
            },
        )
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'social' => __DIR__ . '/../views',
        ),
    ),
    'router' => array(
        'routes' => array(
            'social' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route' => '/social',
                    'defaults' => array(
                        'module'     =>'Social',
                        'controller' => 'SocialController',
                        'action'     => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'addthis' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route' => '/addthis',
                            'defaults' => array(
                                'module'     =>'Social',
                                'controller' => 'AddThisController',
                                'action'     => 'index',
                            ),
                        ),
                        'may_terminate' => true,
                        'child_routes' => array(
                            'add-widget' => array(
                                'type'    => 'Literal',
                                'options' => array(
                                    'route' => '/add-widget',
                                    'defaults' => array(
                                        'module'     =>'Social',
                                        'controller' => 'AddThisController',
                                        'action'     => 'add-widget',
                                    ),
                                ),
                            ),
                            'config' => array(
                                'type'    => 'Literal',
                                'options' => array(
                                    'route' => '/config',
                                    'defaults' => array(
                                        'module'     =>'Social',
                                        'controller' => 'AddThisController',
                                        'action'     => 'config',
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
);
