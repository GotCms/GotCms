<?php
class Datatypes_Textstring_PrevalueEditor extends Es_Model_DbTable_Datatype_Abstract_PrevalueEditor
{
	public function save()
	{
		$length = $this->getParam('length');
		$this->setConfig(array('length' => $length));
	}

	public function load()
	{
		$config = $this->getConfig();

		$length = new Zend_Form_Element_Text('length');
		$length->setLabel('Length')->setValue(isset($config['length']) ? $config['length'] : '');

		return array($length);
	}
}