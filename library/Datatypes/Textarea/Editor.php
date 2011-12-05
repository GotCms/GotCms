<?php
class Datatypes_Textarea_Editor extends Es_Model_DbTable_Datatype_Abstract_Editor
{
    public function save()
    {
        $value = $request->getParam($this->getName());
        $this->setValue($value);
        return $this->saveValue();
    }

    public function load()
    {
        $textarea = new Zend_Form_Element_Textarea($this->getName());
        $textarea->setLabel($this->getProperty()->getName());
        $textarea->setValue($this->getProperty()->getValue());

        return $textarea;
    }
}

