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
 * @package    Module
 * @subpackage Controller
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Module\Controller;

use Gc\Component;
use Gc\Mvc\Controller\Action;
use Gc\Module\Collection as ModuleCollection;
use Gc\Module\Model as ModuleModel;
use Gc\User\Role\Model as RoleModel;
use Module\Form\Module as ModuleForm;
use Modules;
use Zend\Db\Sql;
use Zend\Json\Json;
use Zend\Stdlib\ResponseInterface as Response;
use Zend\View\Model\ViewModel;

/**
 * Index controller
 *
 * @category   Gc_Application
 * @package    Module
 * @subpackage Controller
 */
class IndexController extends Action
{
    /**
     * Contains information about acl
     *
     * @var array
     */
    protected $aclPage = array('resource' => 'modules');

    /**
     * List all modules
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        $collection = new ModuleCollection();

        return array('modules' => $collection->getModules());
    }

    /**
     * Install module
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function installAction()
    {
        $form = new ModuleForm();
        if ($this->getRequest()->isPost()) {
            $form->setData($this->getRequest()->getPost()->toArray());
            if (!$form->isValid()) {
                $this->flashMessenger()->addErrorMessage('Invalid module');
                $this->useFlashMessenger();
            } else {
                $moduleName = $form->getInputFilter()->get('module')->getValue();
                $moduleId   = ModuleModel::install($this->getServiceLocator()->get('CustomModules'), $moduleName);
                if ($moduleId === false) {
                    $this->flashMessenger()->addErrorMessage('Can not install this module');
                    return $this->redirect()->toRoute('module');
                } else {
                    $this->flashMessenger()->addSuccessMessage('Module installed');
                    return $this->redirect()->toRoute('module/edit', array('m' => $moduleId));
                }
            }

        }

        return array('form' => $form);
    }

    /**
     * Uninstall module
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function uninstallAction()
    {
        $moduleId    = $this->getRouteMatch()->getParam('id');
        $modules     = $this->getServiceLocator()->get('CustomModules');
        $moduleModel = ModuleModel::fromId($moduleId);
        if (!empty($moduleModel)) {
            $module = $modules->getModule('Modules\\' . $moduleModel->getName());
            if (ModuleModel::uninstall($module, $moduleModel)) {
                return $this->returnJson(array('success' => true, 'message' => 'Module uninstalled'));
            }
        }

        return $this->returnJson(array('success' => false, 'message' => 'Can\'t uninstall module'));
    }

    /**
     * Load module
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function editAction()
    {
        $moduleId       = $this->getRouteMatch()->getParam('m');
        $controllerName = $this->getRouteMatch()->getParam('mc', 'index');
        $actionName     = $this->getRouteMatch()->getParam('ma', 'index');
        $moduleModel    = ModuleModel::fromId($moduleId);

        if (empty($moduleModel)) {
            return $this->redirect()->toRoute('module');
        }

        /**
         * Bootstrap event
         */
        $modules = $this->getServiceLocator()->get('CustomModules');
        $object  = $modules->getModule('Modules\\' . $moduleModel->getName());

        /**
         * Load controller and execute action
         */
        $controllerClass = sprintf(
            '\\Modules\\%s\\Controller\\%s',
            $moduleModel->getName(),
            ucfirst($controllerName) . 'Controller'
        );

        if (!class_exists($controllerClass)) {
            return false;
        }

        $action = $this->getMethodFromAction($actionName);

        $controllerObject = new $controllerClass($this->getRequest(), $this->getResponse());
        if (!method_exists($controllerObject, $action)) {
            return false;
        }

        $controllerObject->setEvent($this->getEvent());
        $controllerObject->setServiceLocator($this->getServiceLocator());
        $controllerObject->setPluginManager($this->getPluginManager());

        $result = $controllerObject->$action();

        if ($result instanceof Response) {
            return $result;
        }

        if (!empty($result) and is_array($result)) {
            $model  = new ViewModel();
            $result = $model->setVariables($result);
        } elseif (empty($result)) {
            $result = new ViewModel();
        }

        //Change defaut template path stack
        $paths        = $this->getServiceLocator()->get('ViewTemplatePathStack')->getPaths()->toArray();
        $selectedPath = null;
        $string       = 'Modules/' . $moduleModel->getName();
        foreach ($paths as $path) {
            if (strpos($path, $string) !== false) {
                $paths = explode('Modules', realpath($path));
                $selectedPath = $paths[1];
                break;
            }
        }

        $result->setTemplate(sprintf('%s/%s/%s', $selectedPath, $controllerName, $actionName));

        $filename = sprintf(GC_APPLICATION_PATH . '/library/Modules/%s/views/menu.phtml', $moduleModel->getName());
        if (file_exists($filename)) {
            $this->layout()->setVariable('moduleMenu', sprintf('%s/views/menu.phtml', $moduleModel->getName()));
        }

        return $result;
    }
}
