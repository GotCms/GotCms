<?php
class Es_Model_DbTable_Layout_Collection extends Es_Db_Table implements Es_Interface_Iterable {

    protected $_layouts;
    protected $_name = 'layouts';

    public function init()
    {
        $this->setLayouts();
    }

    private function setLayouts()
    {
        $select = $this->select();
        $select->order(array('name ASC'));
        $rows = $this->fetchAll($select);

        $layout = array();
        foreach($rows as $row)
        {
            $layout[] = Es_Model_DbTable_Layout_Model::fromArray($row->toArray());
        }

        $this->_layouts = $layout;
    }

    public function getLayouts()
    {
        return $this->_layouts;
    }

    public function getLayoutsSelect()
    {
        $arrayReturn = array();
        foreach($this->_layouts as $key=>$value)
        {
            $arrayReturn[$value->getId()] = $value->getName();
        }

        return $arrayReturn;
    }

    /*
    * Es_Interfaces_Iterable methods
    */
    public function getParent()
    {
        return FALSE;
    }

    public function getChildren()
    {
        return $this->getViews();
    }

    public function getId()
    {
        return FALSE;
    }

    public function getIterableId()
    {
        return "layouts";
    }

    public function getName()
    {
        return "Layouts";
    }

    public function getUrl()
    {
        return 'javascript:loadController(\''.Zend_Controller_Action_HelperBroker::getStaticHelper('url')->url(array('controller'=>'development', 'action'=>'layouts')).'\')';
    }

    public function getIcon()
    {
        return 'folder';
    }
}
