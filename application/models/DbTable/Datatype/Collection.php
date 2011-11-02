<?php
class Es_Model_DbTable_Datatype_Collection extends Es_Db_Table implements Es_Interface_Iterable
{

    protected $_datatypes;
    protected $_name = 'datatypes';

    public function init()
    {
        $this->setDatatypes();
    }

    private function setDatatypes()
    {
        $select = $this->select()
            ->order('name');
        $rows = $this->fetchAll($select);
        $datatypes = array();
        foreach($rows as $row)
        {
            $datatypes[] = Es_Model_DbTable_Datatype_Model::fromArray($row->toArray());
        }

        $this->_datatypes = $datatypes;
    }

    public function getDatatypes()
    {
        return $this->_datatypes;
    }

    public function getDatatypesSelect()
    {
        $arrayReturn = array();
        foreach($this->getDatatypes() as $key=>$value)
        {
            $arrayReturn[$value->getId()] = $value->getName();
        }

        return $arrayReturn;
    }

    /*
    * Es_Interfaces_Iterable methods
    */
    /* (non-PHPdoc)
    * @see include/Es/Interface/Es_Interface_Iterable#getParent()
    */
    public function getParent()
    {
        return FALSE;
    }
    /* (non-PHPdoc)
    * @see include/Es/Interface/Es_Interface_Iterable#getChildren()
    */
    public function getChildren()
    {
        return $this->getDatatypes();
    }
    /* (non-PHPdoc)
    * @see include/Es/Interface/Es_Interface_Iterable#getId()
    */
    public function getId()
    {
        return FALSE;
    }


    /* (non-PHPdoc)
    * @see include/Es/Interface/Es_Interface_Iterable#getIcon()
    */
    public function getIcon()
    {
        return 'folder';
    }

    /* (non-PHPdoc)
    * @see include/Es/Interface/Es_Interface_Iterable#getIterableId()
    */
    public function getIterableId()
    {
        return 'datatypes';
    }

    /* (non-PHPdoc)
    * @see include/Es/Interface/Es_Interface_Iterable#getName()
    */
    public function getName()
    {
        return 'Datatypes';
    }

    /* (non-PHPdoc)
    * @see include/Es/Interface/Es_Interface_Iterable#getUrl()
    */
    public function getUrl()
    {
        return 'javascript:loadController(\''.Zend_Controller_Action_HelperBroker::getStaticHelper('url')->url(array('controller' => 'development', 'action'=>'datatypes')).'\')';
    }
}
