<?php
/**
 * This source file is part of Got CMS.
 *
 * Got CMS is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Got CMS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with Got CMS. If not, see <http://www.gnu.org/licenses/gpl-3.0.html>.
 *
 * PHP Version >=5.3
 *
 * @category Form
 * @package  Config
 * @author   Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license  GNU/GPL http://www.gnu.org/licenses/gpl-3.0.html
 * @link     http://www.got-cms.com
 * @license  http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Config\Form;

use Gc\Form\AbstractForm,
    Gc\User\Role\Collection as RoleCollection,
    Zend\Validator\Db,
    Zend\Validator\Identical,
    Zend\Form\Element;

class User extends AbstractForm
{
    /**
     * Initialize User form
     * @return void
     */
    public function init()
    {
        $this->setMethod(self::METHOD_POST);

        $email = new Element\Text('email');
        $email->setRequired(TRUE)
            ->setLabel('Email')
            ->setAttrib('class', 'input-text')
            ->addValidator('NotEmpty')
            ->addValidator('EmailAddress');

        $login = new Element\Text('login');
        $login->setLabel('Login')
            ->setAttrib('class', 'input-text');

        $password  = new Element\Password('password');
        $password->setLabel('Password')
            ->setAttrib('class', 'input-text')
            ->setAttrib('autocomplete', 'off');

        $password_confirm  = new Element\Password('password_confirm');
        $password_confirm->setLabel('Password Confirm')
            ->setAttrib('class', 'input-text')
            ->setAttrib('autocomplete', 'off');

        $lastname  = new Element\Text('lastname');
        $lastname->setRequired(TRUE)
            ->setLabel('Lastname')
            ->setAttrib('class', 'input-text')
            ->addValidator('NotEmpty');

        $firstname  = new Element\Text('firstname');
        $firstname->setRequired(TRUE)
            ->setLabel('Firstname')
            ->setAttrib('class', 'input-text')
            ->addValidator('NotEmpty');

        $role = new Element\Select('user_acl_role_id');
        $role->setRequired(TRUE)
            ->setLabel('Role')
            ->setAttrib('class', 'input-select')
            ->addValidator('NotEmpty');

        $role_collection = new RoleCollection();
        $roles_list = $role_collection->getRoles();
        foreach($roles_list as $role_model)
        {
            $role->addMultiOption($role_model->getId(), $role_model->getName());
        }

        $submit = new Element\Submit('submit');
        $submit->setAttrib('class', 'input-submit')
            ->setLabel('Save');


        $this->addElements(array($email, $login, $password, $password_confirm, $lastname, $firstname, $role, $submit));
    }

    /**
     * Validate the form
     *
     * @param  array $data
     * @return boolean
     */
    public function isvalid($data)
    {
        if(!empty($data['password']))
        {
            $this->getElement('password_confirm')->getValidator('Identical')->setToken($data['password']);
        }

        return parent::isValid($data);
    }

    /**
     * Set if yes or no password is required when user click on Save
     *
     * @return \Config\Form\User
     */
    public function passwordRequired()
    {
        $this->getElement('password')->setRequired(TRUE)->addValidator('NotEmpty');
        $this->getElement('password_confirm')->setRequired(TRUE)->addValidator('NotEmpty')->addValidator('Identical');

        return $this;
    }
}
