<?php
class Datatypes_Textstring_Editor extends Es_Model_DbTable_Datatype_Abstract_Editor
{
    protected $_name = 'textstring';
    public function save()
    {
        $value = $request->getParam($this->getName());
        $this->setValue($value);
        return $this->saveValue();
    }

    public function load()
    {
        $parameters = $this->getParameters();
        $property = $this->_property;
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

