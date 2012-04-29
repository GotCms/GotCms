<?php

namespace Config\Controller;

use Gc\Mvc\Controller\Action,
    Gc\User,
    Gc\User\Role,
    Gc\User\Role\Rule,
    Config\Form\Role as RoleForm;

class RoleController extends Action
{
    public function indexAction()
    {
        $roles = new Role\Collection();
        $roles->init();
        return array('roles' => $roles->getRoles());
    }

    public function createAction()
    {
        $form = new RoleForm();
        $post = $this->getRequest()->post()->toArray();
        if($this->getRequest()->isPost() and $form->isValid($post))
        {
            $user_model = new User\Model();
            $user_model->setData($post);
            $user_model->save();

            $this->flashMessenger()->setNamespace('error')->addMessage('Can not connect');
        }

        return array('form' => $form);
    }

    public function deleteAction()
    {
        if($this->getRequest()->isPost())
        {
            $user_id = $this->getRequest()->post()->get('id');
            if(!empty($user_id) and $this->getAuth()->getId() == $user_id)
            {
                User\Model::fromId($user_id)->delete();
                $this->flashMessenger()->setNamespace('success')->addMessage('User deleted');
            }
        }

        return $this->redirect()->toRoute('admin');
    }

    public function editAction()
    {
        $role_id = $this->getRouteMatch()->getParam('id');
        $role_table = Role\Model::fromId($role_id);

        $form = new RoleForm();
        $form->initPermissions($role_table->getUserPermissions());
        $form->setAction($this->url()->fromRoute('userRoleEdit', array('id' => $role_id)));
        $form->populate($role_table->getData());
        $post = $this->getRequest()->post()->toArray();
        if($this->getRequest()->isPost() and $form->isValid($post))
        {
            $role_table->addData($post);
            $role_table->save();

            return $this->redirect()->toRoute('userRoleEdit', array('id' => $role_id));
        }

        return array('form' => $form);
    }
}
