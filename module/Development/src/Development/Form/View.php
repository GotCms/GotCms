<?php
namespace Development\Form;

use Es\Form\AbstractForm,
    Es\Validator,
    Zend\Validator\Db,
    Zend\Form\Element;

class View extends AbstractForm
{
    public function init()
    {
        $this->setMethod(self::METHOD_POST);
        $this->setElementsBelongTo('view');

        $name = new Element\Text('name');
        $name->setRequired(TRUE)
            ->setLabel('Name')
            ->setAttrib('class', 'input-text')
            ->addValidator('NotEmpty')
            ->addValidator(new Db\NoRecordExists(array(
                'table' => 'views'
                , 'field' => 'name'
                ))
            );

        $identifier  = new Element\Text('identifier');
        $identifier->setRequired(TRUE)
            ->setLabel('Identifier')
            ->setAttrib('class', 'input-text')
            ->addValidator('NotEmpty')
            ->addValidator(new Validator\Identifier())
            ->addValidator(new Db\NoRecordExists(array(
                'table' => 'views'
                , 'field' => 'identifier'
                ))
            );

        $description  = new Element\Text('description');
        $description->setLabel('Description')
            ->setAttrib('class', 'input-text');

        $content  = new Element\Textarea('content');
        $content->setLabel('Content');

        $submit = new Element\Submit('submit');
        $submit->setAttrib('class', 'input-submit')
            ->setLabel('Add');


        $this->addElements(array($name, $identifier, $description, $content, $submit));
    }
}
