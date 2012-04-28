<?php
namespace Gc\Datatype;

use Gc\Db\AbstractTable,
    Gc\Component\IterableInterface;

class Collection extends AbstractTable implements IterableInterface
{
    protected $_datatypes;
    protected $_name = 'datatypes';

    public function init()
    {
        $this->setDatatypes();
    }

    private function setDatatypes()
    {
        $rows = $this->select();
        $datatypes = array();
        foreach($rows as $row)
        {
            $datatypes[] = Model::fromArray((array)$row);
        }

        $this->_datatypes = $datatypes;
    }

    public function getDatatypes()
    {
        return $this->_datatypes;
    }

    public function getSelect()
    {
        $arrayReturn = array();
        foreach($this->getDatatypes() as $key=>$value)
        {
            $arrayReturn[$value->getId()] = $value->getName();
        }

        return $arrayReturn;
    }

    /*
    * Gc\Component\IterableInterfaces methods
    */
    /* (non-PHPdoc)
    * @see include/Gc/Interface/Gc\Component\IterableInterface#getParent()
    */
    public function getParent()
    {
        return FALSE;
    }
    /* (non-PHPdoc)
    * @see include/Gc/Interface/Gc\Component\IterableInterface#getChildren()
    */
    public function getChildren()
    {
        return $this->getDatatypes();
    }
    /* (non-PHPdoc)
    * @see include/Gc/Interface/Gc\Component\IterableInterface#getId()
    */
    public function getId()
    {
        return FALSE;
    }


    /* (non-PHPdoc)
    * @see include/Gc/Interface/Gc\Component\IterableInterface#getIcon()
    */
    public function getIcon()
    {
        return 'folder';
    }

    /* (non-PHPdoc)
    * @see include/Gc/Interface/Gc\Component\IterableInterface#getIterableId()
    */
    public function getIterableId()
    {
        return 'datatypes';
    }

    /* (non-PHPdoc)
    * @see include/Gc/Interface/Gc\Component\IterableInterface#getName()
    */
    public function getName()
    {
        return 'Datatypes';
    }

    /* (non-PHPdoc)
    * @see include/Gc/Interface/Gc\Component\IterableInterface#getUrl()
    */
    public function getUrl()
    {
        return 'javascript:loadController(\''.Zend_Controller_Action_HelperBroker::getStaticHelper('url')->url(array('controller' => 'development', 'action'=>'datatypes')).'\')';
    }
}
