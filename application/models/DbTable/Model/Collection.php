<?php

/**
 * Es Object
 *
 * @category   	Es_Model_DbTable
 * @package		Es_Model_DbTable_Model_Model
 * @author	  	RAMBAUD Pierre
 */
class Es_Model_DbTable_Model_Collection extends Es_Db_Table implements Es_Interface_Iterable
{

	protected $_name = 'models';
	protected $_models;

	public function init()
	{
		$this->setModels();
	}

	private function setModels()
	{
		$select = $this->select()
			->order(array('name'));
		$rows = $this->fetchAll($select);
		$models = array();
		foreach($rows as $row)
		{
			$models[] = Es_Model_DbTable_Model_Model::fromArray($row->toArray());
		}

		$this->_models = $models;
	}

	public function getModels()
	{
		return $this->_models;
	}

	public function getModelsSelect()
	{
		$select = array();
		foreach($this->getModels() as $model)
		{
			$select[$model->getId()] = $model->getName();
		}

		return $select;
	}


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
		return $this->getModels();
	}

	/* (non-PHPdoc)
	 * @see include/Es/Interface/Es_Interface_Iterable#getId()
	 */
	public function getId()
	{
		return null;
	}

	/* TODO Finish icon in Es_Controller_Collection
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
		return 'models';
	}

	/* (non-PHPdoc)
	 * @see include/Es/Interface/Es_Interface_Iterable#getName()
	 */
	public function getName()
	{
		return 'Models';
	}

	/* (non-PHPdoc)
	 * @see include/Es/Interface/Es_Interface_Iterable#getUrl()
	 */
	public function getUrl()
	{
		return 'javascript:loadController(\''.Zend_Controller_Action_HelperBroker::getStaticHelper('url')->url(array('controller'=>'development', 'action'=>'models')).'\')';
	}
}