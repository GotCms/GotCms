<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Form
 */

namespace Gc\View\Helper;

use Zend\Form\ElementInterface,
    Zend\Form\Element\Checkbox as CheckboxElement,
    Zend\Form\View\Helper\FormCheckbox as ZendFormCheckbox;

/**
 * @category   Zend
 * @package    Zend_Form
 * @subpackage View
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
