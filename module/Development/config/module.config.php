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
 * @package    Development
 * @subpackage Config
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

return array(
    'controllers' => array(
        'invokables' => array(
            'DatatypeController'      => 'Development\Controller\DatatypeController',
            'DevelopmentController'     => 'Development\Controller\IndexController',
            'DocumentTypeController'  => 'Development\Controller\DocumentTypeController',
            'LayoutController'        => 'Development\Controller\LayoutController',
            'ScriptController'          => 'Development\Controller\ScriptController',
            'ViewController'          => 'Development\Controller\ViewController',
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'development' => __DIR__ . '/../views',
        ),
    ),
    'router' => array(
        'routes' => array(
            'development' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/admin/development',
                    'defaults' =>
                    array (
                        'module'     => 'development',
                        'controller' => 'DevelopmentController',
                        'action'     => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    //Views
                    'view' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => '/view',
                            'defaults' =>
                            array (
                                'module'     => 'development',
                                'controller' => 'ViewController',
                                'action'     => 'index',
                            ),
                        ),
                        'may_terminate' => true,
                        'child_routes' => array(
                            'create' => array(
                                'type'    => 'Literal',
                                'options' => array(
                                    'route'    => '/create',
                                    'defaults' =>
                                    array (
                                        'module'     => 'development',
                                        'controller' => 'ViewController',
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
                                        'module'     => 'development',
                                        'controller' => 'ViewController',
                                        'action'     => 'edit',
                                    ),
                                    'constraints' =>
                                    array (
                                        'id' => '\d+',
                                    ),
                                ),
                            ),
                            'delete' => array(
                                'type'    => 'Segment',
                                'options' => array(
                                    'route'    => '/delete/:id',
                                    'defaults' =>
                                    array (
                                        'module'     => 'development',
                                        'controller' => 'ViewController',
                                        'action'     => 'delete',
                                    ),
                                    'constraints' =>
                                    array (
                                        'id' => '\d+',
                                    ),
                                ),
                            ),
                            'download' => array(
                                'type'    => 'Segment',
                                'options' => array(
                                    'route'    => '/download[/:id]',
                                    'defaults' =>
                                    array (
                                        'module'     => 'development',
                                        'controller' => 'ViewController',
                                        'action'     => 'download',
                                    ),
                                    'constraints' =>
                                    array (
                                        'id' => '\d+',
                                    ),
                                ),
                            ),
                            'upload' => array(
                                'type'    => 'Segment',
                                'options' => array(
                                    'route'    => '/upload[/:id]',
                                    'defaults' =>
                                    array (
                                        'module'     => 'development',
                                        'controller' => 'ViewController',
                                        'action'     => 'upload',
                                    ),
                                    'constraints' =>
                                    array (
                                        'id' => '\d+',
                                    ),
                                ),
                            ),
                            'update' => array(
                                'type'    => 'Segment',
                                'options' => array(
                                    'route'    => '/update[/:id]',
                                    'defaults' =>
                                    array (
                                        'module'     => 'development',
                                        'controller' => 'ViewController',
                                        'action'     => 'update',
                                    ),
                                    'constraints' =>
                                    array (
                                        'id' => '\d+',
                                    ),
                                ),
                            ),
                        ),
                    ),
                    'script' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => '/script',
                            'defaults' =>
                            array (
                                'module'     => 'development',
                                'controller' => 'ScriptController',
                                'action'     => 'index',
                            ),
                        ),
                        'may_terminate' => true,
                        'child_routes' => array(
                            'create' => array(
                                'type'    => 'Literal',
                                'options' => array(
                                    'route'    => '/create',
                                    'defaults' =>
                                    array (
                                        'module'     => 'development',
                                        'controller' => 'ScriptController',
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
                                        'module'     => 'development',
                                        'controller' => 'ScriptController',
                                        'action'     => 'edit',
                                    ),
                                    'constraints' =>
                                    array (
                                        'id' => '\d+',
                                    ),
                                ),
                            ),
                            'delete' => array(
                                'type'    => 'Segment',
                                'options' => array(
                                    'route'    => '/delete/:id',
                                    'defaults' =>
                                    array (
                                        'module'     => 'development',
                                        'controller' => 'ScriptController',
                                        'action'     => 'delete',
                                    ),
                                    'constraints' =>
                                    array (
                                        'id' => '\d+',
                                    ),
                                ),
                            ),
                            'download' => array(
                                'type'    => 'Segment',
                                'options' => array(
                                    'route'    => '/download[/:id]',
                                    'defaults' =>
                                    array (
                                        'module'     => 'development',
                                        'controller' => 'ScriptController',
                                        'action'     => 'download',
                                    ),
                                    'constraints' =>
                                    array (
                                        'id' => '\d+',
                                    ),
                                ),
                            ),
                            'upload' => array(
                                'type'    => 'Segment',
                                'options' => array(
                                    'route'    => '/upload[/:id]',
                                    'defaults' =>
                                    array (
                                        'module'     => 'development',
                                        'controller' => 'ScriptController',
                                        'action'     => 'upload',
                                    ),
                                    'constraints' =>
                                    array (
                                        'id' => '\d+',
                                    ),
                                ),
                            ),
                            'update' => array(
                                'type'    => 'Segment',
                                'options' => array(
                                    'route'    => '/update[/:id]',
                                    'defaults' =>
                                    array (
                                        'module'     => 'development',
                                        'controller' => 'ScriptController',
                                        'action'     => 'update',
                                    ),
                                    'constraints' =>
                                    array (
                                        'id' => '\d+',
                                    ),
                                ),
                            ),
                        ),
                    ),
                    'layout' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => '/layout',
                            'defaults' =>
                            array (
                                'module'     => 'development',
                                'controller' => 'LayoutController',
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
                                        'module'     => 'development',
                                        'controller' => 'LayoutController',
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
                                        'module'     => 'development',
                                        'controller' => 'LayoutController',
                                        'action'     => 'edit',
                                    ),
                                    'constraints' =>
                                    array (
                                        'id' => '\d+',
                                    ),
                                ),
                            ),
                            'delete' => array(
                                'type'    => 'Segment',
                                'options' => array(
                                    'route'    => '/delete/:id',
                                    'defaults' =>
                                    array (
                                        'module'     => 'development',
                                        'controller' => 'LayoutController',
                                        'action'     => 'delete',
                                    ),
                                    'constraints' =>
                                    array (
                                        'id' => '\d+',
                                    ),
                                ),
                            ),
                            'download' => array(
                                'type'    => 'Segment',
                                'options' => array(
                                    'route'    => '/download[/:id]',
                                    'defaults' =>
                                    array (
                                        'module'     => 'development',
                                        'controller' => 'LayoutController',
                                        'action'     => 'download',
                                    ),
                                    'constraints' =>
                                    array (
                                        'id' => '\d+',
                                    ),
                                ),
                            ),
                            'upload' => array(
                                'type'    => 'Segment',
                                'options' => array(
                                    'route'    => '/upload[/:id]',
                                    'defaults' =>
                                    array (
                                        'module'     => 'development',
                                        'controller' => 'LayoutController',
                                        'action'     => 'upload',
                                    ),
                                    'constraints' =>
                                    array (
                                        'id' => '\d+',
                                    ),
                                ),
                            ),
                            'update' => array(
                                'type'    => 'Segment',
                                'options' => array(
                                    'route'    => '/update[/:id]',
                                    'defaults' =>
                                    array (
                                        'module'     => 'development',
                                        'controller' => 'LayoutController',
                                        'action'     => 'update',
                                    ),
                                    'constraints' =>
                                    array (
                                        'id' => '\d+',
                                    ),
                                ),
                            ),
                        ),
                    ),
                    'datatype' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => '/datatype',
                            'defaults' =>
                            array (
                                'module'     => 'development',
                                'controller' => 'DatatypeController',
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
                                        'module'     => 'development',
                                        'controller' => 'DatatypeController',
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
                                        'module'     => 'development',
                                        'controller' => 'DatatypeController',
                                        'action'     => 'edit',
                                    ),
                                    'constraints' =>
                                    array (
                                        'id' => '\d+',
                                    ),
                                ),
                            ),
                            'delete' => array(
                                'type'    => 'Segment',
                                'options' => array(
                                    'route'    => '/delete/:id',
                                    'defaults' =>
                                    array (
                                        'module'     => 'development',
                                        'controller' => 'DatatypeController',
                                        'action'     => 'delete',
                                    ),
                                    'constraints' =>
                                    array (
                                        'id' => '\d+',
                                    ),
                                ),
                            ),
                        ),
                    ),
                    'document-type' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => '/document-type',
                            'defaults' =>
                            array (
                                'module'     => 'development',
                                'controller' => 'DocumentTypeController',
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
                                        'module'     => 'development',
                                        'controller' => 'DocumentTypeController',
                                        'action'     => 'create',
                                    ),
                                ),
                            ),
                            'delete' => array(
                                'type'    => 'Segment',
                                'options' => array(
                                    'route'    => '/delete/:id',
                                    'defaults' =>
                                    array (
                                        'module'     => 'development',
                                        'controller' => 'DocumentTypeController',
                                        'action'     => 'delete',
                                    ),
                                    'constraints' =>
                                    array (
                                        'id' => '\d+',
                                    ),
                                ),
                            ),
                            'edit' => array(
                                'type'    => 'Segment',
                                'options' => array(
                                    'route'    => '/edit/:id',
                                    'defaults' =>
                                    array (
                                        'module'     => 'development',
                                        'controller' => 'DocumentTypeController',
                                        'action'     => 'edit',
                                    ),
                                    'constraints' =>
                                    array (
                                        'id' => '\d+',
                                    ),
                                ),
                            ),
                            'add-tab' => array(
                                'type'    => 'Literal',
                                'options' => array(
                                    'route'    => '/create-tab',
                                    'defaults' =>
                                    array (
                                        'module'     => 'development',
                                        'controller' => 'DocumentTypeController',
                                        'action'     => 'add-tab',
                                    ),
                                ),
                            ),
                            'delete-tab' => array(
                                'type'    => 'Literal',
                                'options' => array(
                                    'route'    => '/delete-tab',
                                    'defaults' =>
                                    array (
                                        'module'     => 'development',
                                        'controller' => 'DocumentTypeController',
                                        'action'     => 'delete-tab',
                                    ),
                                ),
                            ),
                            'import-tab' => array(
                                'type'    => 'Literal',
                                'options' => array(
                                    'route'    => '/import-tab',
                                    'defaults' =>
                                    array (
                                        'module'     => 'development',
                                        'controller' => 'DocumentTypeController',
                                        'action'     => 'import-tab',
                                    ),
                                ),
                            ),
                            'add-property' => array(
                                'type'    => 'Literal',
                                'options' => array(
                                    'route'    => '/create-property',
                                    'defaults' =>
                                    array (
                                        'module'     => 'development',
                                        'controller' => 'DocumentTypeController',
                                        'action'     => 'add-property',
                                    ),
                                ),
                            ),
                            'delete-property' => array(
                                'type'    => 'Literal',
                                'options' => array(
                                    'route'    => '/delete-property',
                                    'defaults' =>
                                    array (
                                        'module'     => 'development',
                                        'controller' => 'DocumentTypeController',
                                        'action'     => 'delete-property',
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
