<?php
namespace Config\Form;

use Gc\Form\AbstractForm,
    Zend\Validator\Db,
    Zend\Form\Element;

class UserLogin extends AbstractForm
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

        $submit = new Element\Submit('submit');
        $submit->setAttrib('class', 'input-submit')
            ->setLabel('Log in');


        $this->addElements(array($email, $password, $submit));
    }
}
