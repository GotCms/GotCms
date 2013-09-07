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

use Gc\Core\Config as CoreConfig;
use Gc\Module\Collection as ModuleCollection;
use Gc\User\Model as UserModel;
use Gc\View\Helper;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Storage;
use Zend\ModuleManager\Listener;
use Zend\ModuleManager\ModuleManager;

return array(
    'controllers' => array(
        'invokables' => array(
            'IndexController'   => 'Application\Controller\IndexController',
            'InstallController' => 'Application\Controller\InstallController',
        ),
    ),
    'service_manager' => array(
        'factories' => array(
            'Auth' => function ($sm) {
                return new AuthenticationService(new Storage\Session(UserModel::BACKEND_AUTH_NAMESPACE));
            },
            'CoreConfig' => function ($sm) {
                return new CoreConfig();
            },
            'CustomModules' => 'Gc\Mvc\Service\ModuleManagerFactory',
            'translator'    => 'Zend\Mvc\Service\TranslatorServiceFactory',
        )
    ),
    'translator' => array(
        'locale' => 'en_GB',
        'translation_file_patterns' => array(
            array(
                'type'     => 'phparray',
                'base_dir' => GC_APPLICATION_PATH . '/data/translation',
                'pattern'  => '%s.php',
            ),
        ),
    ),
    'view_manager' => array(
        'display_not_found_reason'  => false,
        'display_exceptions'        => false,
        'doctype'                   => 'HTML5',
        'not_found_template'        => 'error/404',
        'exception_template'        => 'error/index',
        'template_path_stack' => array(
            'application' => __DIR__ . '/../views',
        ),
        'template_map' => array(
            'error/404'     => __DIR__ . '/../views/error/404.phtml',
            'error/index'   => __DIR__ . '/../views/error/index.phtml',
        ),
    ),
    'view_helpers' => array(
        'factories' => array(
            'acl'        => function ($pm) {
                return new Helper\Acl(
                    $pm->getServiceLocator()->get('auth')->getIdentity()
                );
            },
            'admin'        => function ($pm) {
                return new Helper\Admin(
                    $pm->getServiceLocator()->get('auth')
                );
            },
            'cdn'        => function ($pm) {
                return new Helper\Cdn(
                    $pm->getServiceLocator()->get('request'),
                    $pm->getServiceLocator()->get('CoreConfig')
                );
            },
            'cdnBackend' => function ($pm) {
                $serviceLocator = $pm->getServiceLocator();
                $configuration  = $serviceLocator->get('Config');
                return new Helper\CdnBackend(
                    $serviceLocator->get('request'),
                    isset($configuration['db']) ? $serviceLocator->get('CoreConfig') : null
                );
            },
            'config'     => function ($pm) {
                return new Helper\Config($pm->getServiceLocator()->get('CoreConfig'));
            },
            'script'     => function ($pm) {
                return new Helper\Script($pm->getServiceLocator());
            },
        ),
        'invokables' => array(
            'documents'         => 'Gc\View\Helper\Documents',
            'document'          => 'Gc\View\Helper\Document',
            'formCheckbox'      => 'Gc\View\Helper\FormCheckbox',
            'formMultiCheckbox' => 'Gc\View\Helper\FormMultiCheckbox',
            'partial'           => 'Gc\View\Helper\Partial',
            'tools'             => 'Gc\View\Helper\Tools',
            'modulePlugin'      => 'Gc\View\Helper\ModulePlugin',
        ),
    ),
    'router' => array(
        'routes' => array(
            'cms' => array(
                'type'    => 'Regex',
                'options' => array(
                    'regex' => '^/(?!admin?/)(?<path>.*)',
                    'defaults' => array(
                        'module'     =>'application',
                        'controller' => 'IndexController',
                        'action'     => 'index',
                    ),
                    'spec' => '/%path%',
                ),
            ),
            'install' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/install',
                    'defaults' => array(
                        'module'     =>'application',
                        'controller' => 'InstallController',
                        'action'     => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes'  => array(
                    'check-config' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => '/check-server-configuration',
                            'defaults' => array(
                                'module'     =>'application',
                                'controller' => 'InstallController',
                                'action'     => 'check-config',
                            ),
                        ),
                    ),
                    'license' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => '/license',
                            'defaults' => array(
                                'module'     =>'application',
                                'controller' => 'InstallController',
                                'action'     => 'license',
                            ),
                        ),
                    ),
                    'database' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => '/database-configuration',
                            'defaults' => array(
                                'module'     =>'application',
                                'controller' => 'InstallController',
                                'action'     => 'database',
                            ),
                        ),
                    ),
                    'configuration' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => '/configuration',
                            'defaults' => array(
                                'module'     =>'application',
                                'controller' => 'InstallController',
                                'action'     => 'configuration',
                            ),
                        ),
                    ),
                    'complete' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => '/complete',
                            'defaults' => array(
                                'module'     => 'application',
                                'controller' => 'InstallController',
                                'action'     => 'complete',
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),

    'locales' => array(
        'en_GB' => 'English',
        'fr_FR' => 'Français',
        'ru_RU' => 'Русский',
    )
);
