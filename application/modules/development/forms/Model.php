<?php
class Development_Form_Model extends Es_Form
{
	public function init()
	{
		$this->setMethod(self::METHOD_POST);
		$this->setElementsBelongTo('model');

		$name = new Zend_Form_Element_Text('name');
		$name->setRequired(TRUE)
			->setLabel('Name')
			->setAttrib('class', 'input-text')
			->addValidator(new Zend_Validate_NotEmpty())
			->addValidator(new Zend_Validate_Db_NoRecordExists(array(
				'table' => 'models'
				, 'field' => 'name'
				))
			);

		$identifier  = new Zend_Form_Element_Select('identifier');

		$path = APPLICATION_PATH.'/../library/Datatypes/';
		$list_dir = glob($path.'*', GLOB_ONLYDIR);
		foreach($list_dir as $dir)
		{
			$dir = str_replace($path, '', $dir);
			$identifier->addMultiOption($dir, $dir);
		}

		$identifier->setRequired(TRUE)
			->setLabel('Identifier')
			->addValidator(new Zend_Validate_NotEmpty())
			->addValidator(new Es_Validate_Identifier())
			->addValidator(new Zend_Validate_Db_NoRecordExists(array(
				'table' => 'models'
				, 'field' => 'identifier'
				))
			);

		$description  = new Zend_Form_Element_Text('description');
		$description->setLabel('Description')
			->setAttrib('class', 'input-text');

		$submit = new Zend_Form_Element_Submit('submit');
		$submit->setAttrib('class', 'input-submit')
			->setLabel('Add');


		$this->addElements(array($name, $identifier, $description, $submit));
	}
}