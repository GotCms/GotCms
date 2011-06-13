<?php

/**
 * Es Object
 *
 * @category   	Es_Model_DbTable
 * @package		Es_Model_DbTable_Document_Collection
 * @author	  	RAMBAUD Pierre
 */
class Es_Model_DbTable_Document_Collection extends Es_Db_Table implements Es_Interface_Iterable
{
	protected $_documents = array();
	protected $_name = 'documents';

	public function __construct($parent_id = 0)
	{
		$this->setData('parent_id', $parent_id);
		$this->setDocuments();
	}

	private function setDocuments()
	{
		$select = $this->select()
			->where('parent_id = ? ', $this->getParentId());
		$rows = $this->fetchAll($select);
		$documents = array();
		foreach($rows as $row)
		{
			$documents[] = Es_Document_Model::fromArray($row);
		}

		$this->_documents = $documents;
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
		return $this->_documents;
	}

	/* (non-PHPdoc)
	 * @see include/Es/Interface/Es_Interface_Iterable#getId()
	 */
	public function getId()
	{
		return FALSE;
	}

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
		return 'documents';
	}

	/* (non-PHPdoc)
	 * @see include/Es/Interface/Es_Interface_Iterable#getName()
	 */
	public function getName()
	{
		return 'Website';
	}

	/* (non-PHPdoc)
	 * @see include/Es/Interface/Es_Interface_Iterable#getUrl()
	 */
	public function getUrl()
	{
		return 'javascript:loadController(\''.Zend_Controller_Action_HelperBroker::getStaticHelper('url')->url(array('controller'=>'content', 'action'=>'documents')).'\')';
	}

}