<?php
/**
 * This source file is part of Got CMS.
 *
 * Got CMS is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Got CMS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License along
 * with Got CMS. If not, see <http://www.gnu.org/licenses/lgpl-3.0.html>.
 *
 * PHP Version >=5.3
 *
 * @category Form
 * @package  Config
 * @author   Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license  GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link     http://www.got-cms.com
 */

namespace Config\Form;

use Gc\Form\AbstractForm,
    Zend\Validator\Db,
    Zend\Validator\Identical,
    Zend\Form\Element,
    Gc\User\Permission;

class Role extends AbstractForm
{
    /**
     * Initialize Role form
     * @return void
     */
    public function init()
    {
        $this->setMethod(self::METHOD_POST);

        $name = new Element\Text('name');
        $name->setRequired(TRUE)
            ->setLabel('Name')
            ->setAttrib('class', 'input-text')
            ->addValidator('NotEmpty');

        $description  = new Element\Text('description');
        $description->setLabel('Description')
            ->setAttrib('class', 'input-text');

        $submit = new Element\Submit('submit');
        $submit->setAttrib('class', 'input-submit')
            ->setLabel('Save');


        $this->addElements(array($name, $description, $submit));
    }

    /**
     * Initialize permissions
     *
     * @return \Config\Form\Role
     */
    public function initPermissions($user_permissions = NULL)
    {
        $permissions_table = new Permission\Collection();
        $resources = $permissions_table->getPermissions();
        foreach($resources as $resource => $permissions)
        {
            foreach($permissions as $permission_id => $permission)
            {
                $element = new Element\Checkbox((string)$permission_id);
                $element->setBelongsTo('permissions');
                $element->setLabel($permission);

                if(!empty($user_permissions[$resource]) and array_key_exists($permission_id, $user_permissions[$resource]))
                {
                    $element->setValue(TRUE);
                }

                $this->addElement($element);
            }
        }
    }
}
