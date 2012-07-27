<?php
/**
 * This source file is part of GotCms.
 *
 * GotCms is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * GotCms is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License along
 * with GotCms. If not, see <http://www.gnu.org/licenses/lgpl-3.0.html>.
 *
 * PHP Version >=5.3
 *
 * @category    Gc
 * @package     Library
 * @subpackage  Validator
 * @author      Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license     GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link        http://www.got-cms.com
 */

namespace Gc\Validator;

use Zend\Validator\AbstractValidator;
class Identifier extends AbstractValidator
{
    const NOT_IDENTIFIER    = 'notIdentifier';

    protected $_pattern = '~^[a-zA-Z0-9_]+$~';
    protected $_messageTemplates = array(
        self::NOT_IDENTIFIER => "'%value%' can only contains alphabetic characters and '_'",
    );

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
