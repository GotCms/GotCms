<?php

namespace Application\Model\Layout;

use Es\Db\AbstractTable,
    Es\Component\IterableInterface;

class Collection extends AbstractTable implements IterableInterface
{
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
            $layout[] = Model::fromArray($row->toArray());
        }

        $this->setData('layouts', $layout);
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
