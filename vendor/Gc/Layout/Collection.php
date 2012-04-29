<?php

namespace Gc\Layout;

use Gc\Db\AbstractTable,
    Gc\Component\IterableInterface;

class Collection extends AbstractTable implements IterableInterface
{
    protected $_name = 'layout';

    public function init()
    {
        $this->setLayouts();
    }

    private function setLayouts()
    {
        $rows = $this->select();
        //$select->order(array('name ASC'));
        $layout = array();
        foreach($rows as $row)
        {
            $layout[] = Model::fromArray((array)$row);
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
    * Gc\Component\IterableInterfaces methods
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
