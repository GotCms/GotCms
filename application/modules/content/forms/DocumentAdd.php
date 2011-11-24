<?php
class Content_Form_DocumentAdd extends Es_Form
{
    public function init()
    {
        $this->setMethod(self::METHOD_POST);
        $this->setElementsBelongTo('document');

        $name = new Zend_Form_Element_Text('name');
        $name->setRequired(TRUE)
            ->setLabel('Name')
            ->setAttrib('class', 'input-text')
            ->addValidator(new Zend_Validate_NotEmpty());

        $url_key  = new Zend_Form_Element_Text('url_key');
        $url_key->setRequired(TRUE)
            ->setLabel('Url key')
            ->setAttrib('class', 'input-text')
            ->addValidator(new Zend_Validate_NotEmpty());

        $document_collection = new Es_Model_DbTable_Document_Collection();
        $parent_id = new Zend_Form_Element_Select('parent_id');
        $parent_id->addMultiOption('', 'Select parent');
        $parent_id->addMultiOptions($document_collection->getSelect());

        $document_type_collection = new Es_Model_DbTable_DocumentType_Collection();
        $document_type = new Zend_Form_Element_Select('document_type');
        $document_type->addMultiOption('', 'Select document type');
        $document_type->addMultiOptions($document_type_collection->getSelect());

        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setAttrib('class', 'input-submit')
            ->setLabel('Create');

        $this->addElements(array($name, $url_key, $parent_id, $document_type, $submit));
    }
}
