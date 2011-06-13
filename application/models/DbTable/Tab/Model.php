<?php
/**
 * @author Rambaud Pierre
 *
 */
class Es_Model_DbTable_Tab_Model extends Es_Db_Table
{
	protected $_name = 'tabs';

	/**
	 * @param integer $defaultId
	 */
	public function __construct($defaultId = -1, $documentType_id = -1)
	{
		$this->setId($defaultId);
		$this->setDocumentTypeId($documentType_id);
		$this->setOrder();
	}

	/**
	 * @param string $value
	 */
	public function setOrder($value = -1)
	{
		if($value == -1) {
			$this->verifyOrder($value);
		}
		$this->_order = (int)$value;
		return $this;
	}
	/**
 	* @return string|string
 	*/
	private function verifyOrder(&$order)
	{
		$select = $this->select();
		$select->from(array('t'=>'tabs'),array('maxOrder'=>'MAX(order)'));
		$select->where('document_type_id = ?',$this->getDocumentTypeId());
		$row = $this->query($select)->fetchAll();
		if(count($row) != 0)
		{
			$order = $row[0]['maxOrder'] + 1;
		}
		else
		{
			$order = 1;
		}
	}

	/**
	 * @return boolean
	 */
	public function save()
	{
		$arraySave = array('name'=>$this->getName(),
							'description'=>$this->getDescription(),
							'order'=>$this->getOrder(),
							'document_type_id'=>$this->getDocumentTypeId()
							);

		try
		{
			if($this->getId() == -1)
			{
				$this->insert('tabs', $arraySave);
				$this->_id = $this->lastInsertId('tabs','id');
			}
			else
			{
				$this->update('tabs', $arraySave, 'id = '.$this->getId());
	 		}

			return true;
		}
		catch (Exception $e)
		{
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
	public function delete()
	{
		if(!empty($this->_id))
		{
			$select = $this->select();
			$select->from(array('p'=>'properties'));
			$select->where('id = ? ', $this->getId());
			$row = $this->query($select)->fetchAll();
			if(count($row) == 0)
			{
				$this->delete('tabs','id = '.$this->getId());
		   		unset($this);
		   		return true;
			}
		}

		return false;
	}

	/**
	 * @param array $array
	 * @return Es_Component_Model
	 */
	static function fromArray(Array $array)
	{
		$t = new Es_Component_Model($array['id']);
		$t->setName($array['name']);
		$t->setDescription($array['description']);
		$t->setOrder($array['order']);
		$t->setDocumentTypeId($array['document_type_id']);
		return $t;
	}

	/**
	 * @param integer $id
	 * @return Es_Component_Model
	 */
	static function fromId($id)
	{
		$select = $this->select();
		$select->from(array('t'=>'tabs'));
		$select->where('id = ?', (int)$id);
		$tab = $this->query($select)->fetchAll();
		if(count($tab) > 0)
		{
			return self::fromArray($tab[0]);
		}
		else
		{
			return null;
		}
	}
}