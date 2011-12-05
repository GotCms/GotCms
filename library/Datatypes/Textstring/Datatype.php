<?php
class Datatypes_Textstring_Datatype extends Es_Model_DbTable_Datatype_Abstract
{
    protected $_name = 'textstring';

    public function getEditor(Es_Model_DbTable_Property_Model $property)
    {
        $this->_property = $property;
        if($this->_editor === null)
        {
            $this->_editor = new Datatypes_Textstring_Editor($this);
        }

        return $this->_editor;
    }

    public function getPrevalueEditor()
    {
        if($this->_prevalueEditor === null)
        {
            $this->_prevalueEditor = new Datatypes_Textstring_PrevalueEditor($this);
        }

        return $this->_prevalueEditor;
    }
}

