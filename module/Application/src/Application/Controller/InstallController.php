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

use Gc\Mvc\Controller\Action,
    Gc\Version,
    Application\Form\Install,
    Zend\Db\Adapter\Adapter as DbAdapter;

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
     protected $_umask = 0774;

    /**
     * Install form
     *
     * @var Application\Form\Install
     */
    protected $_installForm;

    /**
     * Initialize Installer
     *
     * @return void
     */
    public function init()
    {
        $this->layout()->setTemplate('layouts/install.phtml');
        $this->_installForm = new Install();
        if(file_exists(GC_APPLICATION_PATH . '/config/autoload/global.php'))
        {
            return $this->redirect()->toUrl('/');
        }

        //Force locale to translator
        $session = $this->getSession();
        if(!empty($session['install']['lang']))
        {
            \Gc\Registry::get('Translator')->setLocale($session['install']['lang']);
        }
    }

    /**
     * Select language in first page
     *
     * @return array
     */
    public function indexAction()
    {
        $this->_checkInstall(1);
        $this->_installForm->lang();

        if($this->getRequest()->isPost())
        {
            $post_data = $this->getRequest()->getPost()->toArray();
            $this->_installForm->setData($post_data);
            if($this->_installForm->isValid())
            {
                $session = $this->getSession();
                $session['install'] = array('lang' => $this->_installForm->getInputFilter()->getValue('lang'));

                return $this->redirect()->toRoute('installLicense');
            }
        }

        $this->layout()->setVariables(array('currentRoute' => $this->getRouteMatch()->getMatchedRouteName()));
        return array('form' => $this->_installForm);
    }

    /**
     * Display license
     *
     * @return array
     */
    public function licenseAction()
    {
        $this->_checkInstall(2);
        $this->_installForm->license();

        if($this->getRequest()->isPost())
        {
            $post_data = $this->getRequest()->getPost()->toArray();
            $this->_installForm->setData($post_data);
            if($this->_installForm->isValid())
            {
                return $this->redirect()->toRoute('installCheckConfig');
            }
        }

        $this->layout()->setVariables(array('currentRoute' => $this->getRouteMatch()->getMatchedRouteName()));
        return array('form' => $this->_installForm, 'license' => file_get_contents(GC_APPLICATION_PATH . '/LICENSE.txt'));
    }

    /**
     * Check configuration
     *
     * @return array
     */
    public function checkConfigAction()
    {
        $this->_checkInstall(3);
        if(!defined('PHP_VERSION_ID'))
        {
           $version = explode('.', PHP_VERSION);
           define('PHP_VERSION_ID', ($version[0] * 10000 + $version[1] * 100 + $version[2]));
        }

        $server_data = array();
        $server_data[] = array('label' => '/public/frontend', 'value' => is_writable(GC_APPLICATION_PATH . '/public/frontend'));
        $server_data[] = array('label' => '/config/autoload', 'value' => is_writable(GC_APPLICATION_PATH . '/config/autoload'));
        $server_data[] = array('label' => '/data/cache', 'value' => is_writable(GC_APPLICATION_PATH . '/data/cache'));
        $server_data[] = array('label' => '/public/media', 'value' => is_writable(GC_MEDIA_PATH));

        $php_data = array();
        $php_data[] = array('label' => 'Php version >= 5.3.3', 'value' => PHP_VERSION_ID > 50303);
        $php_data[] = array('label' => 'Pdo', 'value' => extension_loaded('pdo'));
        $php_data[] = array('label' => 'Xml', 'value' => extension_loaded('xml'));
        $php_data[] = array('label' => 'Intl', 'value' => extension_loaded('intl'));
        $php_data[] = array('label' => 'Database (Mysql, Pgsql)', 'value' => extension_loaded('pdo_mysql') or extension_loaded('pdo_pgsql'));
        $php_data[] = array('label' => 'Mbstring', 'value' => extension_loaded('mbstring'));
        $php_data[] = array('label' => 'Json', 'value' => function_exists('json_encode') and function_exists('json_decode'));

        $php_directive = array();
        $php_directive[] = array('label' => 'Display Errors', 'needed' => FALSE, 'value' => ini_get('display_errors') == TRUE);
        $php_directive[] = array('label' => 'File Uploads', 'needed' => TRUE, 'value' => ini_get('file_uploads') == TRUE);
        $php_directive[] = array('label' => 'Magic Quotes Runtime', 'needed' => FALSE, 'value' => ini_get('magic_quote_runtime') == TRUE);
        $php_directive[] = array('label' => 'Magic Quotes GPC', 'needed' => FALSE, 'value' => ini_get('magic_quote_gpc') == TRUE);
        $php_directive[] = array('label' => 'Register Globals', 'needed' => FALSE, 'value' => ini_get('register_globals') == TRUE);
        $php_directive[] = array('label' => 'Session Auto Start', 'needed' => FALSE, 'value' => ini_get('session.auto_start') == TRUE);

        if($this->getRequest()->isPost())
        {
            return $this->redirect()->toRoute('installDatabase');
        }

        $this->layout()->setVariables(array('currentRoute' => $this->getRouteMatch()->getMatchedRouteName()));
        return array('phpData' => $php_data, 'phpDirective' => $php_directive, 'serverData' => $server_data, 'cmsVersion' => Version::VERSION);
    }

    /**
     * Display database information
     *
     * @return array
     */
    public function databaseAction()
    {
        $this->_checkInstall(4);
        $this->_installForm->database();
        $messages = array();
        if($this->getRequest()->isPost())
        {
            $data = $this->getRequest()->getPost()->toArray();
            $this->_installForm->setData($data);
            if($this->_installForm->isValid())
            {
                //Test database connexion
                $values = $this->_installForm->getInputFilter()->getValues();
                $config = array(
                    'driver' => $values['driver'],
                    'username' => $values['username'],
                    'database' => $values['dbname'],
                    'hostname' => $values['hostname'],
                    'password' => empty($values['password']) ? '' : $values['password'],
                );

                try
                {
                    $db_adapter = new DbAdapter($config);
                    $db_adapter->getDriver()->getConnection()->connect();

                    $session = $this->getSession();
                    $install = $session['install'];
                    $install['db'] = $config;

                    $session['install'] = $install;

                    return $this->redirect()->toRoute('installConfiguration');
                }
                catch(\Exception $e)
                {
                    $messages[] = 'Can\'t connect to database';
                    $messages[] = $e->getMessage();
                }
            }
        }

        $this->layout()->setVariables(array('currentRoute' => $this->getRouteMatch()->getMatchedRouteName()));
        return array('form' => $this->_installForm, 'messages' => $messages);
    }

    /**
     * Configuration
     *
     * @return array
     */
    public function configurationAction()
    {
        $this->_checkInstall(5);
        $this->_installForm->configuration();

        if($this->getRequest()->isPost())
        {
            $data = $this->getRequest()->getPost()->toArray();
            $this->_installForm->setData($data);
            if($this->_installForm->isValid())
            {
                $values = $this->_installForm->getInputFilter()->getValues();

                $session = $this->getSession();
                $install = $session['install'];
                $install['configuration'] = $values;
                $session['install'] = $install;

                return $this->redirect()->toRoute('installComplete');
            }
        }

        $this->layout()->setVariables(array('currentRoute' => $this->getRouteMatch()->getMatchedRouteName()));
        return array('form' => $this->_installForm);
    }


    /**
     * Complete installation
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function completeAction()
    {
        $this->_checkInstall(6);
        if($this->getRequest()->isXmlHttpRequest())
        {
            if($this->getRequest()->isPost())
            {
                $session = $this->getSession();

                $db_adapter = new DbAdapter($session['install']['db']);
                $db_adapter->getDriver()->getConnection()->connect();

                $step = $this->getRequest()->getPost()->get('step');
                $sql_type = str_replace('pdo_', '', $session['install']['db']['driver']);
                try
                {
                    switch($step)
                    {
                        //Create database
                        case 'c-db':
                            $sql = file_get_contents(GC_APPLICATION_PATH . sprintf('/data/install/sql/database-%s.sql', $sql_type));
                            $db_adapter->getDriver()->getConnection()->getResource()->exec($sql);
                        break;

                        //Insert data
                        case 'i-d':
                            $configuration = $session['install']['configuration'];
                            $db_adapter->query("INSERT INTO core_config_data (identifier, value) VALUES ('site_name', ?);", array($configuration['site_name']));
                            $db_adapter->query("INSERT INTO core_config_data (identifier, value) VALUES ('site_is_offline', ?);", array($configuration['site_is_offline']));
                            $db_adapter->query("INSERT INTO core_config_data (identifier, value) VALUES ('cookie_domain', ?);", array($this->getRequest()->getUri()->getHost()));
                            $db_adapter->query("INSERT INTO core_config_data (identifier, value) VALUES ('session_lifetime', '3600');", array());
                            $db_adapter->query("INSERT INTO core_config_data (identifier, value) VALUES ('locale', ?);", array($session['install']['lang']));
                            $db_adapter->query("INSERT INTO core_config_data (identifier, value) VALUES ('mail_from', ?);", array($configuration['admin_email']));
                            $db_adapter->query("INSERT INTO core_config_data (identifier, value) VALUES ('mail_from_name', ?);", array($configuration['admin_firstname'] . ' ' . $configuration['admin_lastname']));


                            $language_filename = sprintf(GC_APPLICATION_PATH . '/data/translate/%s.php', $session['install']['lang']);
                            if(file_exists($language_filename))
                            {
                                \Zend\Db\TableGateway\Feature\GlobalAdapterFeature::setStaticAdapter($db_adapter);
                                \Gc\Registry::set('Configuration', array('db' => $session['install']['db']));
                                $lang_config = include $language_filename;
                                foreach($lang_config as $source => $destination)
                                {
                                    \Gc\Core\Translator::setValue($source, array(array(
                                        'locale' => $session['install']['lang']
                                        , 'value' => $destination)
                                    ));
                                }
                            }

                            $sql = file_get_contents(GC_APPLICATION_PATH . '/data/install/sql/data.sql');
                            $db_adapter->getDriver()->getConnection()->getResource()->exec($sql);
                        break;

                        //Create user and roles
                        case 'c-uar':
                            //Create role
                            $ini = new \Zend\Config\Reader\Ini();
                            $roles = $ini->fromFile(GC_APPLICATION_PATH . '/data/install/scripts/roles.ini');

                            try
                            {
                                foreach($roles['role'] as $key=>$value)
                                {
                                    $statement = $db_adapter->createStatement("INSERT INTO user_acl_role (name) VALUES ('" . $value . "')");
                                    $result = $statement->execute();
                                }
                            }
                            catch(Exception $e)
                            {
                                return $this->returnJson(array('messages' => $e->getMessage()));
                            }

                            //resources
                            $ini = new \Zend\Config\Reader\Ini();
                            $resources = $ini->fromFile(GC_APPLICATION_PATH . '/data/install/scripts/resources.ini');

                            try
                            {
                                foreach($resources as $key => $value)
                                {
                                    $statement = $db_adapter->createStatement("INSERT INTO user_acl_resource (resource) VALUES ('" . $key . "')");
                                    $result = $statement->execute();

                                    $statement = $db_adapter->createStatement("SELECT id FROM user_acl_resource WHERE resource =  '" . $key . "'");
                                    $result = $statement->execute();
                                    $last_insert_id = $result->current();
                                    $last_insert_id = $last_insert_id['id'];

                                    $permissions = array();
                                    foreach($value as $k => $v)
                                    {
                                        if(!in_array($k, $permissions))
                                        {
                                            $statement = $db_adapter->createStatement("INSERT INTO user_acl_permission (permission, user_acl_resource_id) VALUES ('".$k."', '".$last_insert_id."')");
                                            $result = $statement->execute();
                                            $permissions[] = $k;
                                        }
                                    }
                                }

                                foreach($resources as $key => $value)
                                {
                                    $statement = $db_adapter->createStatement("SELECT id FROM user_acl_resource WHERE resource =  '" . $key . "'");
                                    $result = $statement->execute();
                                    $last_resource_insert_id = $result->current();
                                    $last_resource_insert_id = $last_resource_insert_id['id'];

                                    foreach($value as $k => $v)
                                    {
                                        $statement = $db_adapter->createStatement("SELECT id FROM user_acl_permission WHERE permission =  '" . $k . "' AND user_acl_resource_id = '" .$last_resource_insert_id . "'");
                                        $result = $statement->execute();
                                        $last_insert_id = $result->current();
                                        $last_insert_id = $last_insert_id['id'];

                                        $statement = $db_adapter->createStatement("SELECT id FROM user_acl_role WHERE name = '" . $v . "'");
                                        $result = $statement->execute();
                                        $role = $result->current();
                                        if(!empty($role['id']))
                                        {
                                            $statement = $db_adapter->createStatement("INSERT INTO user_acl (user_acl_role_id, user_acl_permission_id) VALUES ('".$role['id']."', " . $last_insert_id . ")");
                                            $result = $statement->execute();
                                        }
                                    }
                                }
                            }
                            catch(Exception $e)
                            {
                                return $this->returnJson(array('messages' => $e->getMessage()));
                            }

                            //Add admin user
                            $configuration = $session['install']['configuration'];
                            if($sql_type == 'mysql')
                            {
                                $sql_string = "INSERT INTO `user` (created_at, updated_at, lastname, firstname, email, login, password, user_acl_role_id) VALUES (NOW(), NOW(), ?, ?, ?, ?, ?, 1)";
                            }
                            else
                            {
                                $sql_string = "INSERT INTO \"user\" (created_at, updated_at, lastname, firstname, email, login, password, user_acl_role_id) VALUES (NOW(), NOW(), ?, ?, ?, ?, ?, 1)";
                            }

                            $db_adapter->query($sql_string,
                                array($configuration['admin_lastname'], $configuration['admin_firstname'], $configuration['admin_email'], $configuration['admin_login'], sha1($configuration['admin_password'])));
                        break;

                        //Install template
                        case 'it':
                            $template = $session['install']['configuration']['template'];
                            $template_path = GC_APPLICATION_PATH . sprintf('/data/install/design/%s', $template);
                            $file_path = sprintf('%s/%s.sql', $template_path, $sql_type);
                            if(!file_exists($file_path))
                            {
                                return $this->returnJson(array('success' => FALSE, 'message' => sprintf('Could not find data for this template and driver: Driver %s, path %s', $sql_type, $template_path)));
                            }

                            $sql = file_get_contents($file_path);
                            $db_adapter->getDriver()->getConnection()->getResource()->exec($sql);

                            $this->_copyDirectory($template_path . '/frontend', GC_APPLICATION_PATH . '/public/frontend');
                            if(file_exists($template_path . '/files'))
                            {
                                $this->_copyDirectory($template_path . '/files', GC_MEDIA_PATH . '/files');
                            }
                        break;

                        //Create configuration file
                        case 'c-cf':
                            $db = $session['install']['db'];
                            $file = file_get_contents(GC_APPLICATION_PATH . '/data/install/tpl/config.tpl.php');
                            $file = str_replace(array(
                                '__DRIVER__',
                                '__USERNAME__',
                                '__PASSWORD__',
                                '__DATABASE__',
                                '__HOSTNAME__',
                            ), array(
                                $db['driver'],
                                $db['username'],
                                $db['password'],
                                $db['database'],
                                $db['hostname'],
                            ), $file);

                            $config_filename = GC_APPLICATION_PATH . '/config/autoload/global.php';
                            file_put_contents($config_filename, $file);
                            chmod($config_filename, $this->_umask);

                            return $this->returnJson(array('message' => 'Installation complete. Please refresh or go to /admin page to manage your website.'));
                        break;
                    }
                }
                catch(Exception $e)
                {
                    return $this->returnJson(array('success' => FALSE, 'message' => $e->getMessage()));
                }

                return $this->returnJson(array('success' => TRUE));
            }
        }
    }

    /**
     * Check install step
     *
     * @param string $step
     * @return \Zend\View\Model\ViewModel
     */
    protected function _checkInstall($step)
    {
        $session = $this->getSession();
        if($step == 1)
        {
            return TRUE;
        }

        //step 2 or higher
        if(empty($session['install']) or empty($session['install']['lang']))
        {
            return $this->redirect()->toRoute('install');
        }

        //Higher than 4
        if($step > 4)
        {
            if(empty($session['install']['db']))
            {
                return $this->redirect()->toRoute('installDatabase');
            }
        }

        //Higher than 5
        if($step > 5)
        {
            if(empty($session['install']['configuration']))
            {
                return $this->redirect()->toRoute('configuration');
            }
        }
    }

    /**
     * Copy directory from source to destination
     *
     * @param string $source
     * @param string $destination
     * @return boolean
     */
    protected function _copyDirectory($source, $destination)
    {
        if(is_dir($source))
        {
            if(!file_exists($destination))
            {
                @mkdir($destination, 0777);
            }

            $directory = dir($source);
            while(FALSE !== ($read_directory = $directory->read()))
            {
                if($read_directory == '.' || $read_directory == '..')
                {
                    continue;
                }

                $path_dir = $source . '/' . $read_directory;
                $this->_copyDirectory($path_dir, $destination . '/' . $read_directory);
            }

            $directory->close();
        }
        else
        {
            $result = copy($source, $destination);
            @chmod($destination, $this->_umask);

            return $result;
        }

        return TRUE;
    }
}
