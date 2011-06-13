<?php
//@TODO
class Es_Media_Icon_Collection implements Es_Interface_Iterable{
	private $_icons;

	public function __construct() {
		$this->setIcons();
	}
	private function setIcons() {
		$db = Zend_Registry::get('db');
		$db->setFetchMode(Zend_Db::FETCH_ASSOC);
		$select = $db->select();
		$select->from(array('i'=>'icons'));
		$select->order(array('icon_name ASC'));
		$statement = $db->query($select);
		$rows = $statement->fetchAll();
		$icons = array();
		foreach($rows as $row) {
			$icons[] = Es_Media_Icon_Model::fromArray($row);
		}
		$this->_icons = $icons;
	}
	public function getIcons() {
		return $this->_icons;
	}
	public function getIconsSelect() {
		$arrayReturn = array();
		foreach($this->_icons as $key=>$value){
			$arrayReturn[$value->getId()] = $value->getName();
		}
		return $arrayReturn;
	}
	public function getIconByName($name) {
		foreach($this->_icons as $icon) {
			if(in_array($name,$icon)) {
				return $icon;
			}
		}
		return false;
	}

	public function getIconById($id) {
		foreach($this->_icons as $icon) {
			if(in_array($id,$icon)) {
				return $icon;
			}
		}
		return false;
	}

	static function fromId($icon_id = null) {
		if($icon_id === null) {
			$db = Zend_Registry::get('db');
			$db->setFetchMode(Zend_Db::FETCH_ASSOC);
			$select = $db->select();
			$select->from(array('i'=>'icons'));
			$select->order(array('icon_name ASC'));
			$select->where('icon_id = ?', $icon_id);
			$statement = $db->query($select);
			return $statement->fetchAll();
		}
		return false;
	}


	public function __toString() {
		return $this->_icons;
	}


/*
	 * Es_Interfaces_Iterable methods
	 */
	public function getParent() {
		return null;
	}
	public function getChildren() {
		return $this->getIcons();
	}
	public function getId() {
		return null;
	}
	public function getIterableId() {
		return "icons";
	}
	public function getName() {
		return "Icons";
	}
	public function getUrl() {
		return 'javascript:loadController(\''.Zend_Controller_Action_HelperBroker::getStaticHelper('url')->url(array('controller'=>'media', 'action'=>'icons')).'\')';
	}
	public function getIcon() {
		return 'folder';
	}
}