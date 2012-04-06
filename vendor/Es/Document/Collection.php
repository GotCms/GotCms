<?php

namespace Application\Model\Document;

use Es\Db\AbstractTable,
    Es\Component\IterableInterface;

class Collection extends AbstractTable implements IterableInterface
{
    protected $_name = 'documents';

    public function load($parent_id = NULL)
    {
        if($parent_id !== NULL)
        {
            $this->setData('parent_id', $parent_id);
            $this->setDocuments();
        }
    }

    private function setDocuments()
    {
        $parent_id = $this->getParentId();

        if(!empty($parent_id))
        {
            $rows = $this->select(array('parent_id = ? ' => $this->getParentId()));
        }
        else
        {
            $rows = $this->select('parent_id IS NULL');
        }

        $documents = array();
        foreach($rows as $row)
        {
            $documents[] = Model::fromArray((array)$row);
        }

        $this->setData('documents', $documents);
    }

    public function getSelect()
    {
        $documents = $this->getDocuments();
        if(!is_array($documents))
        {
            $documents = array();
        }

        $array = array();
        foreach($documents as $document)
        {
            $array[$document->getId()] = $document->getName();
        }

        return $array;
    }

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
        return $this->getDocuments();
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
        return 'documents';
    }

    /* (non-PHPdoc)
    * @see include/Es/Interface/Es_Interface_Iterable#getName()
    */
    public function getName()
    {
        return 'Website';
    }

    /* (non-PHPdoc)
    * @see include/Es/Interface/Es_Interface_Iterable#getUrl()
    */
    public function getUrl()
    {
        return NULL;
    }

}
