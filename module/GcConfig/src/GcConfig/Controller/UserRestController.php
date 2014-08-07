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

use GcConfig\Filter\User as UserFilter;
use Gc\Mvc\Controller\RestAction;
use Gc\User;
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
     * @return array
     */
    public function getList()
    {
        $userCollection = new User\Collection();
        $return         = array();
        foreach ($userCollection->getAll() as $user) {
            $return[] = $user->toArray();
        }

        return array('users' => $return);
    }

    /**
     * Create user
     *
     * @param array $data Data to use
     *
     * @return array
     */
    public function create($data)
    {
        $userFilter = new UserFilter($this->getServiceLocator()->get('DbAdapter'));
        $userFilter->passwordRequired();
        $userFilter->setData($data);
        $userFilter->get('password_confirm')
            ->getValidatorChain()
            ->addValidator(new Identical(empty($data['password']) ? null : $data['password']));

        if ($userFilter->isValid()) {
            $userModel = new User\Model();
            $userModel->setData($userFilter->getValues());
            $userModel->setPassword($data['password']);
            $userModel->save();

            $userModel->unsPassword();

            return $userModel->toArray();
        }

        return array('content' => 'Invalid data', 'errors' => $userFilter->getMessages());
    }

    /**
     * Get user
     *
     * @param integer $id Id of the user
     *
     * @return array
     */
    public function get($id)
    {
        $user = User\Model::fromId($id);
        if (empty($user)) {
            return $this->notFoundAction();
        }

        return $user->toArray();
    }

    /**
     * Delete user
     *
     * @param integer $id Id of the user
     *
     * @return array
     */
    public function delete($id)
    {
        $user = User\Model::fromId($id);
        if (!empty($user) and $user->delete()) {
            return array('success' => true, 'content' => 'This user has been deleted.');
        }

        return $this->notFoundAction();
    }

    /**
     * Edit user
     *
     * @param integer $id   Id of the user
     * @param array   $data Data to use
     *
     * @return array
     */
    public function update($id, $data)
    {
        $userModel = User\Model::fromId($id);
        if (empty($userModel)) {
            return $this->notFoundAction();
        }

        $userFilter = new UserFilter($this->getServiceLocator()->get('DbAdapter'));
        $userFilter->loadValues($userModel);
        if (!empty($data['password'])) {
            $userFilter->passwordRequired();
            $userFilter->get('password_confirm')
                ->getValidatorChain()
                ->addValidator(new Identical($data['password']));
        }

        $userFilter->addData($data);
        if ($userFilter->isValid()) {
            $values = $userFilter->getValues();
            $userModel->addData($values);
            $userModel->setActive(
                empty($values['active']) ?
                false :
                $values['active']
            );

            if (!empty($values['password'])) {
                $userModel->setPassword($values['password']);
            }

            $userModel->save();
            $userModel->unsPassword();

            return $userModel->toArray();
        }

        return array('content' => 'Invalid data', 'errors' => $userFilter->getMessages());
    }
}
