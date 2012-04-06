<?php
namespace Es\View;

use Es\Db\AbstractTable,
    Es\Component\IterableInterface;

class Collection extends AbstractTable implements IterableInterface
{
    protected $_views;
    protected $_views_elements;
    protected $_name = 'views';

    public function init($document_type_id = NULL)
    {
        $this->setDocumentTypeId($document_type_id);
        $this->setViews();
    }

    private function setViews()
    {
        $select = $this->getSqlSelectPrototype()->order(array('name'));

        if($this->getDocumentTypeId() !== NULL)
        {
            $select->join(array('dtv'=>'document_types_views'),'dtv.view_id = v.view_id');
            $select->where('dtv.document_type_id = ?', $this->getDocumentTypeId());
        }

        $rows = $this->fetchAll($select);
        $views = array();
        foreach($rows as $row)
        {
            $views[] = Model::fromArray((array)$row);
        }

        $this->_views = $views;
        return $this;
    }

    public function getViews()
    {
        return $this->_views;
    }

    public function getSelect()
    {
        $array_views = array();
        foreach($this->_views as $key => $value)
        {
            $array_views[$value->getId()] = $value->getName();
        }

        return $array_views;
    }

    public function addElement(Model $view)
    {
        $this->_views_elements[] = $view;
        return $this;
    }

    public function clearElements()
    {
        $this->_views_elements = array();
        return $this;
    }

    public function getElements()
    {
        return $this->_views_elements;
    }

    public function save()
    {
        if(!empty($this->_data['document_type_id']))
        {
            $this->delete();
            foreach($this->getElements() as $view)
            {
                $this->getSqlInsert()->into('document_type_views')->values(array('document_type_id' => $this->getDocumentTypeId(), 'view_id' => $view->getId()));
            }

            return TRUE;
        }

        return FALSE;
    }

    public function delete()
    {
        if(!empty($this->_data['document_type_id']))
        {
            $this->getApdater()->delete('document_type_views', 'document_type_id = '.$this->getDocumentTypeId());
            return TRUE;
        }

        return FALSE;
    }

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
        return "views";
    }

    public function getName()
    {
        return "Views";
    }

    public function getUrl()
    {
        return '';
    }

    public function getIcon()
    {
        return 'folder';
    }
}
