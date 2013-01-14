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

use Gc\Form\AbstractForm,
    Gc\User\Permission,
    Zend\Form\Element,
    Zend\InputFilter\Factory as InputFilterFactory,
    Zend\Validator\Db,
    Zend\Validator\Identical;

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
        $input_filter_factory = new InputFilterFactory();
        $input_filter = $input_filter_factory->createInputFilter(array(
            'name' => array(
                'required' => TRUE,
                'validators' => array(
                    array('name' => 'not_empty'),
                ),
            ),
            'description' => array(
                'required' => FALSE,
            ),
            'permissions' => array(
                'required' => FALSE,
            ),
        ));

        $this->setInputFilter($input_filter);

        $this->add(new Element\Text('name'));
        $this->add(new Element\Text('description'));
    }

    /**
     * Initialize permissions
     *
     * @param array $user_permissions Optional
     * @return \Config\Form\Role
     */
    public function initPermissions($user_permissions = array())
    {
        $filter = $this->getInputFilter();
        $permissions_table = new Permission\Collection();
        $resources = $permissions_table->getPermissions();
        $element = new Element('permissions');

        $data = $resources;
        foreach($resources as $resource => $permissions)
        {
            foreach($permissions as $permission_id => $permission)
            {
                $data[$resource][$permission_id] = array('name' => $permission, 'value' => FALSE);
                if(!empty($user_permissions[$resource]) and array_key_exists($permission_id, $user_permissions[$resource]))
                {
                    $data[$resource][$permission_id]['value'] = TRUE;
                }
            }
        }

        $element->setValue($data);
        $this->add($element);
    }
}
