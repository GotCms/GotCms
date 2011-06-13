<?php

class Development_Form_DocumentType extends Es_Form
{

	private $_subDocumentTypeForms = array(
		'infos' => array(
			'legend' => 'Infos'
			, 'decorators' => array(
				'FormElements'
				, array(
					'HtmlTag'
					, array(
						'tag' => 'dl'
						, 'id' => 'tabs-1')
			)))
		, 'views' => array(
			'legend' => 'View'
			,'decorators' => array(
				'FormElements'
				, array(
					'HtmlTag'
					, array(
						'tag' => 'dl'
						, 'id' => 'tabs-2')
			)))
		, 'properties' => array(
			'legend' => 'Properties'
			, 'decorators' => array(
				'FormElements'
				, array(
					'HtmlTag'
					, array(
						'tag' => 'dl'
						, 'id' => 'tabs-3')
			)))
		, 'tabs' => array(
			'legend' => 'Tabs'
			, 'decorators' => array(
				'FormElements'
				, array(
					'HtmlTag'
					, array(
						'tag' => 'dl'
						, 'id' => 'tabs-4')
		)))
	);

	private $_subDocumentTypeForms = array();


	/**
	 * @param unknown_type $values
	 * @return unknown_type
	 */
	public function init()
	{
		$this->getInfos()
			->getViews()
			->getProperties()
			->getTabs();

		$this->addDecorator('FormElements')
		     ->addDecorator('HtmlTag', array('tag'  =>  'div', 'class'  =>  'zend-form'))
		     ->addDecorator('Form');
	}

	/**
	 * @param unknown_type $values
	 * @return unknown_type
	 */
	private function getInfos($values = array())
	{
		$sub_form = new Zend_Form_SubForm($this->_subDocumentTypeForms['infos']);
		$sub_form->addElement('text', 'name', array('label' => 'Name','value' => ''));
		$sub_form->addElement('text', 'description', array('label' => 'Description','value' => ''));
		$sub_form->addElement('select', 'icon_id', array('label' => 'Icon', 'multioptions' => $options));

		return $this->addSubForm($sub_form, 'infos');
	}

	/**
	 * @param unknown_type $values
	 * @return unknown_type
	 */
	private function getViews($values = array())
	{
		$sub_form = new Zend_Form_SubForm($this->_subDocumentTypeForms['views']);
		$sub_form->addElement('select', 'default_view_id', array('label' => 'Default view','value' => $selected,'multioptions' => $options,'required'  =>  true, 'requiredSuffix' => ' * '));
		$sub_form->addElement('select', 'all_views', array('id' => 'view_name','label' => 'Add view','value' => $selected,'multioptions' => $options));
		$sub_form->addElement('button', 'icon_id', array('id' => 'button_add','label' => 'Add view','alt' => 'Add view','class' => 'button-add button-add-view'));

		return $this->addSubForm($sub_form, 'infos');
	}

	/**
	 * @param unknown_type $values
	 * @return unknown_type
	 */
	private function getProperties($values = array())
	{
		$tabs = array('' => '-- Select tab --');
		$collection =  new Es_Model_DbTable_Datatype_Collection();
		$datatypes = $collection->getDatatypesSelect();

		$sub_form = new Zend_Form_SubForm($this->_subDocumentTypeForms['properties']);
		$sub_form->addElement('text', 'name', array('label' => 'Name','value' => ''));
		$sub_form->addElement('text', 'identifier', array('label' => 'Alias','value' => ''));
		$sub_form->addElement('select', 'tabs', array('label' => 'Tab','class' => 'select-options','value' => '','multioptions' => $tabs));
		$sub_form->addElement('select', 'datatype', array('label' => 'Datatype','value' => '','multioptions' => $datatypes));
		$sub_form->addElement('text', 'description', array('label' => 'Description','value' => ''));
		$sub_form->addElement('checkbox', 'required', array('label' => 'Required'));
		$sub_form->addElement('button', 'add', array('id' => 'property_add','label' => 'Add property','alt' => 'Add property','class' => 'button-add button-add-property'));

		return $this->addSubForm($sub_form, 'properties');
	}

	/**
	 * @param unknown_type $values
	 * @return unknown_type
	 */
	private function getTabs($values = array())
	{
		$sub_form = new Zend_Form_SubForm($this->_subDocumentTypeForms['tabs']);
		$sub_form->addElement('text', 'name', array('label' => 'Name','value' => ''));
		$sub_form->addElement('text', 'description', array('label' => 'Description','value' => ''));
		$sub_form->addElement('text', 'description', array('label' => 'Description','value' => ''));
		$sub_form->addElement('button', 'add', array('button', array('value' => 'addTab','id' => 'tab_img_add','class' => 'button-add button-add-tab','label' => 'Add tab', 'alt' => 'Add tab')));

		return $this->addSubForm($sub_form, 'tabs');
	}
}
