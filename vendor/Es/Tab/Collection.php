<?php

namespace Es\Tab;

use Es\Db\AbstractTable;

class Collection extends AbstractTable
{
    protected $_name = 'tabs';

    public function load($document_type_id = NULL)
    {
        $this->setDocumentTypeId($document_type_id);

        return $this;
    }

    public function getTabs($force_reload = FALSE)
    {
        $tabs = $this->getData('tabs');
        $document_type_id = $this->getDocumentTypeId();
        if(empty($tabs) or $force_reload == TRUE)
        {
            if(empty($document_type_id))
            {
                $rows = $this->select('document_type_id = ?', $document_type_id);
            }
            else
            {
                $rows = $this->select();
            }

            $tabs = array();
            foreach($rows as $row)
            {
                $tabs[] = Model::fromArray((array)$row);
            }

            $this->setData('tabs', $tabs);
        }

        return $this->getData('tabs');
    }

    public function setTabs(Array $tabs)
    {
        $array = array();
        foreach($tabs as $tab)
        {
            $array[] = Model::fromArray($tab);
        }

        $this->setData('tabs', $array);
    }

    public function addTab(Array $tab)
    {
        $tabs = $this->getTabs();
        $tabs[] = Model::fromArray($tab);

        $this->setData('tabs', $tabs);
    }

    public function save()
    {
        $tabs = $this->getTabs();
        foreach($tabs as $tab)
        {
            $tab->save();
        }
    }

    public function delete()
    {
        $tabs = $this->getTabs();
        foreach($tabs as $tab)
        {
            $tab->delete();
        }

        return TRUE;
    }
}
