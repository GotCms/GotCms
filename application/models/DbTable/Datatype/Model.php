<?php
/**
 * @author Rambaud Pierre
 *
 */
class Es_Model_DbTable_Datatype_Model extends Es_Db_Table implements Es_Interface_Iterable
{
	protected $_name = 'datatypes';
	protected $_model;

	/**
	 * @param integer $defaultId
	 */
	public function init($id = NULL)
	{
		$this->setId($id);
	}

	public function setModelId($value)
	{
		$this->setData('model_id', $value);
		$this->_model = null;
		$this->setModel();

		return $this;
	}

	private function setModel()
	{
		if($this->_model === null)
		{
			$this->_model = Es_Model_DbTable_Model_Model::fromId($this->getModelId());
		}

		return $this;
	}

	public function getModel()
	{
		return $this->_model;
	}

	public function setPrevalueValue($value)
	{
		if(is_string($value)) $value = unserialize($value);
		$this->setData('prevalue_value', $value);

		return $this;
	}

	/**
	 * @param array $array
	 * @return Es_Datatype_Model
	 */
	static function fromArray(Array $array)
	{
		$datatype = new Es_Model_DbTable_Datatype_Model();
		$datatype->init($array['id']);
		$datatype->setName($array['name']);
		$datatype->setModelId($array['model_id']);
		$datatype->setPrevalueValue($array['prevalue_value']);
		return $datatype;
	}

	/**
	 * @param integer $datatype_id
	 * @return Es_Datatype_Model
	 */
	static function fromId($datatype_id)
	{
		$datatype = new Es_Model_DbTable_Datatype_Model();
		$select = $datatype->select()
			->where('id = ?', $datatype_id);
		$datatype = $datatype->fetchRow($select);
		if(!empty($datatype))
		{
			return self::fromArray($datatype->toArray());
		}
		else
		{
			return FALSE;
		}
	}

	public function save()
	{
		$arraySave = array(
			'name' => $this->getName()
			, 'prevalue_value' => serialize($this->getPrevalueValue())
			, 'model_id' => $this->getModelId()
		);

		try
		{
			$id = $this->getId();
			if(empty($id))
			{
				$this->setId($this->insert($arraySave));
				$id = $this->getId();
			}
			else
			{
				$this->update($arraySave, sprintf('id = %d', $id));
	 		}

			return $id;
		}
		catch (Exception $e)
		{
			/**
			 * TODO(Make Es_Error)
			 */
			Es_Error::set(get_class($this),$e);
		}

		return FALSE;
	}

	public function delete()
	{
		$id = $this->getId();
		if(!empty($id))
		{
			if(parent::delete(sprintf('id = %d', $id)))
			{
				unset($this);
				return TRUE;
			}
		}

		return FALSE;
	}

	public function getId()
	{
		return $this->getData('id');
	}

	public function getName()
	{
		return $this->getData('name');
	}

	/* (non-PHPdoc)
	 * @see include/Es/Interface/Es_Interface_Iterable#getParent()
	 */
	public function getParent()
	{
		return FALSE;
	}

	/* (non-PHPdoc)
	 * @see include/Es/Interface/Es_Interface_Iterable#getChildren()
	 */
	public function getChildren()
	{
		return FALSE;
	}

	/* (non-PHPdoc)
	 * @see include/Es/Interface/Es_Interface_Iterable#getIterableId()
	 */
	public function getIterableId()
	{
		return 'datatype_'.$this->getId();
	}

	/* (non-PHPdoc)
	 * @see include/Es/Interface/Es_Interface_Iterable#getUrl()
	 */
	public function getUrl()
	{
		return 'javascript:loadController(\''.Zend_Controller_Action_HelperBroker::getStaticHelper('url')->url(array('datatype' => 'development', 'action' => 'edit')).'/type/datatype/id/'.$this->getId().'\')';
	}

	/* (non-PHPdoc)
	 * @see include/Es/Interface/Es_Interface_Iterable#getIcon()
	 */
	public function getIcon()
	{
		return 'file';
	}
}