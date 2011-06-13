<?php
class Es_Model_DbTable_View_Model extends Es_Db_Table implements Es_Interface_Iterable
{
	protected $_name = 'views';

	/**
	 * @param integer $id
	 * @return Es_Model_DbTable_View_Model
	 */
	public function init($id = NULL)
	{
		$this->setId($id);
	}

	/**
	 * @param array $view
	 * @return Es_Model_DbTable_View_Model
	 */
	static function fromArray(Array $array)
	{
		$view = new Es_Model_DbTable_View_Model();
		$view->init($array['id']);
		$view->setName($array['name']);
		$view->setIdentifier($array['identifier']);
		$view->setDescription($array['description']);
		$view->setContent($array['content']);
		$view->setCreatedAt($array['created_at']);
		$view->setUpdatedAt($array['updated_at']);

		return $view;
	}



	/**
 	* @param integer $id
 	* @return Es_Model_DbTable_View_Model
 	*/
	static function fromId($id)
	{
		$view_table = new Es_Model_DbTable_View_Model();
		$select = $view_table->select();
		$select->where('id = ?', $id);
		$view = $view_table->fetchRow($select);
		if(!empty($view))
		{
			return self::fromArray($view->toArray());
		}
		else
		{
			return FALSE;
		}
	}

	/**
	 * @return boolean
	 */
	public function save()
	{
		$arraySave = array('name' => $this->getName(),
			'identifier' => $this->getIdentifier(),
			'description' => $this->getDescription(),
			'content' => $this->getContent(),
			'updated_at' => new Zend_Db_Expr('NOW()')
		);

		try
		{
			if($this->getId() == NULL)
			{
				$arraySave['created_at'] = new Zend_Db_Expr('NOW()');
				$id = $this->insert($arraySave);
				$this->setId($id);
			}
			else
			{
				$this->update($arraySave, 'id = '.(int)$this->getId());
			}

			return $this->getId();
		}
		catch (Exception $e)
		{
			/**
			 * TODO(Make Es_Error)
			 */
			Es_Error::set(get_class($this), $e);
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
	 * @see include/Es/Interface/Es_Interface_Iterable#getId()
	 */
	public function getId()
	{
		return parent::getId();
	}

	/* (non-PHPdoc)
	 * @see include/Es/Interface/Es_Interface_Iterable#getIterableId()
	 */
	public function getIterableId()
	{
		return 'view-'.$this->getId();
	}

	/* (non-PHPdoc)
	 * @see include/Es/Interface/Es_Interface_Iterable#getName()
	 */
	public function getName()
	{
		return parent::getName();
	}

	/* (non-PHPdoc)
	 * @see include/Es/Interface/Es_Interface_Iterable#getUrl()
	 */
	public function getUrl()
	{
		return 'javascript:loadController(\''.Zend_Controller_Action_HelperBroker::getStaticHelper('url')->url(array('controller'=>'development','action'=>'edit')).'/type/view/id/'.$this->getId().'\')';
	}

	/* (non-PHPdoc)
	 * @see include/Es/Interface/Es_Interface_Iterable#getIcon()
	 */
	public function getIcon()
	{
		return 'file';
	}
}
