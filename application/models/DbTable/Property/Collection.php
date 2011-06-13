<?php
class Es_Component_Property_Collection {
	private $_properties;
	private $_tab_id;
	private $_document_type_id;
	private $_properties_elements;
	private $_document_id;

	public function __construct($document_type_id = -1, $tab_id = -1, $document_id = null) {
		$this->setDocumentTypeId($document_type_id);
		$this->setTabId($tab_id);
		if($document_id !== null) {
			$this->_document_id = $document_id;
		}
		$this->setProperties();
	}
	private function setProperties() {
		$db = Zend_Registry::get('db');
		$db->setFetchMode(Zend_Db::FETCH_ASSOC);
		$select = $db->select();
		$select->from(array('t'=>'tabs'), array());
		$select->join(array('p'=>'properties'),
				't.tab_id = p.tab_id');
		if($this->_document_id !== null) {
			$select->join(array('d'=>'documents'),
					'd.document_type_id = t.document_type_id', array('d.document_id'));
			$select->joinLeft(array('pv'=>'properties_value'),
				'd.document_id = pv.document_id AND p.property_id = pv.property_id', array('pv.value', 'pv.property_value_id'));
			$select->where('d.document_id = ?', $this->_document_id);
		}
		if($this->getTabId() != -1){
			$select->where('t.tab_id = ?', array($this->getTabId()));
		}
		$select->where('t.document_type_id = ? ',$this->getDocumentTypeId());
		$select->order('p.property_order ASC');
		$statement = $db->query($select);
		$rows = $statement->fetchAll();
		$properties = array();
		foreach($rows as $value) {
			$properties[] = Es_Component_Property_Model::fromArray($value);
		}
		$this->_properties = $properties;
	}

	/**
	 * @return Es_Component_Property_Model
	 */
	public function getProperties() {
		return $this->_properties;
	}

	public function setTabId($value) {
		$this->_tab_id = (int)$value;
	}

	public function getTabId() {
		return $this->_tab_id;
	}

	public function setDocumentTypeId($document_type_id) {
		$this->_document_type_id = (int) $document_type_id;
	}

	public function getDocumentTypeId() {
		return $this->_document_type_id;
	}

	public function cleanUp() {
		try {
			foreach($this->_properties as $value) {
				$value->delete();
			}
		} catch (Exception $e) {
			Es_Error::set(get_class($this), $e);
		}
		return true;
	}

	public function addElement($property) {
		$this->_properties_elements[] = $property;
		return $this;
	}

	public function getElements() {
		return $this->_properties_elements;
	}

	public function cleanElements() {
		$this->_properties_elements = array();
		return $this;
	}
}