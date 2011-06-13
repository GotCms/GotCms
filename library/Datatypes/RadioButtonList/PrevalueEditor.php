<?php
class Datatypes_Textrich_PrevalueEditor extends Es_Model_DbTable_Datatype_Abstract_PrevalueEditor
{
	public function save($request = null)
	{
		//Save prevalue in column datatypes_prevalue_value
		$this->setConfiguration(array());

		return $this->getConfiguration();
	}

	public function load()
	{
		$configuration = $this->getConfiguration();

		return array();
	}
}