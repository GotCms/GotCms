<?php
class Datatypes_Textstring_Editor extends Es_Model_DbTable_Datatype_Abstract_Editor
{
	public function save(Zend_Controller_Request_Abstract $request = NULL)
	{
		$value = $request->getParam('textstring'.$this->_property->getId());
		$this->setValue($value);
		return $this->saveValue();
	}

	public function load()
	{
		$parameters = $this->getParameters();
		$property = $this->_property;
		$textstring = new Zend_Form_Element_Text('textstring'.$property->getId());
		$textstring->setLabel($property->getName());
		$textstring->setValue($this->getValue());
		if(!empty($parameters['length']))
		{
			$textstring->setAttrib('maxlength', $parameters['length']);
		}

		return $textstring;
	}
}

