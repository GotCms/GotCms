<?php
class Development_Form_Layout extends Es_Form
{
    public function init()
    {
        $this->setMethod(self::METHOD_POST);
        $this->setElementsBelongTo('layout');

        $name = new Element\Text('name');
        $name->setRequired(TRUE)
            ->setLabel('Name')
            ->setAttrib('class', 'input-text')
            ->addValidator('NotEmpty')
            ->addValidator(new Db\NoRecordExists(array(
                'table' => 'layouts'
                , 'field' => 'name')
                )
            );

        $identifier  = new Element\Text('identifier');
        $identifier->setRequired(TRUE)
            ->setLabel('Identifier')
            ->setAttrib('class', 'input-text')
            ->addValidator('NotEmpty')
            ->addValidator(new Validator\Identifier())
            ->addValidator(new Db\NoRecordExists(array(
                'table' => 'layouts'
                , 'field' => 'identifier')
                )
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
