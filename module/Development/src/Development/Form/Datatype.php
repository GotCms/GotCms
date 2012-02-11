<?php
namespace Development\Form;

use Es\Form,
    Zend\Form\Element;

class Datatype extends Form
{
    public function init()
    {
        $this->setMethod(self::METHOD_POST);

        $name = new Element\Text('name');
        $name->setRequired(TRUE)
            ->setLabel('Name')
            ->setAttrib('class', 'input-text')
            ->addValidator('NotEmpty')
            ->addValidator(new Db\NoRecordExists(array(
                'table' => 'datatypes'
                , 'field' => 'name'
                ))
            );

        $model  = new Element\Select('model');

        $path = APPLICATION_PATH.'/../library/Datatypes/';
        $list_dir = glob($path.'*', GLOB_ONLYDIR);
        foreach($list_dir as $dir)
        {
            $dir = str_replace($path, '', $dir);
            $model->addMultiOption($dir, $dir);
        }

        $model->setRequired(TRUE)
            ->setLabel('Identifier')
            ->addValidator('NotEmpty')
            ->addValidator(new Validator\Identifier());

        $submit = new Element\Submit('submit', array('order' => 999));
        $submit->setAttrib('class', 'input-submit')
            ->setLabel('Save');


        $this->addElements(array($name, $model, $submit));
    }
}
