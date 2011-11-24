<?php
/**
 * @author Rambaud Pierre
 *
 */
class Es_Model_DbTable_DocumentType_Collection extends Es_Db_Table implements Es_Interface_Iterable
{
    protected $_name = 'document_types';

    public function init($sort = 'ASC')
    {
        $this->setDocumentTypes();
    }

    private function setDocumentTypes()
    {
        $select = $this->select();
        $select->order(array('document_type_name ASC'));
        $rows = $this->fetchAll();
        $documentTypes = array();
        foreach($rows as $row)
        {
            $documentTypes[] = Es_Model_DbTable_DocumentType_Model::fromArray($row->toArray());
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
    * Es_Interfaces_Iterable methods
    */
    /* (non-PHPdoc)
    * @see include/Es/Interface/Es_Interface_Iterable#getParent()
    */
    public function getParent()
    {
        return null;
    }
    /* (non-PHPdoc)
    * @see include/Es/Interface/Es_Interface_Iterable#getChildren()
    */
    public function getChildren()
    {
        return $this->getDocumentTypes();
    }
    /* (non-PHPdoc)
    * @see include/Es/Interface/Es_Interface_Iterable#getId()
    */
    public function getId()
    {
        return null;
    }
    /* TODO Finish icon in Es_DocumentType_Collection
    */
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
        return 'documenttypes';
    }
    /* (non-PHPdoc)
    * @see include/Es/Interface/Es_Interface_Iterable#getName()
    */
    public function getName()
    {
        return 'Document Types';
    }
    /* (non-PHPdoc)
    * @see include/Es/Interface/Es_Interface_Iterable#getUrl()
    */
    public function getUrl()
    {
        return 'javascript:loadController(\''.Zend_Controller_Action_HelperBroker::getStaticHelper('url')->url(array('controller'=>'development', 'action'=>'documenttypes')).'\')';
    }

}
