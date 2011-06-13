<?php
/**
 * @author Rambaud Pierre
 *
 */
class Es_Component_Property_Model  {

	private $_property_id, $_property_name, $_property_alias, $_property_description, $_property_required, $_property_order, $_tab_id, $_datatype_id;
	private $_property_value;
	/**
	 * @param integer $defaultId
	 */
	public function __construct($defaultId = -1, $tab_id = -1) {
		$this->setId($defaultId);
		$this->setTabId($tab_id);
	}

	/**
	 * @param integer $value
	 */
	private function setId($value) {
		$this->_property_id = (int)$value;
		return $this;
	}

	/**
	 * @param string $value
	 */
	public function setName($value) {
		$this->_property_name = $value;
		return $this;
	}

	/**
	 * @param string $value
	 */
	public function setAlias($value) {
		$this->_property_alias = $value;
		return $this;
	}

	/**
	 * @param boolean $value
	 */
	public function setRequired($value) {
		$value = (bool)$value;
		if($value !== true) {
			$value = false;
		}
		$this->_property_required = (boolean)$value;
		return $this;
	}

	/**
	 * @param string $value
	 */
	public function setDescription($value) {
		$this->_property_description = $value;
		return $this;
	}

	/**
	 * @param integer $value
	 */
	public function setOrder($value) {
		$this->_property_order = (int)$value;
		return $this;
	}

	/**
	 * @param string $value
	 */
	public function setTabId($value) {
		$this->_tab_id = $value;
		return $this;
	}

	public function setDatatypeId($value) {
		$this->_datatype_id = (int)$value;
		return $this;
	}

	public function getDatatypeId() {
		return $this->_datatype_id;
	}

	/**
	 * @return string|string
	 */
	public function getName() {
		return $this->_property_name;
	}

	/**
	 * @return string|string
	 */
	public function getId() {
		return $this->_property_id;
	}

	/**
	 * @return string|string
	 */
	public function getDescription() {
		return $this->_property_description;
	}

	/**
	 * @return string|string
	 */
	public function getAlias() {
		return $this->_property_alias;
	}

	/**
	 * @return string|string
	 */
	public function getRequired() {
		return $this->_property_required;
	}

	/**
	 * @return integer
	 */
	public function getOrder() {
		return $this->_property_order;
	}

	/**
	 * @return string|string
	 */
	public function getTabId() {
		return $this->_tab_id;
	}

	/**
	 * @param mixte $value
	 * @return $this
	 */
	public function setValue($value) {
		$this->_property_value->setValue($value);
		return $this;
	}

	/**
	 * @return mixte
	 */
	public function getValue() {
		return $this->_property_value->getValue();
		return false;
	}

	public function saveValue() {
		return $this->_property_value->save();
		return false;
	}

	/**
	 * @param unknown_type $value
	 * @return Es_Component_Property_Model
	 */
	public function setPropertyValue($value) {
		$this->_property_value = $value;
		return $this;
	}

	/**
	 * @param unknown_type $value
	 * @return Es_Component_Property_Value_Model
	 */
	public function getPropertyValue($value = null) {
		return $this->_property_value;
	}
	/**
	 * @return boolean
	 */
	public function save() {
		$db = Zend_Registry::get('db');
		$arraySave = array('property_name'=>$this->getName(),
							'property_description'=>$this->getDescription(),
							'property_alias'=>$this->getAlias(),
							'property_required'=>$this->getRequired()===true?'TRUE':'FALSE',
							'property_order'=>$this->getOrder(),
							'tab_id'=>$this->getTabId(),
							'datatype_id'=>$this->getDatatypeId()
							);

		try {
			if($this->getId() == -1){
				$db->insert('properties', $arraySave);
				$this->_property_id = $db->lastInsertId('properties','property_id');
			}
			else{
				$db->update('properties', $arraySave, 'property_id = '.$this->getId());
	 		}
			return true;
		} catch (Exception $e){
			/**
			 * TODO(Make Es_Error)
			 */
			Es_Error::set(get_class($this),$e);
		}
		return false;
	}
	/**
	 * @return boolean
	 */
	public function delete() {
		if(!empty($this->_property_id)) {
			$db = Zend_Registry::get('db');
			$db->delete('properties','property_id = '.$this->getId());
			$db->delete('properties_value', 'property_id = '.$this->getId());
		   	unset($this);
		   	return true;
		}
		return false;
	}
	/**
	 * @param array $array
	 * @return Es_Component_Property_Model
	 */
	static function fromArray(Array $array){
		$pm = new Es_Component_Property_Model($array['property_id']);
		$pm->setName($array['property_name']);
		$pm->setDescription($array['property_description']);
		$pm->setAlias($array['property_alias']);
		$pm->setRequired($array['property_required']);
		$pm->setOrder($array['property_order']);
		$pm->setTabId($array['tab_id']);
		$pm->setDatatypeId($array['datatype_id']);
		$pv = Es_Component_Property_Value_Model::fromArray($array);
		if($pv !== null) {
	   		$pm->setPropertyValue($pv);
		} else {
			if(!empty($array['document_id']) && !empty($array['property_id']))
			$pm->setPropertyValue(new Es_Component_Property_Value_Model(null, $array['document_id'], $array['property_id']));
		}
		return $pm;
	}

	/**
	 * @param integer $property_id
	 * @return Es_Component_Property_Model
	 */
	static function fromId($property_id){
		$db = Zend_Registry::get('db');
		$db->setFetchMode(Zend_Db::FETCH_ASSOC);
		$select = $db->select();
		$select->from(array('p'=>'properties'));
		$select->where('p.property_id = ?', (int)$property_id);
		$property = $db->query($select)->fetchAll();
		if(count($property) > 0) {
			return self::fromArray($property[0]);
		} else {
			return null;
		}
	}
}