<?php
class Datatypes_Textarea_Datatype extends Es_Model_DbTable_Datatype_Abstract {

    public function getEditor(Es_Component_Property_Model $property)
    {
        $this->_property = $property;
        if($this->_editor === null)
        {
            $this->_editor = new Datatypes_Textarea_Editor($this);
        }

        return $this->_editor;
    }

    public function getPrevalueEditor()
    {
        if($this->_prevalueEditor === null)
        {
            $this->_prevalueEditor = new Datatypes_Textarea_PrevalueEditor($this);
        }

        return $this->_prevalueEditor;
    }
}

