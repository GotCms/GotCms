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
            'DatatypeRest'          => 'GcDevelopment\Controller\DatatypeRestController',
            'DevelopmentController' => 'GcDevelopment\Controller\IndexController',
            'DocumentTypeRest'      => 'GcDevelopment\Controller\DocumentTypeRestController',
            'LayoutRest'            => 'GcDevelopment\Controller\LayoutRestController',
            'PropertyRest'          => 'GcDevelopment\Controller\PropertyRestController',
            'ScriptRest'            => 'GcDevelopment\Controller\ScriptRestController',
            'TabRest'               => 'GcDevelopment\Controller\TabRestController',
            'ViewRest'              => 'GcDevelopment\Controller\ViewRestController',
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
                    'defaults' => array(
                        'module'     => 'gcdevelopment',
                        'controller' => 'DevelopmentController',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'view' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'       => '/view[/:id]',
                            'constraints' => array(
                                'id' => '[0-9]+'
                            ),
                            'defaults'    => array(
                                'module'     => 'gcdevelopment',
                                'controller' => 'ViewRest',
                            ),
                        ),
                    ),
                    'script' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'       => '/script[/:id]',
                            'constraints' => array(
                                'id' => '[0-9]+'
                            ),
                            'defaults'    => array(
                                'module'     => 'gcdevelopment',
                                'controller' => 'ScriptRest',
                            ),
                        ),
                    ),
                    'layout' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'       => '/layout[/:id]',
                            'constraints' => array(
                                'id' => '[0-9]+'
                            ),
                            'defaults'    => array(
                                'module'     => 'gcdevelopment',
                                'controller' => 'LayoutRest',
                            ),
                        ),
                    ),
                    'datatype' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'       => '/datatype[/:id]',
                            'constraints' => array(
                                'id' => '[0-9]+'
                            ),
                            'defaults'    => array(
                                'module'     => 'gcdevelopment',
                                'controller' => 'DatatypeRest',
                            ),
                        ),
                    ),
                    'document-type' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'       => '/document-type[/:id]',
                            'constraints' => array(
                                'id' => '[0-9]+'
                            ),
                            'defaults'    => array(
                                'module'     => 'gcdevelopment',
                                'controller' => 'DocumentTypeRest',
                            ),
                        ),
                    ),
                    'tab' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'       => '/tab[/:id]',
                            'constraints' => array(
                                'id' => '[0-9]+'
                            ),
                            'defaults'    => array(
                                'module'     => 'gcdevelopment',
                                'controller' => 'TabRest',
                            ),
                        ),
                    ),
                    'property' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'       => '/property[/:id]',
                            'constraints' => array(
                                'id' => '[0-9]+'
                            ),
                            'defaults'    => array(
                                'module'     => 'gcdevelopment',
                                'controller' => 'PropertyRest',
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
);
