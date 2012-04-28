<?php

namespace Gc\Document;

use Gc\Db\AbstractTable,
    Gc\Component\IterableInterface;

class Collection extends AbstractTable implements IterableInterface
{
    protected $_name = 'document';

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
        return $this->getDocuments();
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
        return 'documents';
    }

    /* (non-PHPdoc)
    * @see include/Gc/Interface/Gc\Component\IterableInterface#getName()
    */
    public function getName()
    {
        return 'Website';
    }

    /* (non-PHPdoc)
    * @see include/Gc/Interface/Gc\Component\IterableInterface#getUrl()
    */
    public function getUrl()
    {
        return NULL;
    }

}
