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
 * @package    GcFrontend
 * @subpackage Config
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

use Gc\Core\Config as CoreConfig;
use Gc\User\Model as UserModel;
use Gc\View\Helper;
use Gc\Mvc\Resolver\AssetAliasPathStack;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Storage;
use Zend\ModuleManager\Listener;
use AssetManager\Cache\ZendCacheAdapter;

return array(
    'asset_manager' => array(
        'caching' => array(
            'default' => array(
                'cache'     => 'AssetCache',
            ),
        ),
        'resolvers' => array(
            'AssetAliasPathStack' => 2000,
        ),
    ),

    'controllers' => array(
        'invokables' => array(
            'IndexController'   => 'GcFrontend\Controller\IndexController',
            'InstallController' => 'GcFrontend\Controller\InstallController',
        ),
    ),
    'service_manager' => array(
        'factories' => array(
            'AssetAliasPathStack'        => function ($sm) {
                return new AssetAliasPathStack($sm);
            },
            'AssetCache'        => function ($sm) {
                $cacheIsActive = $sm->get('Gc\Mvc\Listener\CacheListener')->cacheIsActive(
                    $sm->get('Application')->getMvcEvent()
                );

                if ($cacheIsActive) {
                     return new ZendCacheAdapter($sm->get('Cache'));
                }

                return false;
            },
            'Auth'                  => function () {
                return new AuthenticationService(new Storage\Session(UserModel::BACKEND_AUTH_NAMESPACE));
            },
            'CoreConfig'            => function () {
                return new CoreConfig();
            },
            'Cache'                 => 'Gc\Mvc\Factory\CacheFactory',
            'CacheService'          => 'Gc\Mvc\Factory\CacheServiceFactory',
            'CustomModules'         => 'Gc\Mvc\Factory\ModuleManagerFactory',
            'ViewTemplatePathStack' => 'Gc\Mvc\Factory\ViewTemplatePathStackFactory',
        ),
        'invokables' => array(
            'Gc\Mvc\Listener\CacheListener'     => 'Gc\Mvc\Listener\CacheListener',
            'Gc\Mvc\Listener\DocumentListener'  => 'Gc\Mvc\Listener\DocumentListener',
            'Gc\Mvc\Listener\ExceptionListener' => 'Gc\Mvc\Listener\ExceptionListener',
            'Gc\Mvc\Listener\SslListener'       => 'Gc\Mvc\Listener\SslListener',
        ),
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
            'cdn'             => function ($pm) {
                return new Helper\Cdn(
                    $pm->getServiceLocator()->get('request'),
                    $pm->getServiceLocator()->get('CoreConfig')
                );
            },
            'cdnBackend'      => function ($pm) {
                $serviceLocator = $pm->getServiceLocator();
                $configuration  = $serviceLocator->get('Config');
                return new Helper\CdnBackend(
                    $serviceLocator->get('request'),
                    isset($configuration['db']) ? $serviceLocator->get('CoreConfig') : null
                );
            },
            'config'          => function ($pm) {
                return new Helper\Config($pm->getServiceLocator()->get('CoreConfig'));
            },
            'currentDocument' => function ($pm) {
                return new Helper\CurrentDocument($pm->getServiceLocator());
            },
            'partial'         => function ($pm) {
                $serviceLocator = $pm->getServiceLocator();
                $configuration  = $serviceLocator->get('Config');
                return new Helper\Partial(
                    isset($configuration['db']) ? $serviceLocator->get('CoreConfig') : null
                );
            },
            'script'          => function ($pm) {
                return new Helper\Script($pm->getServiceLocator());
            },
        ),
        'invokables' => array(
            'documents'         => 'Gc\View\Helper\Documents',
            'document'          => 'Gc\View\Helper\Document',
            'formCheckbox'      => 'Gc\View\Helper\FormCheckbox',
            'formMultiCheckbox' => 'Gc\View\Helper\FormMultiCheckbox',
            'modulePlugin'      => 'Gc\View\Helper\ModulePlugin',
            'tools'             => 'Gc\View\Helper\Tools',
        ),
    ),
    'router' => array(
        'routes' => array(
            'cms' => array(
                'type'    => 'Regex',
                'options' => array(
                    'regex' => '^/(?!admin?/)(?<path>.*)',
                    'defaults' => array(
                        'module'     =>'gcfrontend',
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
                        'module'     =>'gcfrontend',
                        'controller' => 'InstallController',
                        'action'     => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes'  => array(
                    'license' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => '/license',
                            'defaults' => array(
                                'module'     =>'gcfrontend',
                                'controller' => 'InstallController',
                                'action'     => 'license',
                            ),
                        ),
                    ),
                    'check-config' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => '/check-server-configuration',
                            'defaults' => array(
                                'module'     =>'gcfrontend',
                                'controller' => 'InstallController',
                                'action'     => 'check-config',
                            ),
                        ),
                    ),
                    'database' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => '/database-configuration',
                            'defaults' => array(
                                'module'     =>'gcfrontend',
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
                                'module'     =>'gcfrontend',
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
        'af' => 'Afrikaans',
        'ak' => 'Akan',
        'sq' => 'Shqip',
        'am' => 'አማርኛ',
        'ar' => 'العربية',
        'hy' => 'Հայերեն',
        'rup_MK' => 'Armãneashce',
        'as' => 'অসমীয়া',
        'az' => 'Azərbaycan dili',
        'az_TR' => 'Azərbaycan Türkcəsi',
        'ba' => 'башҡорт теле',
        'eu' => 'Euskara',
        'bel' => 'Беларуская мова',
        'bn_BD' => 'বাংলা',
        'bs_BA' => 'Bosanski',
        'bg_BG' => 'Български',
        'my_MM' => 'ဗမာစာ',
        'ca' => 'Català',
        'bal' => 'Català (Balear)',
        'zh_CN' => '中文',

        'zh_HK' => '香港中文版	',

        'zh_TW' => '中文',
        'co' => 'corsu',
        'hr' => 'Hrvatski',
        'cs_CZ' => 'čeština‎',
        'da_DK' => 'Dansk',
        'dv' => 'ދިވެހި',
        'nl_NL' => 'Nederlands',
        'nl_BE' => 'Nederlands (België)',
        'en_US' => 'English',
        'en_AU' => 'English (Australia)',
        'en_CA' => 'English (Canada)',
        'en_GB' => 'English (UK)',
        'eo' => 'Esperanto',
        'et' => 'Eesti',
        'fo' => 'føroyskt',
        'fi' => 'Suomi',
        'fr_BE' => 'Français de Belgique',
        'fr_FR' => 'Français',
        'fy' => 'Frysk',
        'fuc' => 'Pulaar',
        'gl_ES' => 'Galego',
        'ka_GE' => 'ქართული',
        'de_DE' => 'Deutsch',
        'el' => 'Ελληνικά',
        'gn' => 'Avañe\'ẽ',
        'haw_US' => 'Ōlelo Hawaiʻi',
        'haz' => 'هزاره گی',
        'he_IL' => 'עִבְרִית',
        'hi_IN' => 'हिन्दी',
        'hu_HU' => 'Magyar',
        'is_IS' => 'Íslenska',
        'id_ID' => 'Bahasa Indonesia',
        'ga' => 'Gaelige',
        'it_IT' => 'Italiano',
        'ja' => '日本語',
        'jv_ID' => 'Basa Jawa',
        'kn' => 'ಕನ್ನಡ',
        'kk' => 'Қазақ тілі',
        'km' => 'ភាសាខ្មែរ',
        'kin' => 'Kinyarwanda',
        'ky_KY' => 'кыргыз тили',
        'ko_KR' => '한국어',
        'ckb' => 'كوردی‎',
        'lo' => 'ພາສາລາວ',
        'lv' => 'latviešu valoda',
        'li' => 'Limburgs',
        'lt_LT' => 'Lietuvių kalba',
        'lb_LU' => 'Lëtzebuergesch',
        'mk_MK' => 'македонски јазик',
        'mg_MG' => 'Malagasy',
        'ms_MY' => 'Bahasa Melayu',
        'ml_IN' => 'മലയാളം',
        'mr' => 'मराठी',
        'xmf' => 'მარგალური ნინა',
        'mn' => 'Монгол',
        'me_ME' => 'Crnogorski jezik',
        'ne_NP' => 'नेपाली',
        'nb_NO' => 'Norsk bokmål',
        'nn_NO' => 'Norsk nynorsk',
        'os' => 'Ирон',
        'ps' => 'پښتو',
        'fa_IR' => 'فارسی',
        'fa_AF' => '(فارسی (افغانستان',
        'pl_PL' => 'Polski',
        'pt_BR' => 'Português do Brasil',
        'pt_PT' => 'Português',
        'pa_IN' => 'ਪੰਜਾਬੀ',
        'rhg' => 'Rohingya',
        'ro_RO' => 'Română',
        'ru_RU' => 'Русский',
        'ru_UA' => 'украї́нська мо́ва',
        'rue' => 'Русиньскый',
        'sah' => 'Sakha',
        'sa_IN' => 'भारतम्',
        'srd' => 'sardu',
        'gd' => 'Gàidhlig',
        'sr_RS' => 'Српски језик',
        'sd_PK' => 'سندھ',
        'si_LK' => 'සිංහල',
        'sk_SK' => 'Slovenčina',
        'sl_SI' => 'slovenščina',
        'so_SO' => 'Afsoomaali',
        'azb' => 'گؤنئی آذربایجان',
        'es_AR' => 'Español de Argentina',
        'es_CL' => 'Español de Chile',
        'es_CO' => 'Español de Colombia',
        'es_MX' => 'Español de México',
        'es_PE' => 'Español de Perú',
        'es_PR' => 'Español de Puerto Rico',
        'es_ES' => 'Español',
        'es_VE' => 'Español de Venezuela',
        'su_ID' => 'Basa Sunda',
        'sw' => 'Kiswahili',
        'sv_SE' => 'Svenska',
        'gsw' => 'Schwyzerdütsch',
        'tl' => 'Tagalog',
        'tg' => 'тоҷикӣ',
        'tzm' => 'ⵜⴰⵎⴰⵣⵉⵖⵜ',
        'ta_IN' => 'தமிழ்',
        'ta_LK' => 'தமிழ்',
        'tt_RU' => 'Татар теле',
        'te' => 'తెలుగు',
        'th' => 'ไทย',
        'bo' => 'བོད་སྐད',
        'tir' => 'ትግርኛ',
        'tr_TR' => 'Türkçe',
        'tuk' => 'Türkmençe',
        'ug_CN' => 'Uyƣurqə',
        'uk' => 'Українська',
        'ur' => 'اردو',
        'uz_UZ' => 'O‘zbekcha',
        'vi' => 'Tiếng Việt',
        'wa' => 'Walon',
        'cy' => 'Cymraeg',
    ),
);
