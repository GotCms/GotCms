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
    public function __construct()
    {
        parent::__construct();
        $this->setMethod(self::METHOD_POST);

        $email = new Element('email');
        $email->setRequired(TRUE)
            ->setAttrib('type', 'text')
            ->setLabel('Email')
            ->setAttrib('class', 'input-text')
            ->addValidator('NotEmpty')
            ->addValidator('EmailAddress');

        $login = new Element('login');
        $login->setLabel('Login')
            ->setAttrib('type', 'text')
            ->setAttrib('class', 'input-text');

        $password  = new Element('password');
        $password->setLabel('Password')
            ->setAttrib('type', 'password')
            ->setAttrib('class', 'input-text')
            ->setAttrib('autocomplete', 'off');

        $password_confirm  = new Element('password_confirm');
        $password_confirm->setLabel('Password Confirm')
            ->setAttrib('type', 'password')
            ->setAttrib('class', 'input-text')
            ->setAttrib('autocomplete', 'off');

        $lastname  = new Element('lastname');
        $lastname->setRequired(TRUE)
            ->setAttrib('type', 'text')
            ->setLabel('Lastname')
            ->setAttrib('class', 'input-text')
            ->addValidator('NotEmpty');

        $firstname  = new Element('firstname');
        $firstname->setRequired(TRUE)
            ->setAttrib('type', 'text')
            ->setLabel('Firstname')
            ->setAttrib('class', 'input-text')
            ->addValidator('NotEmpty');

        $role = new Element('user_acl_role_id');
        $role->setRequired(TRUE)
            ->setAttrib('type', 'select')
            ->setLabel('Role')
            ->setAttrib('class', 'input-select')
            ->addValidator('NotEmpty');

        $role_collection = new RoleCollection();
        $roles_list = $role_collection->getRoles();
        foreach($roles_list as $role_model)
        {
            $role->addMultiOption($role_model->getId(), $role_model->getName());
        }

        $submit = new Element('submit');
        $submit->setAttrib('class', 'input-submit')
            ->setAttrib('type', 'submit')
            ->setLabel('Save');

        $this->add(array($email, $login, $password, $password_confirm, $lastname, $firstname, $role, $submit));
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
