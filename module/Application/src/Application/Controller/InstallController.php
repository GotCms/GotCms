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
 * @subpackage Controller
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Application\Controller;

use Gc\Core;
use Gc\Media\File;
use Gc\Media\Info;
use Gc\Module\Model as ModuleModel;
use Gc\Mvc\Controller\Action;
use Gc\Registry;
use Gc\Version;
use Application\Form\Install;
use Zend\Config\Reader\Ini;
use Zend\Db\TableGateway\Feature\GlobalAdapterFeature;
use Zend\Db\Adapter\Adapter as DbAdapter;
use Exception;

/**
 * Install controller for module Application
 *
 * @category   Gc_Application
 * @package    Application
 * @subpackage Controller
 */
class InstallController extends Action
{
    /**
     * Umask
     *
     * @var integer
     */
     protected $umask = 0774;

    /**
     * Install form
     *
     * @var Application\Form\Install
     */
    protected $installForm;

    /**
     * Initialize Installer
     *
     * @return void
     */
    public function init()
    {
        $this->layout()->setTemplate('layouts/install.phtml');
        $this->installForm = new Install();
        if (file_exists(GC_APPLICATION_PATH . '/config/autoload/global.php')) {
            return $this->redirect()->toUrl('/');
        }

        //Force locale to translator
        $session = $this->getSession();
        if (!empty($session['install']['lang'])) {
            $this->getServiceLocator()->get('translator')->setLocale(
                $session['install']['lang']
            );
        }
    }

    /**
     * Select language in first page
     *
     * @return array
     */
    public function indexAction()
    {
        $this->checkInstall(1);
        $this->installForm->lang();

        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost()->toArray();
            $this->installForm->setData($postData);
            if ($this->installForm->isValid()) {
                $session            = $this->getSession();
                $session['install'] = array('lang' => $this->installForm->getInputFilter()->getValue('lang'));

                return $this->redirect()->toRoute('install/license');
            }
        }

        $this->layout()->setVariables(array('currentRoute' => $this->getRouteMatch()->getMatchedRouteName()));
        return array('form' => $this->installForm);
    }

    /**
     * Display license
     *
     * @return array
     */
    public function licenseAction()
    {
        $this->checkInstall(2);
        $this->installForm->license();

        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost()->toArray();
            $this->installForm->setData($postData);
            if ($this->installForm->isValid()) {
                return $this->redirect()->toRoute('install/check-config');
            }
        }

        $this->layout()->setVariables(array('currentRoute' => $this->getRouteMatch()->getMatchedRouteName()));
        return array(
            'form' => $this->installForm,
            'license' => file_get_contents(GC_APPLICATION_PATH . '/LICENSE.txt')
        );
    }

    /**
     * Check configuration
     *
     * @return array
     */
    public function checkConfigAction()
    {
        $this->checkInstall(3);
        if (!defined('PHP_VERSION_ID')) {
            $version = explode('.', PHP_VERSION);
            define('PHP_VERSION_ID', ($version[0] * 10000 + $version[1] * 100 + $version[2]));
        }

        $serverData   = array();
        $serverData[] = array(
            'label' => '/'.basename(dirname(GC_FRONTEND_PATH)).'/'.basename(GC_FRONTEND_PATH),
            'value' => File::isWritable(GC_FRONTEND_PATH)
        );
        $serverData[] = array(
            'label' => '/config/autoload',
            'value' => File::isWritable(GC_APPLICATION_PATH . '/config/autoload')
        );
        $serverData[] = array(
            'label' => '/data/cache',
            'value' => is_writable(GC_APPLICATION_PATH . '/data/cache')
        );
        $serverData[] = array(
            'label' => '/'.basename(dirname(GC_MEDIA_PATH)).'/'.basename(GC_MEDIA_PATH),
            'value' => File::isWritable(GC_MEDIA_PATH)
        );

        $phpData   = array();
        $phpData[] = array(
            'label' => 'Php version >= 5.3.3',
            'value' => PHP_VERSION_ID > 50303
        );
        $phpData[] = array(
            'label' => 'Pdo',
            'value' => extension_loaded('pdo')
        );
        $phpData[] = array(
            'label' => 'Xml',
            'value' => extension_loaded('xml')
        );
        $phpData[] = array(
            'label' => 'Intl',
            'value' => extension_loaded('intl')
        );
        $phpData[] = array(
            'label' => 'Database (Mysql, Pgsql)',
            'value' => extension_loaded('pdo_mysql') or extension_loaded('pdo_pgsql')
        );
        $phpData[] = array(
            'label' => 'Mbstring',
            'value' => extension_loaded('mbstring')
        );
        $phpData[] = array(
            'label' => 'Json',
            'value' => function_exists('json_encode') and function_exists('json_decode')
        );

        $phpDirective   = array();
        $phpDirective[] = array(
            'label' => 'Display Errors',
            'needed' => false,
            'value' => ini_get('display_errors') == true
        );
        $phpDirective[] = array(
            'label' => 'File Uploads',
            'needed' => true,
            'value' => ini_get('file_uploads') == true
        );
        $phpDirective[] = array(
            'label' => 'Magic Quotes Runtime',
            'needed' => false,
            'value' => ini_get('magic_quote_runtime') == true
        );
        $phpDirective[] = array(
            'label' => 'Magic Quotes GPC',
            'needed' => false,
            'value' => ini_get('magic_quote_gpc') == true
        );
        $phpDirective[] = array(
            'label' => 'Register Globals',
            'needed' => false,
            'value' => ini_get('register_globals') == true
        );
        $phpDirective[] = array(
            'label' => 'Session Auto Start',
            'needed' => false,
            'value' => ini_get('session.auto_start') == true
        );

        if ($this->getRequest()->isPost()) {

            $continue = true;
            foreach (array($serverData, $phpData) as $configs) {
                foreach ($configs as $config) {
                    if ($config['label'] !== 'Intl') {
                        if ($config['value'] !== true) {
                            $continue = false;
                            break 2;
                        }
                    }
                }
            }

            if ($continue) {
                return $this->redirect()->toRoute('install/database');
            } else {
                $this->flashMessenger()->addErrorMessage('All parameters must be set to "Yes"');
                return $this->redirect()->toRoute('install/check-config');
            }
        }

        $this->layout()->setVariables(array('currentRoute' => $this->getRouteMatch()->getMatchedRouteName()));
        return array(
            'gitProject'   => file_exists(GC_APPLICATION_PATH . '/.git'),
            'phpData'      => $phpData,
            'phpDirective' => $phpDirective,
            'serverData'   => $serverData,
            'cmsVersion'   => Version::VERSION
        );
    }

    /**
     * Display database information
     *
     * @return array
     */
    public function databaseAction()
    {
        $this->checkInstall(4);
        $this->installForm->database();
        $messages = array();
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost()->toArray();
            $this->installForm->setData($data);
            if ($this->installForm->isValid()) {
                //Test database connexion
                $values = $this->installForm->getInputFilter()->getValues();
                $config = array(
                    'driver' => $values['driver'],
                    'username' => $values['username'],
                    'database' => $values['dbname'],
                    'hostname' => $values['hostname'],
                    'password' => empty($values['password']) ? '' : $values['password'],
                );

                try {
                    $dbAdapter = new DbAdapter($config);
                    $dbAdapter->getDriver()->getConnection()->connect();

                    $session            = $this->getSession();
                    $install            = $session['install'];
                    $install['db']      = $config;
                    $session['install'] = $install;

                    return $this->redirect()->toRoute('install/configuration');
                } catch (Exception $e) {
                    $messages[] = 'Can\'t connect to database';
                    $messages[] = $e->getMessage();
                }
            }
        }

        $this->layout()->setVariables(array('currentRoute' => $this->getRouteMatch()->getMatchedRouteName()));
        return array('form' => $this->installForm, 'messages' => $messages);
    }

    /**
     * Configuration
     *
     * @return array
     */
    public function configurationAction()
    {
        $this->checkInstall(5);
        $this->installForm->configuration();

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost()->toArray();
            $this->installForm->setData($data);
            if ($this->installForm->isValid()) {
                $values = $this->installForm->getInputFilter()->getValues();

                $session                  = $this->getSession();
                $install                  = $session['install'];
                $install['configuration'] = $values;
                $session['install']       = $install;

                return $this->redirect()->toRoute('install/complete');
            }
        }

        $this->layout()->setVariables(array('currentRoute' => $this->getRouteMatch()->getMatchedRouteName()));
        return array('form' => $this->installForm);
    }


    /**
     * Complete installation
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function completeAction()
    {
        $this->checkInstall(6);
        if ($this->getRequest()->isXmlHttpRequest()) {
            if ($this->getRequest()->isPost()) {
                $session = $this->getSession();

                $dbAdapter = new DbAdapter($session['install']['db']);
                $dbAdapter->getDriver()->getConnection()->connect();

                GlobalAdapterFeature::setStaticAdapter($dbAdapter);

                $step    = $this->getRequest()->getPost()->get('step');
                $sqlType = str_replace('pdo_', '', $session['install']['db']['driver']);
                try {
                    switch($step) {
                        //Create database
                        case 'c-db':
                            $sql = file_get_contents(
                                GC_APPLICATION_PATH . sprintf('/data/install/sql/database-%s.sql', $sqlType)
                            );
                            $dbAdapter->getDriver()->getConnection()->getResource()->exec($sql);
                            break;

                        //Insert data
                        case 'i-d':
                            $configuration = $session['install']['configuration'];
                            $dbAdapter->query(
                                "INSERT INTO core_config_data
                                (identifier, value) VALUES ('site_name', ?);",
                                array($configuration['site_name'])
                            );
                            $dbAdapter->query(
                                "INSERT INTO core_config_data
                                (identifier, value) VALUES ('site_is_offline', ?);",
                                array($configuration['site_is_offline'])
                            );
                            $dbAdapter->query(
                                "INSERT INTO core_config_data
                                (identifier, value) VALUES ('cookie_domain', ?);",
                                array($this->getRequest()->getUri()->getHost())
                            );
                            $dbAdapter->query(
                                "INSERT INTO core_config_data
                                (identifier, value) VALUES ('session_lifetime', '3600');",
                                array()
                            );
                            $dbAdapter->query(
                                "INSERT INTO core_config_data
                                (identifier, value) VALUES ('locale', ?);",
                                array($session['install']['lang'])
                            );
                            $dbAdapter->query(
                                "INSERT INTO core_config_data
                                (identifier, value) VALUES ('mail_from', ?);",
                                array($configuration['admin_email'])
                            );
                            $dbAdapter->query(
                                "INSERT INTO core_config_data
                                (identifier, value) VALUES ('mail_from_name', ?);",
                                array($configuration['admin_firstname'] . ' ' . $configuration['admin_lastname'])
                            );


                            $languageFilename = sprintf(
                                GC_APPLICATION_PATH . '/data/install/translation/%s.php',
                                $session['install']['lang']
                            );
                            if (file_exists($languageFilename)) {
                                $translator = new Core\Translator;
                                $langConfig = include $languageFilename;
                                foreach ($langConfig as $source => $destination) {
                                    $translator->setValue(
                                        $source,
                                        array(
                                            array(
                                                'locale' => $session['install']['lang']
                                                , 'value' => $destination
                                            )
                                        )
                                    );
                                }
                            }

                            $sql = file_get_contents(GC_APPLICATION_PATH . '/data/install/sql/data.sql');
                            $dbAdapter->getDriver()->getConnection()->getResource()->exec($sql);
                            break;

                        //Create user and roles
                        case 'c-uar':
                            //Create role
                            $ini   = new Ini();
                            $roles = $ini->fromFile(GC_APPLICATION_PATH . '/data/install/scripts/roles.ini');

                            try {
                                foreach ($roles['role'] as $key => $value) {
                                    $statement = $dbAdapter->createStatement(
                                        "INSERT INTO user_acl_role (name) VALUES ('" . $value . "')"
                                    );
                                    $result    = $statement->execute();
                                }
                            } catch (Exception $e) {
                                return $this->returnJson(array('messages' => $e->getMessage()));
                            }

                            //resources
                            $ini       = new Ini();
                            $resources = $ini->fromFile(GC_APPLICATION_PATH . '/data/install/scripts/resources.ini');

                            try {
                                foreach ($resources as $key => $value) {
                                    $statement = $dbAdapter->createStatement(
                                        "INSERT INTO user_acl_resource (resource) VALUES ('" . $key . "')"
                                    );
                                    $result    = $statement->execute();

                                    $statement    = $dbAdapter->createStatement(
                                        "SELECT id FROM user_acl_resource WHERE resource =  '" . $key . "'"
                                    );
                                    $result       = $statement->execute();
                                    $lastInsertId = $result->current();
                                    $lastInsertId = $lastInsertId['id'];

                                    $permissions = array();
                                    foreach ($value as $k => $v) {
                                        if (!in_array($k, $permissions)) {
                                            $statement     = $dbAdapter->createStatement(
                                                "INSERT INTO user_acl_permission
                                                (
                                                    permission,
                                                    user_acl_resource_id
                                                )
                                                VALUES ('" . $k . "', '" . $lastInsertId . "')"
                                            );
                                            $result        = $statement->execute();
                                            $permissions[] = $k;
                                        }
                                    }
                                }

                                foreach ($resources as $key => $value) {
                                    $statement            = $dbAdapter->createStatement(
                                        "SELECT id FROM user_acl_resource WHERE resource =  '" . $key . "'"
                                    );
                                    $result               = $statement->execute();
                                    $lastResourceInsertId = $result->current();
                                    $lastResourceInsertId = $lastResourceInsertId['id'];

                                    foreach ($value as $k => $v) {
                                        $statement    = $dbAdapter->createStatement(
                                            "SELECT id
                                            FROM user_acl_permission
                                            WHERE permission =  '" . $k . "'
                                                AND user_acl_resource_id = '" . $lastResourceInsertId . "'"
                                        );
                                        $result       = $statement->execute();
                                        $lastInsertId = $result->current();
                                        $lastInsertId = $lastInsertId['id'];

                                        $statement = $dbAdapter->createStatement(
                                            "SELECT id FROM user_acl_role WHERE name = '" . $v . "'"
                                        );
                                        $result    = $statement->execute();
                                        $role      = $result->current();
                                        if (!empty($role['id'])) {
                                            $statement = $dbAdapter->createStatement(
                                                "INSERT INTO user_acl
                                                (
                                                    user_acl_role_id,
                                                    user_acl_permission_id
                                                )
                                                VALUES ('" . $role['id'] . "', " . $lastInsertId . ')'
                                            );
                                            $result    = $statement->execute();
                                        }
                                    }
                                }
                            } catch (Exception $e) {
                                return $this->returnJson(array('messages' => $e->getMessage()));
                            }

                            //Add admin user
                            $configuration = $session['install']['configuration'];
                            if ($sqlType == 'mysql') {
                                $sqlString = 'INSERT INTO `user`
                                    (
                                        created_at,
                                        updated_at,
                                        lastname,
                                        firstname,
                                        email,
                                        login,
                                        password,
                                        user_acl_role_id
                                    )
                                    VALUES (NOW(), NOW(), ?, ?, ?, ?, ?, 1)';
                            } else {
                                $sqlString = 'INSERT INTO "user"
                                    (
                                        created_at,
                                        updated_at,
                                        lastname,
                                        firstname,
                                        email,
                                        login,
                                        password,
                                        user_acl_role_id
                                    )
                                    VALUES (NOW(), NOW(), ?, ?, ?, ?, ?, 1)';
                            }

                            $dbAdapter->query(
                                $sqlString,
                                array(
                                    $configuration['admin_lastname'],
                                    $configuration['admin_firstname'],
                                    $configuration['admin_email'],
                                    $configuration['admin_login'],
                                    sha1($configuration['admin_password'])
                                )
                            );
                            break;

                        //Install template
                        case 'it':
                            $template     = $session['install']['configuration']['template'];
                            $templatePath = GC_APPLICATION_PATH . sprintf('/data/install/design/%s', $template);
                            $info         = new Info();
                            $info->fromFile($templatePath . '/design.info');
                            $filePath = sprintf('%s/sql/%s.sql', $templatePath, $sqlType);
                            if (!file_exists($filePath)) {
                                return $this->returnJson(
                                    array(
                                        'success' => false,
                                        'message' => sprintf(
                                            'Could not find data for this template and driver: Driver %s, path %s',
                                            $sqlType,
                                            $templatePath
                                        )
                                    )
                                );
                            }

                            $designInfos = $info->getInfos();
                            if (!empty($designInfos['modules'])) {
                                foreach ($designInfos['modules'] as $module) {
                                    ModuleModel::install($module);
                                }
                            }

                            $sql = file_get_contents($filePath);
                            $dbAdapter->getDriver()->getConnection()->getResource()->exec($sql);

                            File::copyDirectory($templatePath . '/frontend', GC_FRONTEND_PATH);
                            if (file_exists($templatePath . '/files')) {
                                File::copyDirectory($templatePath . '/files', GC_MEDIA_PATH . '/files');
                            }
                            break;

                        //Create configuration file
                        case 'c-cf':
                            $db   = $session['install']['db'];
                            $file = file_get_contents(GC_APPLICATION_PATH . '/data/install/tpl/config.tpl.php');
                            $file = str_replace(
                                array(
                                    '__DRIVER__',
                                    '__USERNAME__',
                                    '__PASSWORD__',
                                    '__DATABASE__',
                                    '__HOSTNAME__',
                                ),
                                array(
                                    $db['driver'],
                                    $db['username'],
                                    $db['password'],
                                    $db['database'],
                                    $db['hostname'],
                                ),
                                $file
                            );

                            $configFilename = GC_APPLICATION_PATH . '/config/autoload/global.php';
                            file_put_contents($configFilename, $file);
                            chmod($configFilename, $this->umask);

                            return $this->returnJson(
                                array(
                                    'message' => 'Installation complete.
                                    Please refresh or go to /admin page to manage your website.'
                                )
                            );
                            break;
                                                }
                } catch (Exception $e) {
                    return $this->returnJson(array('success' => false, 'message' => $e->getMessage()));
                }

                return $this->returnJson(array('success' => true));
            }
        }
    }

    /**
     * Check install step
     *
     * @param string $step Installation step
     *
     * @return \Zend\View\Model\ViewModel
     */
    protected function checkInstall($step)
    {
        $session = $this->getSession();
        if ($step == 1) {
            return true;
        }

        //step 2 or higher
        if (empty($session['install']) or empty($session['install']['lang'])) {
            return $this->redirect()->toRoute('install');
        }

        //Higher than 4
        if ($step > 4) {
            if (empty($session['install']['db'])) {
                return $this->redirect()->toRoute('install/database');
            }
        }

        //Higher than 5
        if ($step > 5) {
            if (empty($session['install']['configuration'])) {
                return $this->redirect()->toRoute('install/configuration');
            }
        }
    }
}
