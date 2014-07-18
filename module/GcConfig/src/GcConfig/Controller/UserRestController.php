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
 * @package    GcConfig
 * @subpackage Controller
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace GcConfig\Controller;

use GcConfig\Form\UserLogin;
use GcConfig\Form\User as UserForm;
use GcConfig\Form\UserForgotPassword as UserForgotForm;
use Gc\Mvc\Controller\RestAction;
use Gc\User;
use Gc\User\Role;
use Zend\View\Model\ViewModel;
use Zend\Validator\Identical;

/**
 * User controller
 *
 * @category   Gc_Application
 * @package    GcConfig
 * @subpackage Controller
 */
class UserRestController extends RestAction
{
    /**
     * Contains information about acl resource
     *
     * @var array
     */
    protected $aclResource = 'Settings';

    /**
     * Contains information about acl
     *
     * @var array
     */
    protected $aclPage = array('resource' => 'settings', 'permission' => 'user');

    /**
     * List all roles
     *
     * @return \Zend\View\Model\ViewModel|array
     */
    public function getList()
    {
        $userCollection = new User\Collection();
        return $this->returnJson(array(
            'users' => $userCollection->getUsers()
        ));
    }

    /**
     * Create user
     *
     * @return \Zend\View\Model\ViewModel|array
     */
    public function create($data)
    {
        var_dump($data);
        die();
        if ($this->getRequest()->isPost()) {
            $form->setData($post);
            $form->getInputFilter()
                ->get('password_confirm')
                ->getValidatorChain()
                ->addValidator(new Identical(empty($post['password']) ? null : $post['password']));

            if ($form->isValid()) {
                $userModel = new User\Model();
                $userModel->setData($post);
                $userModel->setPassword($post['password']);
                $userModel->save();
                $this->flashMessenger()->addSuccessMessage('User saved!');

                return $this->redirect()->toRoute('config/user/edit', array('id' => $userModel->getId()));
            }

            $this->useFlashMessenger();
            $this->flashMessenger()->addErrorMessage('User can not be saved');
        }

        return array('form' => $form);
    }

    /**
     * Delete user
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function delete($id)
    {
        $user = User\Model::fromId($this->getRouteMatch()->getParam('id'));
        if (!empty($user) and $user->getRole()->getName() !== Role\Model::PROTECTED_NAME and $user->delete()) {
            return $this->returnJson(array('success' => true, 'message' => 'The user has been deleted'));
        }

        return $this->returnJson(array('success' => false, 'message' => 'User does not exists'));
    }

    /**
     * Edit user
     *
     * @return \Zend\View\Model\ViewModel|array
     */
    public function update($id, $data)
    {
        $userId    = $this->getRouteMatch()->getParam('id');
        $userModel = User\Model::fromId($userId);

        if (empty($userModel)) {
            $this->flashMessenger()->addErrorMessage("Can't edit this user");
            return $this->redirect()->toRoute('config/user');
        }

        $form = new UserForm();
        $form->setAttribute('action', $this->url()->fromRoute('config/user/edit', array('id' => $userId)));
        $form->loadValues($userModel);
        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost()->toArray();
            if (!empty($post['password'])) {
                $form->passwordRequired();
                $form->getInputFilter()
                    ->get('password_confirm')
                    ->getValidatorChain()
                    ->addValidator(new Identical($post['password']));
            }

            $form->setData($post);
            if ($form->isValid()) {
                $userModel->addData($post);
                $userModel->setActive(
                    empty($post['active']) ?
                    false :
                    $post['active']
                );

                if (!empty($post['password'])) {
                    $userModel->setPassword($post['password']);
                }

                $userModel->save();
                $this->flashMessenger()->addSuccessMessage('This user has been saved');
                return $this->redirect()->toRoute('config/user/edit', array('id' => $userId));
            }

            $this->flashMessenger()->addErrorMessage('User can not be saved');
        }

        return array('form' => $form);
    }
}
