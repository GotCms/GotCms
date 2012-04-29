<?php
namespace Config\Form;

use Gc\Form\AbstractForm,
    Gc\User\Role\Collection as RoleCollection,
    Zend\Validator\Db,
    Zend\Validator\Identical,
    Zend\Form\Element;

class User extends AbstractForm
{
    public function init()
    {
        $this->setMethod(self::METHOD_POST);

        $email = new Element\Text('email');
        $email->setRequired(TRUE)
            ->setLabel('Email')
            ->setAttrib('class', 'input-text')
            ->addValidator('NotEmpty');

        $login = new Element\Text('login');
        $login->setLabel('Login')
            ->setAttrib('class', 'input-text');

        $password  = new Element\Password('password');
        $password->setLabel('Password')
            ->setAttrib('class', 'input-text');

        $password_confirm  = new Element\Password('password_confirm');
        $password_confirm->setLabel('Password Confirm')
            ->setAttrib('class', 'input-text');

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

    public function isvalid($data)
    {
        if(!empty($data['password']))
        {
            $this->getElement('password_confirm')->getValidator('Identical')->setToken($data['password']);
        }

        return parent::isValid($data);
    }

    public function passwordRequired()
    {
        $this->getElement('password')->setRequired(TRUE)->addValidator('NotEmpty');
        $this->getElement('password_confirm')->setRequired(TRUE)->addValidator('NotEmpty')->addValidator('Identical');
    }
}
