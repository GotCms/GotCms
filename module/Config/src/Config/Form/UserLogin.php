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
        $this->addDecorator('ViewScript', array('viewScript' => 'config-forms/login.phtml'));

        $email = new Element\Text('login');
        $email->setRequired(TRUE)
            ->addValidator('NotEmpty');

        $password  = new Element\Password('password');
        $password->setRequired(TRUE)
            ->addValidator('NotEmpty');

        $this->addElements(array($email, $password));
    }
}
