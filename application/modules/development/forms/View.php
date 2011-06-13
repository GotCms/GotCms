<?php
class Development_Form_View extends Es_Form
{
	public function init()
	{
		$this->setMethod(self::METHOD_POST);
		$this->setElementsBelongTo('view');

		$name = new Zend_Form_Element_Text('name');
		$name->setRequired(TRUE)
			->setLabel('Name')
			->setAttrib('class', 'input-text')
			->addValidator(new Zend_Validate_NotEmpty())
			->addValidator(new Zend_Validate_Db_NoRecordExists(array(
				'table' => 'views'
				, 'field' => 'name'
				))
			);

		$identifier  = new Zend_Form_Element_Text('identifier');
		$identifier->setRequired(TRUE)
			->setLabel('Identifier')
			->setAttrib('class', 'input-text')
			->addValidator(new Zend_Validate_NotEmpty())
			->addValidator(new Es_Validate_Identifier())
			->addValidator(new Zend_Validate_Db_NoRecordExists(array(
				'table' => 'views'
				, 'field' => 'identifier'
				))
			);

		$description  = new Zend_Form_Element_Text('description');
		$description->setLabel('Description')
			->setAttrib('class', 'input-text');

		$content  = new Zend_Form_Element_Textarea('content');
		$content->setLabel('Content');

		$submit = new Zend_Form_Element_Submit('submit');
		$submit->setAttrib('class', 'input-submit')
			->setLabel('Add');


		$this->addElements(array($name, $identifier, $description, $content, $submit));
	}
}