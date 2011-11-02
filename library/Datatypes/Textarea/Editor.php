<?php
class Datatypes_Textarea_Editor extends Es_Datatype_Abstract_Editor {

    public function save() {
        //sauvegarde des informations
    }

    public function load() {
        $textarea = new Zend_Form_Element_Textarea('textarea-'.$this->_property->getId());
        $textarea->setLabel($this->_property->getName());
        $textarea->setAttrib('id', 'textarea-'.$this->_property->getId());
        $textarea->setValue($this->_property->getValue());

        return $textarea;
    }
}

