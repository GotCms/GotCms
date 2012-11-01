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
 * @subpackage  View\Helper
 * @author      Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license     GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link        http://www.got-cms.com
 */

namespace Gc\View\Helper;

use Zend\Form\ElementInterface,
    Zend\Form\Element\Checkbox as CheckboxElement,
    Zend\Form\View\Helper\FormCheckbox as ZendFormCheckbox,
    Zend\Form\Exception;

/**
 * Render form checkbox
 */
class FormCheckbox extends ZendFormCheckbox
{
    /**
     * Render a form <input> element from the provided $element
     *
     * @param  ElementInterface $element
     * @throws Exception\InvalidArgumentException
     * @throws Exception\DomainException
     * @return string
     */
    public function render(ElementInterface $element)
    {
        if(!$element instanceof CheckboxElement)
        {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s requires that the element is of type Zend\Form\Element\Checkbox',
                __METHOD__
            ));
        }

        $name = $element->getName();
        if(empty($name) && $name !== 0)
        {
            throw new Exception\DomainException(sprintf(
                '%s requires that the element has an assigned name; none discovered',
                __METHOD__
            ));
        }

        $attributes            = $element->getAttributes();
        $attributes['name']    = $name;
        $attributes['type']    = $this->getInputType();
        $attributes['value']   = $element->getCheckedValue();
        $closingBracket        = $this->getInlineClosingBracket();

        if($element->isChecked())
        {
            $attributes['checked'] = 'checked';
        }

        if($element->getAttribute('class') != 'input-checkbox' or $element->getAttribute('id') == '')
        {
            $rendered = sprintf(
                '<input %s%s',
                $this->createAttributesString($attributes),
                $closingBracket
            );
        }
        else
        {
            unset($attributes['class']);
            $rendered = sprintf(
                '<div class="input-checkbox"><input %s%s<label for="%s"></label></div>',
                $this->createAttributesString($attributes),
                $closingBracket,
                $element->getAttribute('id')
            );
        }

        return $rendered;
    }
}
