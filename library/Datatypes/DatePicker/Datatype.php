<?php 
class Datatypes_Textrich_Datatype extends Es_Datatype_Abstract {

	public function getEditor() {
		if($this->_editor === null) {
			$this->_editor = new Datatypes_Textrich_Editor($this);
		}
		return $this->_editor;
	}

	public function getPrevalueEditor() {
		if($this->_prevalueEditor === null) {
			$this->_prevalueEditor = new Datatypes_Textrich_PrevalueEditor($this);
		}
		return $this->_prevalueEditor;
	}
}

