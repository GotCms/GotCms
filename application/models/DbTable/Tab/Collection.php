<?php
class Es_Component_Tab_Collection {
	private $_tabs;
	private $_document_type_id;
	private $_tabs_elements = array();

	public function __construct($document_type_id = -1) {
		$this->setDocumentTypeId($document_type_id);
		$this->setTabs();
	}
	private function setTabs() {
		$db = Zend_Registry::get('db');
		$db->setFetchMode(Zend_Db::FETCH_ASSOC);
		$select = $db->select();
		$select->from(array('t'=>'tabs'));
		$select->order(array('tab_name ASC'));
		if($this->_document_type_id != -1) {
			$select->where('t.document_type_id = ? ', $this->getDocumentTypeId());
		}
		$statement = $db->query($select);
		$rows = $statement->fetchAll();
		$tabs = array();
		foreach($rows as $value) {
			$tabs[] = Es_Component_Tab_Model::fromArray($value);
		}
		$this->_tabs = $tabs;
	}
	public function getTabs() {
		return $this->_tabs;
	}
	public function setDocumentTypeId($value) {
		$this->_document_type_id = (int)$value;
		return $this;
	}

	public function getDocumentTypeId() {
		return $this->_document_type_id;
	}
	public function cleanUp() {
		try {
			$db = Zend_Registry::get('db');
			$db->delete('tabs','document_type_id = '.$this->getDocumentTypeId());
		} catch (Exception $e) {
			Es_Error::set(get_class($this), $e);
		}
		return true;
	}

	public function addElement($tab) {
		$this->_tabs_elements[] = $tab;
		return $this;
	}

	public function getElements() {
		return $this->_tabs_elements;
	}

	public function cleanElements() {
		$this->_tabs_elements = array();
		return $this;
	}
}