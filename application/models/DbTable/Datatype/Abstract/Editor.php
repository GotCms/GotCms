<?php
abstract class Es_Model_DbTable_Datatype_Abstract_Editor extends Es_Core_Object
{
	protected $_datatype, $_property;

	public function __construct(Es_Model_DbTable_Datatype_Abstract $datatype)
	{
		$this->_datatype = $datatype;
		$this->_property = $this->_datatype->_property;
		$this->_construct();
	}

	abstract public function save(Zend_Controller_Request_Abstract $request = NULL);
	abstract public function load();

	protected function getValue()
	{
		return $this->_property->getValue();
	}

	protected function setValue($value)
	{
		$this->_property->setValue($value);
		return $this;
	}

	protected function saveValue()
	{
		$value = $this->getValue();
		if($this->_property->getRequired() && empty($value))
		{
			return false;
		}

		return $this->_property->saveValue();
	}

	protected function getParameters()
	{
		return $this->_datatype->getParameters();
	}

	protected function setParameters($value)
	{
		$this->_datatype->setParameters($value);
		return $this;
	}

	protected function getHelper($helper)
	{
		return $this->_datatype->getHelper($helper);
	}

	public function getUploadUrl()
	{
		return $this->_datatype->getUploadUrl().'/property/'.$this->_property->getId();
	}

}