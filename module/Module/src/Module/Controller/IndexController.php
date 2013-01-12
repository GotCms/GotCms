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

use Gc\Component,
    Gc\Mvc\Controller\Action,
    Gc\Module\Collection as ModuleCollection,
    Gc\Module\Model as ModuleModel,
    Module\Form\Module as ModuleForm,
    Modules,
    Zend\Stdlib\ResponseInterface as Response,
    Zend\Json\Json,
    Zend\View\Model\ViewModel;

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
     * @var array $_aclPage
     */
    protected $_aclPage = array('resource' => 'Modules', 'permission' => 'all');

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
        if($this->getRequest()->isPost())
        {
            $form->setData($this->getRequest()->getPost()->toArray());
            if(!$form->isValid())
            {
                $this->flashMessenger()->setNameSpace('error')->addMessage('Invalid module');
                $this->useFlashMessenger();
            }
            else
            {
                $module_name = $form->getInputFilter()->get('module')->getValue();
                $object = $this->_loadBootstrap($module_name);

                if(!$object->install())
                {
                    $this->flashMessenger()->setNameSpace('error')->addMessage('Can not install this module');
                    return $this->redirect()->toRoute('module');
                }
                else
                {
                    $module_model = new ModuleModel();
                    $module_model->setName($module_name);
                    $module_model->save();

                    $this->flashMessenger()->setNameSpace('success')->addMessage('Module installed');
                    return $this->redirect()->toRoute('moduleEdit', array('m' => $module_model->getId()));
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
        $module_id = $this->getRouteMatch()->getParam('id');
        $module_model = ModuleModel::fromId($module_id);
        if(!empty($module_model))
        {
            $object = $this->_loadBootstrap($module_model->getName());

            if($object->uninstall())
            {
                $module_model->delete();
                return $this->returnJson(array('success' => TRUE, 'message' => 'Module uninstalled'));
            }
        }

        return $this->returnJson(array('success' => FALSE, 'message' => 'Can\'t uninstall module'));
    }

    /**
     * Load module
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function editAction()
    {
        $module_id = $this->getRouteMatch()->getParam('m');
        $controller_name = $this->getRouteMatch()->getParam('mc', 'index');
        $action_name = $this->getRouteMatch()->getParam('ma', 'index');

        $module_model = ModuleModel::fromId($module_id);

        /**
         * Bootstrap event
         */
        $object = $this->_loadBootstrap($module_model->getName());
        $object->init($this->getEvent());

        /**
         * Load controller and execute action
         */
        $controller_class = sprintf('\\Modules\\%s\\Controller\\%s', $module_model->getName(), ucfirst($controller_name) . 'Controller');
        if(!class_exists($controller_class))
        {
            return FALSE;
        }

        $action = $this->getMethodFromAction($action_name);

        $controller_object = new $controller_class($this->getRequest(), $this->getResponse());
        $controller_object->setEvent($this->getEvent());
        if(!method_exists($controller_object, $action))
        {
            return FALSE;
        }

        $result = $controller_object->$action();

        if($result instanceof Response)
        {
            return $result;
        }

        if(!empty($result) and is_array($result))
        {
            $model = new ViewModel();
            $result = $model->setVariables($result);
        }
        elseif(empty($result))
        {
            $result = new ViewModel();
        }

        //Change defaut template path
        $result->setTemplate(sprintf('%s/views/%s/%s', $module_model->getName(), $controller_name, $action_name));

        $this->layout()->setVariable('moduleMenu', sprintf('%s/views/menu.phtml', $module_model->getName()));

        return $result;
    }

    /**
     * Load bootstrap from module name
     *
     * @param string $module_name
     * @return \Gc\Module\AbstractModule
     */
    protected function _loadBootstrap($module_name)
    {
        $class_name = sprintf('\\Modules\\%s\\Bootstrap', $module_name, $module_name);
        return new $class_name();
    }
}
