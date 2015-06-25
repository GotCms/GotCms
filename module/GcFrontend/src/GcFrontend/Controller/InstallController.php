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
 * @subpackage Controller
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace GcFrontend\Controller;

use Gc\Core;
use Gc\Media\File;
use Gc\Media\Info;
use Gc\Layout;
use Gc\Script;
use Gc\View;
use Gc\Module\Model as ModuleModel;
use Gc\Mvc\Controller\Action;
use Gc\Version;
use GcFrontend\Form\Install;
use Zend\Db\Adapter\Adapter as DbAdapter;
use Zend\Db\TableGateway\Feature\GlobalAdapterFeature;
use Zend\Validator\AbstractValidator;
use Exception;

/**
 * Install controller for module Application
 *
 * @category   Gc_Application
 * @package    GcFrontend
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
     * @var Install
     */
    protected $installForm;

    /**
     * Initialize Installer
     *
     * @return \Zend\Http\Response|null
     */
    public function init()
    {
        $config = $this->getServiceLocator()->get('Config');
        if (isset($config['db'])) {
            return $this->redirect()->toUrl('/');
        }

        $this->layout()->setTemplate('layouts/install.phtml');
        $this->installForm = new Install();

        //Force locale to translator
        $session = $this->getSession();
        if (!empty($session['install']['lang'])) {
            $translator = $this->getServiceLocator()->get('MvcTranslator');

            $translator->addTranslationFilePattern(
                'phparray',
                GC_APPLICATION_PATH . '/data/install/translation',
                '%s.php'
            );

            $translator->setLocale(
                $session['install']['lang']
            );

            AbstractValidator::setDefaultTranslator($translator);
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
        $this->installForm->lang($this->getServiceLocator()->get('Config'));

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
            'label' => '/' . basename(GC_PUBLIC_PATH) . '/frontend',
            'value' => File::isWritable(GC_PUBLIC_PATH . '/frontend')
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
            'label' => '/templates/layout',
            'value' => is_writable(GC_TEMPLATE_PATH . '/layout')
        );
        $serverData[] = array(
            'label' => '/templates/view',
            'value' => is_writable(GC_TEMPLATE_PATH . '/view')
        );
        $serverData[] = array(
            'label' => '/templates/script',
            'value' => is_writable(GC_TEMPLATE_PATH . '/script')
        );
        $serverData[] = array(
            'label' => basename(GC_PUBLIC_PATH) . '/media',
            'value' => File::isWritable(GC_MEDIA_PATH)
        );

        $phpData   = array();
        $phpData[] = array(
            'label' => 'Php version >= 5.3.23',
            'value' => PHP_VERSION_ID >= 50323
        );
        $phpData[] = array(
            'label' => 'Xml',
            'value' => extension_loaded('xml')
        );
        $phpData[] = array(
            'label' => 'Fileinfo',
            'value' => extension_loaded('fileinfo')
        );
        $phpData[] = array(
            'label' => 'Pdo',
            'value' => extension_loaded('pdo')
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
            'value' => ini_get('display_errors') === true
        );
        $phpDirective[] = array(
            'label' => 'File Uploads',
            'needed' => true,
            'value' => ini_get('file_uploads') === true
        );
        $phpDirective[] = array(
            'label' => 'Magic Quotes Runtime',
            'needed' => false,
            'value' => ini_get('magic_quote_runtime') === true
        );
        $phpDirective[] = array(
            'label' => 'Magic Quotes GPC',
            'needed' => false,
            'value' => ini_get('magic_quote_gpc') === true
        );
        $phpDirective[] = array(
            'label' => 'Register Globals',
            'needed' => false,
            'value' => ini_get('register_globals') === true
        );
        $phpDirective[] = array(
            'label' => 'Session Auto Start',
            'needed' => false,
            'value' => ini_get('session.auto_start') === true
        );

        if ($this->getRequest()->isPost()) {
            $continue = true;
            foreach (array($serverData, $phpData) as $configs) {
                foreach ($configs as $config) {
                    if ($config['value'] !== true) {
                        $continue = false;
                        break 2;
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
        ini_set('max_execution_time', 100);
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
                            $this->createDatabase($dbAdapter, $sqlType);
                            break;

                        //Insert data
                        case 'i-d':
                            $this->insertData($dbAdapter, $session);
                            break;

                        //Insert data
                        case 'i-t':
                            $this->insertTranslations($session['install']['configuration']);
                            break;

                        //Create user and roles
                        case 'c-uar':
                            $this->createUsersAndRoles(
                                $dbAdapter,
                                $session['install']['configuration'],
                                $sqlType
                            );
                            break;

                        //Install template
                        case 'it':
                            $this->installTemplate(
                                $dbAdapter,
                                $session['install']['configuration']['template'],
                                $sqlType
                            );
                            break;

                        //Create configuration file
                        case 'c-cf':
                            return $this->completeInstallation($session['install']['db']);
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

    /**
     * Create database
     *
     * @param \Zend\Db\Adapter\Adapter $dbAdapter Database adapter
     * @param string                   $sqlType   Sql database type
     *
     * @return void
     */
    protected function createDatabase($dbAdapter, $sqlType)
    {
        $sql = file_get_contents(
            GC_APPLICATION_PATH . sprintf('/data/install/sql/database-%s.sql', $sqlType)
        );
        $dbAdapter->getDriver()->getConnection()->getResource()->exec($sql);
    }

    /**
     * Insert data into database
     *
     * @param \Zend\Db\Adapter\Adapter $dbAdapter Database adapter
     * @param \Zend\Session\Container  $session   Session array
     *
     * @return void
     */
    protected function insertData($dbAdapter, $session)
    {
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

        $sql = file_get_contents(GC_APPLICATION_PATH . '/data/install/sql/data.sql');
        $dbAdapter->getDriver()->getConnection()->getResource()->exec($sql);
    }

    /**
     * Insert translations into database
     *
     * @param array $session Session array
     *
     * @return void
     */
    protected function insertTranslations($session)
    {
        if (empty($session['copy_translations'])) {
            return;
        }

        //Save all languages in database
        $languagesFilename = glob(GC_APPLICATION_PATH . '/data/install/translation/*.php');
        $translator        = new Core\Translator;
        foreach ($languagesFilename as $language) {
            $langConfig = include $language;
            $locale     = basename($language, '.php');
            foreach ($langConfig as $source => $destination) {
                $translator->setValue(
                    $source,
                    array(
                        array(
                            'locale' => $locale,
                            'value' => $destination
                        )
                    )
                );
            }

            copy($language, GC_APPLICATION_PATH . '/data/translation/' . basename($language));
        }
    }

    /**
     * Create users and roles
     *
     * @param \Zend\Db\Adapter\Adapter $dbAdapter     Database adapter
     * @param array                    $configuration Configuration
     * @param string                   $sqlType       Sql database type
     *
     * @return \Zend\View\Model\JsonModel|null
     */
    protected function createUsersAndRoles($dbAdapter, $configuration, $sqlType)
    {
        //Create role
        $roles = include GC_APPLICATION_PATH . '/data/install/acl/roles.php';

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
        $resources = include GC_APPLICATION_PATH . '/data/install/acl/resources.php';

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
    }

    /**
     * Insert data into database
     *
     * @param \Zend\Db\Adapter\Adapter $dbAdapter Database adapter
     * @param string                   $template  Template name
     * @param string                   $sqlType   Sql database type
     *
     * @return \Zend\View\Model\JsonModel|null
     */
    protected function installTemplate($dbAdapter, $template, $sqlType)
    {
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
            $modules = $this->getServiceLocator()->get('CustomModules');
            foreach ($designInfos['modules'] as $module) {
                ModuleModel::install($modules, $module);
            }
        }

        $sql = file_get_contents($filePath);
        $dbAdapter->getDriver()->getConnection()->getResource()->exec($sql);

        File::copyDirectory($templatePath . '/frontend', GC_PUBLIC_PATH . '/frontend');
        if (file_exists($templatePath . '/files')) {
            File::copyDirectory($templatePath . '/files', GC_MEDIA_PATH . '/files');
        }

        File::copyDirectory($templatePath . '/templates', GC_APPLICATION_PATH . '/templates');
    }

    /**
     * Insert data into database
     *
     * @param array $db Database information
     *
     * @return \Zend\View\Model\JsonModel
     */
    protected function completeInstallation(array $db)
    {
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

        $configFilename = GC_APPLICATION_PATH . '/config/autoload/local.php';
        file_put_contents($configFilename, $file);
        chmod($configFilename, $this->umask);
        $translator      = $this->getServiceLocator()->get('MvcTranslator');
        $completeMessage = $translator->translate(
            'Installation complete. ' .
            'Please refresh or go to /admin page to manage your website.'
        );
        return $this->returnJson(array('message' => $completeMessage));
    }
}
