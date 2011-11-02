<?php
abstract class Es_Model_DbTable_Datatype_Abstract_PrevalueEditor extends Es_Core_Object {

    protected $_datatype;

    public function __construct(Es_Model_DbTable_Datatype_Abstract $datatype_abstract)
    {
        $this->setData('datatype_abstract', $datatype_abstract);
        $this->_construct();
    }

    abstract public function save();

    abstract public function load();

    protected function getConfig()
    {
        return $this->getDatatypeAbstract()->getConfig();
    }

    protected function setConfig($value)
    {
        $this->getDatatypeAbstract()->setConfig($value);
        return $this;
    }

    public function getParam($name)
    {
        return $this->getDatatypeAbstract()->getDatatype()->getData($name);
    }
}
