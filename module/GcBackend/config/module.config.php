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
 * @package    GcBackend
 * @subpackage Config
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

use Gc\Core\Config as CoreConfig;
use Gc\View\Helper;
use Gc\User\Model as UserModel;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Storage;

return array(
    'service_manager' => array(
        'factories' => array(
            'navigation' => 'Zend\Navigation\Service\DefaultNavigationFactory',
            'Auth'       => function () {
                return new AuthenticationService(new Storage\Session(UserModel::BACKEND_AUTH_NAMESPACE));
            },
            'CoreConfig' => function () {
                return new CoreConfig();
            },
            'Cache' => 'Gc\Mvc\Factory\CacheFactory',
            'CacheService' => 'Gc\Mvc\Factory\CacheServiceFactory',
            'CustomModules' => 'Gc\Mvc\Factory\ModuleManagerFactory',
        )
    ),
    'controllers' => array(
        'invokables' => array(
            'BackendController'  => 'GcBackend\Controller\AuthenticationRestController',
            'AuthenticationRest' => 'GcBackend\Controller\AuthenticationRestController',
            'DashboardRest' => 'GcBackend\Controller\DashboardRestController',
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
    'view_helpers' => array(
        'factories' => array(
            'acl' => function ($pm) {
                return new Helper\Acl(
                    $pm->getServiceLocator()->get('auth')->getIdentity()
                );
            },
            'admin' => function ($pm) {
                return new Helper\Admin(
                    $pm->getServiceLocator()->get('auth')
                );
            },
            'cdn' => function ($pm) {
                return new Helper\Cdn(
                    $pm->getServiceLocator()->get('request'),
                    $pm->getServiceLocator()->get('CoreConfig')
                );
            },
            'cdnBackend' => function ($pm) {
                $serviceLocator = $pm->getServiceLocator();
                $configuration = $serviceLocator->get('Config');
                return new Helper\CdnBackend(
                    $serviceLocator->get('request'),
                    isset($configuration['db']) ? $serviceLocator->get('CoreConfig') : null
                );
            },
            'config' => function ($pm) {
                return new Helper\Config($pm->getServiceLocator()->get('CoreConfig'));
            },
            'currentDocument' => function ($pm) {
                return new Helper\CurrentDocument($pm->getServiceLocator());
            },
            'partial' => function ($pm) {
                $serviceLocator = $pm->getServiceLocator();
                $configuration = $serviceLocator->get('Config');
                return new Helper\Partial(
                    isset($configuration['db']) ? $serviceLocator->get('CoreConfig') : null
                );
            },
            'script' => function ($pm) {
                return new Helper\Script($pm->getServiceLocator());
            },
        ),
        'invokables' => array(
            'documents' => 'Gc\View\Helper\Documents',
            'document' => 'Gc\View\Helper\Document',
            'formCheckbox' => 'Gc\View\Helper\FormCheckbox',
            'formMultiCheckbox' => 'Gc\View\Helper\FormMultiCheckbox',
            'modulePlugin' => 'Gc\View\Helper\ModulePlugin',
            'tools' => 'Gc\View\Helper\Tools',
        ),
    ),
    'router' => array(
        'routes' => array(
            'admin' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/admin',
                    'defaults' => array(
                        'module'     => 'gcbackend',
                        'controller' => 'BackendController',
                    ),
                ),
                'may_terminate' => true,
                'child_routes'  => array(
                    'login' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => '/login',
                            'defaults' => array(
                                'module'     => 'gcbackend',
                                'controller' => 'AuthenticationRest',
                            ),
                        ),
                    ),
                    'logout' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => '/logout',
                            'defaults' => array(
                                'module'     => 'gcbackend',
                                'controller' => 'DashboardRest',
                            ),
                        ),
                    ),

                    'dashboard' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => '/dashboard',
                            'defaults' => array(
                                'module'     => 'gcbackend',
                                'controller' => 'DashboardRest',
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
