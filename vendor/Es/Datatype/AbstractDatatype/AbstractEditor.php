<?php

namespace Es\Datatype\AbstractDatatype;

use Es\Core\Object,
    Es\Datatype,
    Zend\EventManager\StaticEventManager;

abstract class AbstractEditor extends Object
{
    /**
    * @var AbstractDatatype
    */
    protected $_datatype;

    /**
    * @var Es\Property\Model
    */
    protected $_property;

    /**
    * get name of datatype
    * @var string
    */
    protected $_name;

    abstract public function save();
    abstract public function load();

    public function __construct(Datatype\AbstractDatatype $datatype_abstract)
    {
        $this->_datatype = $datatype_abstract;
        $this->_property = $datatype_abstract->getProperty();
        parent::__construct();
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
    * @return Es\Property\Model
    */
    public function getProperty()
    {
        return $this->_property;
    }

    /**
    * @return Es\Property\Model
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
        return $GLOBALS['application']->getRequest();
    }
}
