<?php
class Datatypes_Textstring_Editor extends Es_Model_DbTable_Datatype_Abstract_Editor
{
    public function save()
    {
        $value = $this->getRequest()->getPost($this->getName());
        $this->setValue($value);
    }

    public function load()
    {
        $parameters = $this->getParameters();
        $property = $this->getProperty();
        $textstring = new Zend_Form_Element_Text($this->getName());
        $textstring->setLabel($property->getName());
        $textstring->setValue($this->getValue());
        if(!empty($parameters['length']))
        {
            $textstring->setAttrib('maxlength', $parameters['length']);
        }

        return $textstring;
    }
}

