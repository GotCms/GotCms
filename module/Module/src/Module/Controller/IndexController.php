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
     * @var array $aclPage
     */
    protected $aclPage = array('resource' => 'Modules');

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
                $module_name = $form->getInputFilter()->get('module')->getValue();
                $object      = $this->loadBootstrap($module_name);

                if (!$object->install()) {
                    $this->flashMessenger()->addErrorMessage('Can not install this module');
                    return $this->redirect()->toRoute('module');
                } else {
                    $module_model = new ModuleModel();
                    $module_model->setName($module_name);
                    $module_model->save();

                    $select = new Sql\Select();
                    $select->from('user_acl_resource')
                        ->columns(array('id'))
                        ->where->equalTo('resource', 'Modules');

                    $insert = new Sql\Insert();
                    $insert->into('user_acl_permission')
                        ->values(
                            array(
                                'permission' => $module_name,
                                'user_acl_resource_id' => $module_model->fetchOne($select),
                            )
                        );

                    $module_model->execute($insert);

                    $insert = new Sql\Insert();
                    $insert->into('user_acl')
                        ->values(
                            array(
                                'user_acl_permission_id' => $module_model->getLastInsertId('user_acl_permission'),
                                'user_acl_role_id' => 1, //Administrator role
                            )
                        );

                    $module_model->execute($insert);

                    $this->flashMessenger()->addSuccessMessage('Module installed');
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
        $module_id    = $this->getRouteMatch()->getParam('id');
        $module_model = ModuleModel::fromId($module_id);
        if (!empty($module_model)) {
            $object = $this->loadBootstrap($module_model->getName());

            if ($object->uninstall()) {
                $select = new Sql\Select();
                $select->from('user_acl_permission')
                    ->columns(array('id'))
                    ->where->equalTo('permission', $module_model->getName());

                $user_acl_permission_id = $module_model->fetchOne($select);

                $delete = new Sql\Delete();
                $delete->from('user_acl');
                $delete->where->equalTo('user_acl_permission_id', $user_acl_permission_id);
                $module_model->execute($delete);

                $delete = new Sql\Delete();
                $delete->from('user_acl_permission');
                $delete->where->equalTo('id', $user_acl_permission_id);
                $module_model->execute($delete);

                $module_model->delete();

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
        $module_id       = $this->getRouteMatch()->getParam('m');
        $controller_name = $this->getRouteMatch()->getParam('mc', 'index');
        $action_name     = $this->getRouteMatch()->getParam('ma', 'index');
        $module_model    = ModuleModel::fromId($module_id);

        /**
         * Bootstrap event
         */
        $object = $this->loadBootstrap($module_model->getName());
        $object->init($this->getEvent());

        /**
         * Load controller and execute action
         */
        $controller_class = sprintf(
            '\\Modules\\%s\\Controller\\%s',
            $module_model->getName(),
            ucfirst($controller_name) . 'Controller'
        );

        if (!class_exists($controller_class)) {
            return false;
        }

        $action = $this->getMethodFromAction($action_name);

        $controller_object = new $controller_class($this->getRequest(), $this->getResponse());
        $controller_object->setEvent($this->getEvent());
        if (!method_exists($controller_object, $action)) {
            return false;
        }

        $result = $controller_object->$action();

        if ($result instanceof Response) {
            return $result;
        }

        if (!empty($result) and is_array($result)) {
            $model  = new ViewModel();
            $result = $model->setVariables($result);
        } elseif (empty($result)) {
            $result = new ViewModel();
        }

        //Change defaut template path
        $result->setTemplate(sprintf('%s/views/%s/%s', $module_model->getName(), $controller_name, $action_name));

        $filename = sprintf(GC_APPLICATION_PATH . '/library/Modules/%s/views/menu.phtml', $module_model->getName());
        if (file_exists($filename)) {
            $this->layout()->setVariable('moduleMenu', sprintf('%s/views/menu.phtml', $module_model->getName()));
        }

        return $result;
    }

    /**
     * Load bootstrap from module name
     *
     * @param string $module_name Module name
     *
     * @return \Gc\Module\AbstractModule
     */
    protected function loadBootstrap($module_name)
    {
        $class_name = sprintf('\\Modules\\%s\\Bootstrap', $module_name, $module_name);
        return new $class_name();
    }
}
