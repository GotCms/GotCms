<?php

namespace Es\DocumentType;

use Es\Db\AbstractTable,
    Es\Component\IterableInterface;

class Collection extends AbstractTable implements IterableInterface
{
    protected $_name = 'document_types';

    public function init($sort = 'ASC')
    {
        $this->setDocumentTypes();
    }

    private function setDocumentTypes()
    {
        $rows = $this->select();
        $documentTypes = array();
        foreach($rows as $row)
        {
            $documentTypes[] = Model::fromArray((array)$row);
        }

        $this->setData('document_types', $documentTypes);
    }

    public function getSelect()
    {
        $documents = $this->getDocumentTypes();

        $array = array();
        foreach($documents as $document)
        {
            $array[$document->getId()] = $document->getName();
        }

        return $array;
    }
    /*
    * Gc_Interfaces_Iterable methods
    */
    /* (non-PHPdoc)
    * @see include/Es/Interface/Gc_Interface_Iterable#getParent()
    */
    public function getParent()
    {
        return null;
    }
    /* (non-PHPdoc)
    * @see include/Es/Interface/Gc_Interface_Iterable#getChildren()
    */
    public function getChildren()
    {
        return $this->getDocumentTypes();
    }
    /* (non-PHPdoc)
    * @see include/Es/Interface/Gc_Interface_Iterable#getId()
    */
    public function getId()
    {
        return null;
    }
    /* TODO Finish icon in Gc_DocumentType_Collection
    */
    /* (non-PHPdoc)
    * @see include/Es/Interface/Gc_Interface_Iterable#getIcon()
    */
    public function getIcon()
    {
        return 'folder';
    }
    /* (non-PHPdoc)
    * @see include/Es/Interface/Gc_Interface_Iterable#getIterableId()
    */
    public function getIterableId()
    {
        return 'documenttypes';
    }
    /* (non-PHPdoc)
    * @see include/Es/Interface/Gc_Interface_Iterable#getName()
    */
    public function getName()
    {
        return 'Document Types';
    }
    /* (non-PHPdoc)
    * @see include/Es/Interface/Gc_Interface_Iterable#getUrl()
    */
    public function getUrl()
    {
        return 'javascript:loadController(\''.Zend_Controller_Action_HelperBroker::getStaticHelper('url')->url(array('controller'=>'development', 'action'=>'documenttypes')).'\')';
    }

}
