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

use Gc\Mvc\Controller\Action;
use Gc\User\Role;
use Config\Form\Role as RoleForm;

/**
 * Role controller
 *
 * @category   Gc_Application
 * @package    Config
 * @subpackage Controller
 */
class RoleController extends Action
{
    /**
     * Contains information about acl
     *
     * @var array
     */
    protected $aclPage = array('resource' => 'settings', 'permission' => 'role');

    /**
     * List all roles
     *
     * @return \Zend\View\Model\ViewModel|array
     */
    public function indexAction()
    {
        $roleCollection = new Role\Collection();
        $roles          = array();
        foreach ($roleCollection->getRoles() as $role) {
            if ($role->getName() !== Role\Model::PROTECTED_NAME) {
                $roles[] = $role;
            }
        }

        return array('roles' => $roles);
    }

    /**
     * Create role
     *
     * @return \Zend\View\Model\ViewModel|array
     */
    public function createAction()
    {
        $form = new RoleForm();
        $form->initPermissions();
        $form->setAttribute('action', $this->url()->fromRoute('config/user/role/create'));

        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost()->toArray();
            $form->setData($post);
            if ($form->isValid()) {
                $roleModel = new Role\Model();
                $roleModel->addData($form->getInputFilter()->getValues());
                $roleModel->save();
                $this->flashMessenger()->addSuccessMessage('Role saved!');
                return $this->redirect()->toRoute('config/user/role/edit', array('id' => $roleModel->getId()));
            }

            $this->flashMessenger()->addErrorMessage('Role can not saved!');
            $this->useFlashMessenger();
        }

        return array('form' => $form);
    }

    /**
     * Delete role
     *
     * @return \Zend\View\Model\ViewModel|array
     */
    public function deleteAction()
    {
        $roleModel = Role\Model::fromId($this->getRouteMatch()->getParam('id'));
        if (!empty($roleModel) and $roleModel->getName() !== Role\Model::PROTECTED_NAME and $roleModel->delete()) {
            return $this->returnJson(array('success' => true, 'message' => 'Role has been deleted'));
        }

        return $this->returnJson(array('success' => false, 'message' => 'Role does not exists'));
    }

    /**
     * Edit role
     *
     * @return \Zend\View\Model\ViewModel|array
     */
    public function editAction()
    {
        $roleId = $this->getRouteMatch()->getParam('id');

        $roleModel = Role\Model::fromId($roleId);
        if (empty($roleModel) or $roleModel->getName() === Role\Model::PROTECTED_NAME) {
            $this->flashMessenger()->addErrorMessage("Can't edit this role");
            return $this->redirect()->toRoute('config/user/role');
        }

        $form = new RoleForm();
        $form->initPermissions($roleModel->getUserPermissions());
        $form->setAttribute('action', $this->url()->fromRoute('config/user/role/edit', array('id' => $roleId)));
        $form->loadValues($roleModel);
        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost()->toArray();
            $form->setData($post);
            if ($form->isValid()) {
                $roleModel->addData($form->getInputFilter()->getValues());
                $roleModel->save();

                $this->flashMessenger()->addSuccessMessage('Role saved!');
                return $this->redirect()->toRoute('config/user/role/edit', array('id' => $roleId));
            }

            $this->flashMessenger()->addErrorMessage('Role can not saved!');
            $this->useFlashMessenger();
        }

        return array('form' => $form);
    }
}
