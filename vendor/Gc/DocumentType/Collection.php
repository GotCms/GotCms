<?php

namespace Gc\DocumentType;

use Gc\Db\AbstractTable,
    Gc\Component\IterableInterface;

class Collection extends AbstractTable implements IterableInterface
{
    protected $_name = 'document_type';

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
    * Gc\Component\IterableInterfaces methods
    */
    /* (non-PHPdoc)
    * @see include/Gc/Interface/Gc\Component\IterableInterface#getParent()
    */
    public function getParent()
    {
        return null;
    }
    /* (non-PHPdoc)
    * @see include/Gc/Interface/Gc\Component\IterableInterface#getChildren()
    */
    public function getChildren()
    {
        return $this->getDocumentTypes();
    }
    /* (non-PHPdoc)
    * @see include/Gc/Interface/Gc\Component\IterableInterface#getId()
    */
    public function getId()
    {
        return null;
    }
    /* TODO Finish icon in Gc\DocumentType\Collection
    */
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
        return 'documenttypes';
    }
    /* (non-PHPdoc)
    * @see include/Gc/Interface/Gc\Component\IterableInterface#getName()
    */
    public function getName()
    {
        return 'Document Types';
    }
    /* (non-PHPdoc)
    * @see include/Gc/Interface/Gc\Component\IterableInterface#getUrl()
    */
    public function getUrl()
    {
        return 'javascript:loadController(\''.Zend_Controller_Action_HelperBroker::getStaticHelper('url')->url(array('controller'=>'development', 'action'=>'documenttypes')).'\')';
    }

}
