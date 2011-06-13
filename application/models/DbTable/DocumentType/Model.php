<?php

/**
 * Es Object
 *
 * @category   	Es_Model_DbTable
 * @package		Es_Model_DbTable_Document_Model
 * @author	  	RAMBAUD Pierre
 */
class Es_Model_DbTable_DocumentType_Model extends Es_Db_Table implements Es_Interface_Iterable
{
	protected $_name = 'document_types';
	protected $_views;

	/**
	 * @param integer $defaultId
	 */
	public function __construct($defaultId = -1)
	{
		$this->setId($defaultId);
	}

	public function getUser()
	{
		if($this->_user === null AND $this->getUserId() != null)
		{
			$this->_user = new Es_Model_DbTable_User_Model($this->getUserId());
		}

		return $this->_user;
	}

	public function addView($view_id)
	{
		$this->_views[] = $view_id;
		return $this;
	}

	/**
	 * @return Es_Component_Tab_Model
	 */
	public function getTabs()
	{
		if($this->_tabs === null )
		{
			$select = $this->select()
				->where('id = ? ', array($this->getId()))
				->order('order');
			$tabs = $this->fetchAll($select);
			$this->_tabs = array();
			foreach($tabs as $tab)
			{
				$this->_tabs[] = Es_Model_DbTable_Tab_Model::fromArray($tab);
			}
		}

		return $this->_tabs;
	}

	public function save()
	{
		$arraySave = array(
			'name' => $this->getName()
			, 'created_at' => $this->getCreatedAt()
			, 'updated_at' => $this->getUpdatedAt()
			, 'description' => $this->getDescription()
			, 'icon_id' => $this->getIconId()
			, 'default_view_id' => $this->getDefaultViewId()
			, 'user_id' => $this->getUserId()
		);

		try
		{
			if($this->getId() == -1)
			{
				$this->insert('document_types', $arraySave);
				$this->setId($this->lastInsertId('document_types','id'));
			}
			else
			{
				$this->update('document_types', $arraySave, 'id = '.$this->getId());
	 		}

			return TRUE;
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
		if(!empty($this->_documentType_id))
		{
			if(parent::delete('id = '.$this->getId()))
			{
				unset($this);
				return TRUE;
			}
		}

		return FALSE;
	}

	/**
	 * @param array $array
	 * @return Es_DocumentType_Model
	 */
	static function fromArray(Array $array)
	{
		$dt = new Es_Model_Dbtable_DocumentType_Model($array['id']);
		$dt->setName($array['name']);
		$dt->setDescription($array['description']);
		$dt->setIcon($array['icon_id']);
		$dt->setCreatedAt($array['created_at']);
		$dt->setUpdatedAt($array['updated_at']);
		$dt->setDefaultViewId($array['default_view_id']);

		return $dt;
	}

	/**
	 * @param integer $documentType_id
	 * @return Es_DocumentType_Model
	 */
	static function fromId($documentType_id)
	{
		$select = $this->select()
			->where('id = ?', (int)$this->getId());
		$documentType = $this->fetchRow($select);
		if(!empty($documentType))
		{
			return self::fromArray($documentType);
		}
		else
		{
			return FALSE;
		}
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

	public function getId()
	{
		return parent::getId();
	}

	public function getName()
	{
		return parent::getName();
	}

	/* (non-PHPdoc)
	 * @see include/Es/Interface/Es_Interface_Iterable#getIterableId()
	 */
	public function getIterableId()
	{
		return 'documenttype_'.$this->getId();
	}

	/* (non-PHPdoc)
	 * @see include/Es/Interface/Es_Interface_Iterable#getUrl()
	 */
	public function getUrl()
	{
		return 'javascript:loadController(\''.Zend_Controller_Action_HelperBroker::getStaticHelper('url')->url(array('action' => 'edit')).'/type/documenttype/id/'.$this->getId().'\')';
	}

	/* (non-PHPdoc)
	 * @see include/Es/Interface/Es_Interface_Iterable#getIcon()
	 */
	public function getIcon()
	{
		if($this->_icon_url === null)
		{
			$icon = Es_Media_Icon_Model::fromId($this->_documentType_icon);
			$this->_icon_url = $icon->getIconUrl();
		}

		return $this->_icon_url;
	}
}