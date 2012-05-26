<?php
/**
 * This source file is part of Got CMS.
 *
 * Got CMS is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Got CMS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License along
 * with Got CMS. If not, see <http://www.gnu.org/licenses/lgpl-3.0.html>.
 *
 * PHP Version >=5.3
 *
 * @category    Gc
 * @package     Library
 * @subpackage  Form
 * @author      Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license     GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link        http://www.got-cms.com
 */

namespace Gc\Form;

use Zend\Form\Form,
    Zend\Form\Fieldset,
    Zend\Form\Element,
    Zend\InputFilter\InputFilter,
    Gc\Exception,
    Gc\Db\AbstractTable;

abstract class AbstractForm extends Form
{

    /**
     * Constructor
     *
     * @param null|string|int $name Optional name for the element
     */
    public function __construct($name = null)
    {
        parent::__construct($name);
        $this->setAttribute('method', 'post');
        $this->init();
    }

    /**
     * Initialize form
     */
    public function init(){}

    /**
     * Get db adapter
     * @return Zend_Db_Adapter_Abstract
     */
    public function getAdapter()
    {
        return \Zend\Db\TableGateway\Feature\GlobalAdapterFeature::getStaticAdapter();
    }

    /**
     * Load values
     * @param \Gc\Db\AbstractTable
     * @return \Gc\Form\AbstractForm
     */
    public function loadValues(AbstractTable $table)
    {
        $data = $table->getData();
        $input_filter = $this->getInputFilter();
        if(is_array($data))
        {
            foreach($data as $element_name => $element_value)
            {
                if($this->has($element_name))
                {
                    $this->get($element_name)->setAttribute('value', $element_value);
                }

                if($input_filter->has($element_name))
                {
                    $validator = $input_filter->get($element_name)->getValidatorChain()->getValidator('Zend\Validator\Db\NoRecordExists');;

                    if(!empty($validator))
                    {
                        $validator->setExclude(array('field' => 'id', 'value' => $table->getId()));
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Add content to form
     * @param \Zend\Form\Form $form
     * @param mixed $elements
     * @static
     * @return void
     */
    static function addContent(Fieldset $form, $elements)
    {
        if(is_array($elements))
        {
            foreach($elements as $element)
            {
                self::addContent($form, $element);
            }
        }
        elseif($elements instanceof Element)
        {
            $form->add($elements);
        }
        elseif(is_string($elements))
        {
            $hidden_element = new Element('hidden'.uniqid());
            $hidden_element->setAttribute('content', $elements);
            $form->add($hidden_element);
        }
        else
        {
            throw new Exception("Invalid element ".__CLASS__."::".__METHOD__.")");
        }
    }

    /**
     * Return element value
     * @param string $name
     * @return string
     */
    public function getValue($name)
    {
        if($this->has($name))
        {
            return $this->get($name)->getAttribute('value');
        }

        return NULL;
    }
}
