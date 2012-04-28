<?php
namespace Gc\Validator;

use Zend\Validator\AbstractValidator;
class Identifier extends AbstractValidator
{
    const NOT_IDENTIFIER    = 'notIdentifier';

    protected $_pattern = '~^[a-zA-Z0-9_-]+$~';
    protected $_messageTemplates = array(
        self::NOT_IDENTIFIER => "'%value%' can only contains alphabetic characters and '_'"
    );

    public function __construct()
    {
        parent::__construct();
    }

    public function isValid($value)
    {
        $this->setValue($value);

        if(!preg_match($this->_pattern, $value))
        {
            $this->_error(self::NOT_IDENTIFIER);
            return FALSE;
        }

        return TRUE;
    }
}
