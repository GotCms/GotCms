<?php
abstract class Es_Model_DbTable_Datatype_Abstract_PrevalueEditor extends Es_Core_Object
{
    protected $_datatype;
    protected $_config;

    public function __construct(Es_Model_DbTable_Datatype_Abstract $datatype_abstract)
    {
        $this->_datatype = $datatype_abstract;
        $this->_construct();
    }

    abstract public function save();

    abstract public function load();

    protected function getConfig()
    {
        if(empty($this->_config))
        {
            $this->_config = unserialize($this->getDatatype()->getConfig());
        }

        return $this->_config;
    }

    protected function setConfig($value)
    {
        $this->getDatatype()->setConfig($value);
        return $this;
    }

    /**
    * @return Zend_Controller_Request_Http
    */
    public function getRequest()
    {
        return Zend_Controller_Front::getInstance()->getRequest();
    }

    /**
    * @return Es_Model_DbTable_Datatype_Abstract
    */
    public function getDatatype()
    {
        return $this->_datatype;
    }
}
