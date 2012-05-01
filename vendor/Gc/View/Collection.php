<?php
namespace Gc\View;

use Gc\Db\AbstractTable,
    Gc\Component\IterableInterface,
    Zend\Db\Sql\Select;

class Collection extends AbstractTable implements IterableInterface
{
    protected $_views_elements;
    protected $_name = 'view';

    public function init($document_type_id = NULL)
    {
        $this->setDocumentTypeId($document_type_id);
        $this->getViews(TRUE);
    }

    private function getViews($force_reload = FALSE)
    {
        if($force_reload)
        {
            $select = new Select();
            $select->order(array('name'));
            $select->from('view');

            if($this->getDocumentTypeId() !== NULL)
            {
                $select->join('document_type_view', 'document_type_view.view_id = view.id');
                $select->where(sprintf('document_type_view.document_type_id = %s', $this->getDocumentTypeId()));
            }

            $rows = $this->fetchAll($select);
            $views = array();
            foreach($rows as $row)
            {
                $views[] = Model::fromArray((array)$row);
            }

            $this->setData('views', $views);
        }

        return $this->getData('views');
    }

    public function getSelect()
    {
        $array_views = array();
        foreach($this->getViews() as $key => $value)
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
