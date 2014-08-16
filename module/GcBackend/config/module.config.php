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
            'AuthenticationRest'   => 'GcBackend\Controller\AuthenticationRestController',
            'DeauthenticationRest' => 'GcBackend\Controller\DeauthenticationRestController',
            'DashboardRest'        => 'GcBackend\Controller\DashboardRestController',
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
            'modulePlugin' => 'Gc\View\Helper\ModulePlugin',
            'tools' => 'Gc\View\Helper\Tools',
        ),
    ),
    'router' => array(
        'routes' => array(
            'install' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/install',
                    'defaults' => array(
                        'module'     => 'gcbackend',
                        'controller' => 'InstallController',
                    ),
                ),
            ),
            'admin' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/backend',
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
                                'controller' => 'DeauthenticationRest',
                            ),
                        ),
                    ),
                    'forgot-password' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => '/password-reset[/:id][/:key]',
                            'defaults' => array(
                                'module'     => 'gcbackend',
                                'controller' => 'ForgotPasswordRest',
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
                    ),
                )
            ),
        )
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
        'zh_HK' => '香港中文版',
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
