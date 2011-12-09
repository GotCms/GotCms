<?php
abstract class Es_Model_DbTable_Datatype_Abstract_Editor extends Es_Core_Object
{
    /**
    * @var Es_Model_DbTable_Datatype_Abstract
    */
    protected $_datatype;

    /**
    * @var Es_Model_DbTable_Property_Model
    */
    protected $_property;

    /**
    * get name of datatype
    * @var string
    */
    protected $_name;

    abstract public function save();
    abstract public function load();

    public function __construct(Es_Model_DbTable_Datatype_Abstract $datatype_abstract)
    {
        $this->_datatype = $datatype_abstract;
        $this->_property = $datatype_abstract->getProperty();
        $this->_construct();
    }

    protected function getValue()
    {
        return $this->getProperty()->getValue();
    }

    protected function setValue($value)
    {
        $this->getProperty()->setValue($value);

        return $this;
    }

    protected function saveValue()
    {
        $value = $this->getValue();
        if($this->getProperty()->isRequired() and empty($value))
        {
            return FALSE;
        }

        return $this->getProperty()->saveValue();
    }

    protected function getConfiguration()
    {
        return $this->getDatatype()->getParameters();
    }

    protected function setConfiguration($value)
    {
        $this->getDatatype()->setParameters($value);

        return $this;
    }

    protected function getHelper($helper)
    {
        return $this->getDatatype()->getHelper($helper);
    }

    public function getUploadUrl()
    {
        return $this->getDatatype()->getUploadUrl().'/property/'.$this->getProperty()->getId();
    }

    public function getName()
    {
        return $this->getDatatype()->getName().$this->getProperty()->getId();
    }

    /**
    * @return Es_Model_DbTable_Property_Model
    */
    public function getProperty()
    {
        return $this->_property;
    }

    /**
    * @return Es_Model_DbTable_Property_Model
    */
    public function getDatatype()
    {
        return $this->_datatype;
    }

    /**
    * @return Zend_Controller_Request_Http
    */
    public function getRequest()
    {
        return Zend_Controller_Front::getInstance()->getRequest();
    }
}
