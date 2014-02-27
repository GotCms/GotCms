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
 * @category   Gc
 * @package    Admin
 * @subpackage Config
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

return array(
    'service_manager' => array(
        'factories' => array(
            'navigation' => 'Zend\Navigation\Service\DefaultNavigationFactory',
        )
    ),
    'controllers' => array(
        'invokables' => array(
            'AdminController' => 'Admin\Controller\IndexController',
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'admin' => __DIR__ . '/../views',
        ),
        'template_map' => array(
            'layout/layout' => __DIR__ . '/../views/layouts/layout.phtml',
        ),
        'strategies' => array(
            'ViewJsonStrategy',
        ),
    ),
    'router' => array(
        'routes' => array(
            'admin' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/admin',
                    'defaults' =>
                    array (
                        'module'     => 'admin',
                        'controller' => 'AdminController',
                        'action'     => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes'  => array(
                    'dashboard-save' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => '/dashboard/save',
                            'defaults' =>
                            array (
                                'module'     => 'admin',
                                'controller' => 'AdminController',
                                'action'     => 'save-dashboard',
                            ),
                        ),
                    ),
                    'translator.js' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => '/translator.js',
                            'defaults' => array(
                                'module'     =>'admin',
                                'controller' => 'AdminController',
                                'action'     => 'translator',
                            ),
                        ),
                    ),
                    'keep-alive' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => '/keep-alive',
                            'defaults' => array(
                                'module'     =>'admin',
                                'controller' => 'AdminController',
                                'action'     => 'keep-alive',
                            ),
                        ),
                    ),
                )
            ),
        ),
    ),
    'navigation' => array(
        'default' => array(
            array(
                'label' => 'Dashboard',
                'route' => 'admin',
                'pages' => array(
                    array(
                        'label' => 'Content',
                        'route' => 'content',
                        'pages' => array(
                            array(
                                'label' => 'Translations',
                                'route' => 'content/translation',
                                'pages' => array(
                                    array(
                                        'label' => 'Create',
                                        'route' => 'content/translation/create',
                                    )
                                )
                            ),
                            array(
                                'label' => 'File manager',
                                'route' => 'content/media',
                            ),
                            array(
                                'label' => 'Document',
                                'route' => 'content/document',
                                'pages' => array(
                                    array(
                                        'label' => 'Create',
                                        'route' => 'content/document/create',
                                    ),
                                    array(
                                        'label' => 'Create',
                                        'route' => 'content/document/create-w-parent',
                                    ),
                                    array(
                                        'label' => 'Create',
                                        'route' => 'content/document/create',
                                    ),
                                    array(
                                        'label' => 'Edit',
                                        'route' => 'content/document/edit',
                                    ),
                                ),
                            ),
                        )
                    ),
                    array(
                        'label' => 'Development',
                        'route' => 'development',
                        'pages' => array(
                            array(
                                'label' => 'Document type',
                                'route' => 'development/document-type',
                                'pages' => array(
                                    array(
                                        'label' => 'Create',
                                        'route' => 'development/document-type/create',
                                    ),
                                    array(
                                        'label' => 'Edit',
                                        'route' => 'development/document-type/edit',
                                    ),
                                ),
                            ),
                            array(
                                'label' => 'View',
                                'route' => 'development/view',
                                'pages' => array(
                                    array(
                                        'label' => 'Create',
                                        'route' => 'development/view/create',
                                    ),
                                    array(
                                        'label' => 'Edit',
                                        'route' => 'development/view/edit',
                                    ),
                                ),
                            ),
                            array(
                                'label' => 'Layout',
                                'route' => 'development/layout',
                                'pages' => array(
                                    array(
                                        'label' => 'Create',
                                        'route' => 'development/layout/create',
                                    ),
                                    array(
                                        'label' => 'Edit',
                                        'route' => 'development/layout/edit',
                                    ),
                                ),
                            ),
                            array(
                                'label' => 'Script',
                                'route' => 'development/script',
                                'pages' => array(
                                    array(
                                        'label' => 'Create',
                                        'route' => 'development/script/create',
                                    ),
                                    array(
                                        'label' => 'Edit',
                                        'route' => 'development/script/edit',
                                    ),
                                ),
                            ),
                            array(
                                'label' => 'Datatypes',
                                'route' => 'development/datatype',
                                'pages' => array(
                                    array(
                                        'label' => 'Create',
                                        'route' => 'development/datatype/create',
                                    ),
                                    array(
                                        'label' => 'Edit',
                                        'route' => 'development/datatype/edit',
                                    ),
                                ),
                            ),
                        )
                    ),
                    array(
                        'label' => 'Modules',
                        'route' => 'module',
                        'pages' => array(
                            array(
                                'label' => 'Install',
                                'route' => 'module/install',
                            ),
                        )
                    ),
                    array(
                        'label' => 'Statistics',
                        'route' => 'statistics',
                    ),
                    array(
                        'label' => 'Config',
                        'route' => 'config',
                        'pages' => array(
                            array(
                                'label' => 'General',
                                'route' => 'config/general',
                            ),
                            array(
                                'label' => 'System',
                                'route' => 'config/system',
                            ),
                            array(
                                'label' => 'Server',
                                'route' => 'config/server',
                            ),
                            array(
                                'label' => 'Update',
                                'route' => 'config/cms-update',
                            ),
                            array(
                                'label' => 'User',
                                'route' => 'config/user',
                                'pages' => array(
                                    array(
                                        'label' => 'Create',
                                        'route' => 'config/user/create',
                                    ),
                                    array(
                                        'label' => 'Edit',
                                        'route' => 'config/user/edit',
                                    ),
                                    array(
                                        'label' => 'Role',
                                        'route' => 'config/user/role',
                                        'pages' => array(
                                            array(
                                                'label' => 'Create',
                                                'route' => 'config/user/role/create',
                                            ),
                                            array(
                                                'label' => 'Edit',
                                                'route' => 'config/user/role/edit',
                                            ),
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                )
            ),
        )
    )
);
