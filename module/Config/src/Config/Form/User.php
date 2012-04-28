<?php
namespace Config\Form;

use Es\Form\AbstractForm,
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

        $password  = new Element\Password('password');
        $password->setRequired(TRUE)
            ->setLabel('Password')
            ->setAttrib('class', 'input-text')
            ->addValidator('NotEmpty');

        $password_confirm  = new Element\Password('password_confirm');
        $password_confirm->setRequired(TRUE)
            ->setLabel('Password Confirm')
            ->setAttrib('class', 'input-text')
            ->addValidator('NotEmpty')
            ->addValidator('Identical');

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

        $submit = new Element\Submit('submit');
        $submit->setAttrib('class', 'input-submit')
            ->setLabel('Save');


        $this->addElements(array($email, $password, $password_confirm, $lastname, $firstname, $submit));
    }

    public function isvalid($data)
    {
        if(!empty($data['password']))
        {
            $this->getElement('password_confirm')->getValidator('Identical')->setToken($data['password']);
        }

        return parent::isValid($data);
    }
}
