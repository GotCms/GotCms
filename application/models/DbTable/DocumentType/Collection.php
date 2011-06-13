<?php
/**
 * @author Rambaud Pierre
 *
 */
class Es_DocumentType_Collection implements Es_Interface_Iterable {
	
	private $_documentTypes;
	private $_sort;
	
	public function __construct($sort = 'ASC') {
		if($sort!='DESC')$sort = 'ASC';
		$this->_sort = $sort;
		$this->setDocumentTypes();
	}
	private function setDocumentTypes(){
		$db = Zend_Registry::get('db');
		$db->setFetchMode(Zend_Db::FETCH_ASSOC);
		$select = $db->select();
		$select->from(array('dt'=>'document_types'));
		$select->order(array('document_type_name '.$this->_sort));
		$statement = $db->query($select);
		$rows = $statement->fetchAll();
		$documentTypes = array();
		foreach($rows as $row){
			$documentTypes[] = Es_DocumentType_Model::fromArray($row);
		}
		$this->_documentTypes = $documentTypes;
	}
	public function getDocumentTypes(){
		return $this->_documentTypes;
	}
	
	/*
	 * Es_Interfaces_Iterable methods
	 */	
	/* (non-PHPdoc)
	 * @see include/Es/Interface/Es_Interface_Iterable#getParent()
	 */
	public function getParent() {
		return null;
	}
	/* (non-PHPdoc)
	 * @see include/Es/Interface/Es_Interface_Iterable#getChildren()
	 */
	public function getChildren() {
		return $this->getDocumentTypes();
	}
	/* (non-PHPdoc)
	 * @see include/Es/Interface/Es_Interface_Iterable#getId()
	 */
	public function getId() {
		return null;
	}
	/* TODO Finish icon in Es_DocumentType_Collection
	 */
	/* (non-PHPdoc)
	 * @see include/Es/Interface/Es_Interface_Iterable#getIcon()
	 */
	public function getIcon() {
		return 'folder';
	}
	/* (non-PHPdoc)
	 * @see include/Es/Interface/Es_Interface_Iterable#getIterableId()
	 */
	public function getIterableId() {
		return 'documenttypes';
	}
	/* (non-PHPdoc)
	 * @see include/Es/Interface/Es_Interface_Iterable#getName()
	 */
	public function getName() {
		return 'Document Types';
	}
	/* (non-PHPdoc)
	 * @see include/Es/Interface/Es_Interface_Iterable#getUrl()
	 */
	public function getUrl() {
		return 'javascript:loadController(\''.Zend_Controller_Action_HelperBroker::getStaticHelper('url')->url(array('controller'=>'development', 'action'=>'documenttypes')).'\')';
	}

}