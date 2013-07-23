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
 * @subpackage Form
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Config\Form;

use Gc\Form\AbstractForm;
use Gc\User\Permission;
use Zend\Form\Element;
use Zend\InputFilter\Factory as InputFilterFactory;
use Zend\Validator\Db;
use Zend\Validator\Identical;

/**
 * Role form
 *
 * @category   Gc_Application
 * @package    Config
 * @subpackage Form
 */
class Role extends AbstractForm
{
    /**
     * Initialize Role form
     *
     * @return void
     */
    public function init()
    {
        $inputFilterFactory = new InputFilterFactory();
        $inputFilter        = $inputFilterFactory->createInputFilter(
            array(
                'name' => array(
                    'required' => true,
                    'validators' => array(
                        array('name' => 'not_empty'),
                    ),
                ),
                'description' => array(
                    'required' => false,
                ),
                'permissions' => array(
                    'required' => false,
                ),
            )
        );

        $this->setInputFilter($inputFilter);

        $this->add(new Element\Text('name'));
        $this->add(new Element\Text('description'));
    }

    /**
     * Initialize permissions
     *
     * @param array $userPermissions Optional
     *
     * @return \Config\Form\Role
     */
    public function initPermissions($userPermissions = array())
    {
        $filter           = $this->getInputFilter();
        $permissionsTable = new Permission\Collection();
        $resources        = $permissionsTable->getPermissions();
        $element          = new Element('permissions');

        $data = array();
        foreach ($resources as $resource => $permissions) {
            if (empty($data[$resource])) {
                $data[$resource] = array();
            }

            foreach ($permissions as $permissionId => $permission) {
                $path = explode('/', $permission);
                if (count($path) > 1) {
                    $name = $path[0];
                } else {
                    $name = $permission;
                }


                $array = array(
                    'id' => $permissionId,
                    'name' => empty($path[1]) ? $permission : $path[1],
                    'value' => false
                );
                if (!empty($userPermissions[$resource])
                    and array_key_exists($permissionId, $userPermissions[$resource])) {
                    $array['value'] = true;
                }

                if (empty($data[$resource][$name])) {
                    $data[$resource][$name] = array();
                }
                $data[$resource][$name][] = $array;
            }
        }

        $element->setValue($data);
        $this->add($element);
    }
}
