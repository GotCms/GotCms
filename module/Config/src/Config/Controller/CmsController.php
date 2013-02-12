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
 * @package    Config
 * @subpackage Controller
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Config\Controller;

use Gc\Mvc\Controller\Action,
    Gc\Core\Config,
    Gc\Core\Updater,
    Gc\Media\Info,
    Gc\Version,
    Config\Form\Config as configForm;

/**
 * Cms controller
 *
 * @category   Gc_Application
 * @package    Config
 * @subpackage Controller
 */
class CmsController extends Action
{
    /**
     * Config form
     *
     * @var \Config\Form\Config $_form
     */
    protected $_form;

    /**
     * Contains information about acl
     *
     * @var array
     */
    protected $_aclPage = array('resource' => 'Config', 'permission' => 'system');

    /**
     * Generate general configuration form
     *
     * @return void
     */
    public function editGeneralAction()
    {
        $this->_form = new configForm();
        $this->_form->initGeneral();
        return $this->forward()->dispatch('CmsController', array('action' => 'edit'));
    }

    /**
     * Generate system configuration form
     *
     * @return void
     */
    public function editSystemAction()
    {
        $this->_form = new configForm();
        $this->_form->initSystem();
        return $this->forward()->dispatch('CmsController', array('action' => 'edit'));
    }

    /**
     * Generate server configuration form
     *
     * @return void
     */
    public function editServerAction()
    {
        $this->_form = new configForm();
        $this->_form->initServer();
        return $this->forward()->dispatch('CmsController', array('action' => 'edit'));
    }

    /**
     * Generate form and display
     *
     * @return \Zend\View\Model\ViewModel|array
     */
    public function editAction()
    {
        $values = Config::getValues();
        $this->_form->setValues($values);

        if($this->getRequest()->isPost())
        {
            $this->_form->setData($this->getRequest()->getPost()->toArray());

            if(!$this->_form->isValid())
            {
                $this->flashMessenger()->setNameSpace('error')->addMessage('Can not save configuration');
                $this->useFlashMessenger();
            }
            else
            {
                $inputs = $this->_form->getInputFilter()->getValidInput();
                foreach($inputs as $input)
                {
                    if(method_exists($input, 'getName'))
                    {
                        Config::setValue($input->getName(), $input->getValue());
                    }
                }

                $this->flashMessenger()->setNameSpace('success')->addMessage('Configuration saved');
                return $this->redirect()->toRoute($this->getRouteMatch()->getMatchedRouteName());
            }
        }

        return array('form' => $this->_form);
    }

    /**
     * Update cms
     */
    public function updateAction()
    {
        $version_is_latest = Version::isLatest();
        $latest_version = Version::getLatest();
        $session = $this->getSession();

        if($this->getRequest()->isPost())
        {
            $updater = new Updater();
            if(!$updater->load($this->getRequest()->getPost()->get('adapter')) or $version_is_latest)
            {
                $this->flashMessenger()->setNameSpace('error')->addMessage('Can\'t set adapter');
                return $this->redirect()->toRoute('cmsUpdate');
            }

            $current_version = Version::VERSION;
            $output = '';
            if($updater->update())
            {
                //Fetch content
                if($updater->upgrade())
                {
                    //Upgrade cms
                    //Update database
                    if(!$updater->updateDatabase())
                    {
                        //Upgrade cms
                        $updater->rollback($current_version);
                    }
                    else
                    {
                        $updater->executeScripts();
                        $session['updateOutput'] = $updater->getMessages();

                        $this->flashMessenger()->setNameSpace('success')->addMessage(sprintf('Cms update to %s', $latest_version));
                        return $this->redirect()->toRoute('cmsUpdate');
                    }
                }
            }

            foreach($updater->getMessages() as $message)
            {
                $this->flashMessenger()->setNameSpace('error')->addMessage($message);
            }

            return $this->redirect()->toRoute('cmsUpdate');
        }

        if(!empty($session['updateOutput']))
        {
            $update_output = $session['updateOutput'];
            unset($session['updateOutput']);
        }

        //Check modules and datatypes
        $datatypes_errors = array();
        $this->_checkVersion(glob(GC_APPLICATION_PATH . '/library/Datatypes/*'), 'datatype', $datatypes_errors);
        $modules_errors = array();
        $this->_checkVersion(glob(GC_APPLICATION_PATH . '/library/Modules/*'), 'module', $modules_errors);

        return array(
            'gitProject' => file_exists(GC_APPLICATION_PATH . '/.git'),
            'isLatest' => $version_is_latest,
            'latestVersion' => $latest_version,
            'datatypesErrors' => $datatypes_errors,
            'modulesErrors' => $modules_errors,
            'updateOutput' => empty($update_output) ? '' : $update_output,
        );
    }

    /**
     * Check version in info file
     * from $type directory
     *
     * @param array $directories list of directories
     * @param string $type Type of directory
     * @param array $errors Insert in this all errors
     */
    protected function _checkVersion(array $directories, $type, array &$errors)
    {
        $latest_version = Version::getLatest();
        foreach($directories as $directory)
        {
            if(is_dir($directory))
            {
                $filename = $directory . '/ ' . $type . '.info';
                $info = new Info();

                if($info->fromFile($filename) === TRUE)
                {
                    $infos = $info->getInfos();
                    if(!empty($infos['version']))
                    {
                        preg_match('~(?<operator>[>=]*)(?<version>.+)~', $infos['version'], $matches);
                        if(empty($matches['operator']))
                        {
                            if(version_compare($latest_version, $matches['version']) === 1)
                            {
                                $errors[] = basename($directory);
                            }
                        }
                        else
                        {
                            if(!version_compare($latest_version, $matches['version'], $matches['operator']))
                            {
                                $errors[] = $directory;
                            }
                        }
                    }
                }
            }
        }
    }
}
