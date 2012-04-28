<?php
namespace Config\Form;

use Gc\Form\AbstractForm,
    Zend\Validator\Db,
    Zend\Validator\Identical,
    Zend\Form\Element;

class Role extends AbstractForm
{
    public function init()
    {
        $this->setMethod(self::METHOD_POST);

        $name = new Element\Text('name');
        $name->setRequired(TRUE)
            ->setLabel('Name')
            ->setAttrib('class', 'input-text')
            ->addValidator('NotEmpty');

        $description  = new Element\Text('description');
        $description->setRequired(TRUE)
            ->setLabel('Description')
            ->setAttrib('class', 'input-text')
            ->addValidator('NotEmpty');

        $submit = new Element\Submit('submit');
        $submit->setAttrib('class', 'input-submit')
            ->setLabel('Save');


        $this->addElements(array($name, $description, $submit));
    }


    public function initPermissions($permissions)
    {
        foreach($permissions as $permission)
        {
            var_dump($permission);
        }
        die();
    }
}
