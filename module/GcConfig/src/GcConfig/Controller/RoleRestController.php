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

use GcConfig\Filter\Role as RoleFilter;
use Gc\Mvc\Controller\RestAction;
use Gc\User\Role;

/**
 * User\Role controller
 *
 * @category   Gc_Application
 * @package    GcConfig
 * @subpackage Controller
 */
class RoleRestController extends RestAction
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
    protected $aclPage = array('resource' => 'settings', 'permission' => 'role');

    /**
     * List all roles
     *
     * @return array
     */
    public function getList()
    {
        $roleCollection = new Role\Collection();
        $return         = array();
        foreach ($roleCollection->getAll() as $role) {
            $return[] = $role->toArray();
        }

        return array('roles' => $return);
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
        $roleFilter = new RoleFilter();
        $roleFilter->setData($data);
        if ($roleFilter->isValid()) {
            $roleModel = new Role\Model();
            $roleModel->setData($roleFilter->getValues());
            $roleModel->save();

            return $roleModel->toArray();
        }

        return array('content' => 'Invalid data', 'errors' => $roleFilter->getMessages());
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
        $role = Role\Model::fromId($id);
        if (empty($role)) {
            return $this->notFoundAction();
        }

        return $role->toArray();
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
        $role = Role\Model::fromId($id);
        if (!empty($role) and $role->delete()) {
            return array('success' => true, 'content' => 'This role has been deleted.');
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
        $roleModel = Role\Model::fromId($id);
        if (empty($roleModel)) {
            return $this->notFoundAction();
        }

        $roleFilter = new RoleFilter();
        $roleFilter->setData($data);
        if ($roleFilter->isValid()) {
            $values = $roleFilter->getValues();
            $roleModel->addData($values);
            $roleModel->save();

            return $roleModel->toArray();
        }

        return array('content' => 'Invalid data', 'errors' => $roleFilter->getMessages());
    }
}
