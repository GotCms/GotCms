<?php
namespace Config\Form;

use Gc\Form\AbstractForm,
    Zend\Validator\Db,
    Zend\Form\Element;

class UserForgotPassword extends AbstractForm
{
    public function init()
    {
        $this->setMethod(self::METHOD_POST);
        $this->addDecorator('ViewScript', array('viewScript' => 'config-forms/forgot-password.phtml'));

        $email = new Element\Text('email');
        $email->setRequired(TRUE)
            ->addValidator('NotEmpty')
            ->addValidator('EmailAddress');

        $submit = new Element\Submit('submit');
        $submit->setAttrib('class', 'input-submit')
            ->setLabel('Save');


        $this->addElements(array($email, $submit));
    }
}
