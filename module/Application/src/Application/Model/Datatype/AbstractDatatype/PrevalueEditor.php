<?php
namespace Application\Model\Datatype\AbstractDatatype;

use Es\Core\Object,
    Application\Model\Datatype;

abstract class PrevalueEditor extends Object
{
    protected $_datatype;
    protected $_config;

    public function __construct(Datatype\AbstractDatatype $datatype_abstract)
    {
        $this->_datatype = $datatype_abstract;
        parent::__construct();
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
