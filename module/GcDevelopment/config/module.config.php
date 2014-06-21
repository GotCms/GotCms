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
 * @package    GcDevelopment
 * @subpackage Config
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

return array(
    'service_manager' => array(
        'factories' => array(
            'DatatypesList' => function () {
                $datatypes = array();
                $origin    = new DirectoryIterator(GC_APPLICATION_PATH . '/library/Datatypes/');
                foreach ($origin as $dir) {
                    if ($dir->isDot() or !$dir->isDir()) {
                        continue;
                    }

                    $datatypes[$dir->getPathname()] = $dir->getFileName();
                }

                $extensions = new DirectoryIterator(GC_APPLICATION_PATH . '/extensions/Datatypes/');
                foreach ($extensions as $dir) {
                    if ($dir->isDot() or !$dir->isDir()) {
                        continue;
                    }

                    $datatypes[$dir->getPathname()] = $dir->getFileName();
                }

                asort($datatypes);

                return $datatypes;
            },
            'ModulesList' => function () {
                $modules = array();
                $origin    = new DirectoryIterator(GC_APPLICATION_PATH . '/library/Modules/');
                foreach ($origin as $dir) {
                    if ($dir->isDot() or !$dir->isDir()) {
                        continue;
                    }

                    $modules[$dir->getPathname()] = $dir->getFileName();
                }

                $extensions = new DirectoryIterator(GC_APPLICATION_PATH . '/extensions/Modules/');
                foreach ($extensions as $dir) {
                    if ($dir->isDot() or !$dir->isDir()) {
                        continue;
                    }

                    $modules[$dir->getPathname()] = $dir->getFileName();
                }

                asort($modules);

                return $modules;
            }
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'DatatypeController'     => 'GcDevelopment\Controller\DatatypeController',
            'DevelopmentController'  => 'GcDevelopment\Controller\IndexController',
            'DocumentTypeController' => 'GcDevelopment\Controller\DocumentTypeController',
            'LayoutController'       => 'GcDevelopment\Controller\LayoutController',
            'ScriptController'       => 'GcDevelopment\Controller\ScriptController',
            'ViewController'         => 'GcDevelopment\Controller\ViewController',
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'development' => __DIR__ . '/../views',
            'templates'   =>  GC_APPLICATION_PATH . '/templates',
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
                        'module'     => 'gcdevelopment',
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
                                'module'     => 'gcdevelopment',
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
                                        'module'     => 'gcdevelopment',
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
                                        'module'     => 'gcdevelopment',
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
                                        'module'     => 'gcdevelopment',
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
                                        'module'     => 'gcdevelopment',
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
                                        'module'     => 'gcdevelopment',
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
                                        'module'     => 'gcdevelopment',
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
                                'module'     => 'gcdevelopment',
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
                                        'module'     => 'gcdevelopment',
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
                                        'module'     => 'gcdevelopment',
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
                                        'module'     => 'gcdevelopment',
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
                                        'module'     => 'gcdevelopment',
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
                                        'module'     => 'gcdevelopment',
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
                                        'module'     => 'gcdevelopment',
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
                                'module'     => 'gcdevelopment',
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
                                        'module'     => 'gcdevelopment',
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
                                        'module'     => 'gcdevelopment',
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
                                        'module'     => 'gcdevelopment',
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
                                        'module'     => 'gcdevelopment',
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
                                        'module'     => 'gcdevelopment',
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
                                        'module'     => 'gcdevelopment',
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
                                'module'     => 'gcdevelopment',
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
                                        'module'     => 'gcdevelopment',
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
                                        'module'     => 'gcdevelopment',
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
                                        'module'     => 'gcdevelopment',
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
                                'module'     => 'gcdevelopment',
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
                                        'module'     => 'gcdevelopment',
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
                                        'module'     => 'gcdevelopment',
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
                                        'module'     => 'gcdevelopment',
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
                                        'module'     => 'gcdevelopment',
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
                                        'module'     => 'gcdevelopment',
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
                                        'module'     => 'gcdevelopment',
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
                                        'module'     => 'gcdevelopment',
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
                                        'module'     => 'gcdevelopment',
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
