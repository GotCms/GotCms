<?php
class Development_Form_Datatype extends Es_Form
{
    public function init()
    {
        $this->setMethod(self::METHOD_POST);

        $name = new Zend_Form_Element_Text('name');
        $name->setRequired(TRUE)
            ->setLabel('Name')
            ->setAttrib('class', 'input-text')
            ->addValidator(new Zend_Validate_NotEmpty())
            ->addValidator(new Zend_Validate_Db_NoRecordExists(array(
                'table' => 'datatypes'
                , 'field' => 'name'
                ))
            );

        $model  = new Zend_Form_Element_Select('model');

        $path = APPLICATION_PATH.'/../library/Datatypes/';
        $list_dir = glob($path.'*', GLOB_ONLYDIR);
        foreach($list_dir as $dir)
        {
            $dir = str_replace($path, '', $dir);
            $model->addMultiOption($dir, $dir);
        }

        $model->setRequired(TRUE)
            ->setLabel('Identifier')
            ->addValidator(new Zend_Validate_NotEmpty())
            ->addValidator(new Es_Validate_Identifier());

        $submit = new Zend_Form_Element_Submit('submit', array('order' => 999));
        $submit->setAttrib('class', 'input-submit')
            ->setLabel('Save');


        $this->addElements(array($name, $model, $submit));
    }
}
