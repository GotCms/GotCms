<?php

class Es_Validate_Identifier extends Zend_Validate_Abstract
{
    const NOT_IDENTIFIER    = 'notIdentifier';

	protected $_pattern = '~^[a-zA-Z_]+$~';
	protected $_messageTemplates = array(
		self::NOT_IDENTIFIER    => "'%value%' can only contains alphabetic characters and '_'"
	);

	public function isValid($value)
	{
		$this->_setValue($value);

		if(!preg_match($this->_pattern, $value))
		{
			$this->_error(self::NOT_IDENTIFIER);
			return FALSE;
		}

		return TRUE;
	}
}