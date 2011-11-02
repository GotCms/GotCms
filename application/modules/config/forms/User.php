<?php

class Config_Form_User extends Zend_Form
{
    public function init()
    {
        $lastname = new Zend_Form_Element_Text('lastname');
        $lastname->setRequired(TRUE)
            ->addValidator(new Zend_Validate_Alnum());
        $this->addElement($lastname);

        $firstname = new Zend_Form_Element_Text('firstname');
        $firstname->setRequired(TRUE);
        $this->addElement($firstname);

        $email = new Zend_Form_Element_Text('email');
        $email->setRequired(TRUE)
            ->addValidator(new Zend_Validate_EmailAddress());
        $this->addElement($email);

        $password = new Zend_Form_Element_Text('password');
        $password->setRequired(FALSE);
        $this->addElement($password);

        $confirm = new Zend_Form_Element_Text('password_confirm');
        $confirm->setRequired(FALSE);
        $this->addElement($confirm);
    }
}

