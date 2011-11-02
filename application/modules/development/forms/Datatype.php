<?php
class Development_Form_Datatype extends Es_Form
{
    public function init()
    {
        $this->setMethod(self::METHOD_POST);
        $this->setElementsBelongTo('datatype');

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

        $model_id  = new Zend_Form_Element_Select('model_id');
        $model_id->setRequired(TRUE)
            ->setLabel('Model')
            ->addValidator(new Zend_Validate_NotEmpty());
        $model_collection = new Es_Model_DbTable_Model_Collection();
        $model_id->addMultiOptions($model_collection->getModelsSelect());

        $submit = new Zend_Form_Element_Submit('submit', array('order' => 999));
        $submit->setAttrib('class', 'input-submit')
            ->setLabel('Add');


        $this->addElements(array($name, $model_id, $submit));
    }
}
