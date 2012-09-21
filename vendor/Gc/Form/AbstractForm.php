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
/**
 * Abstract Form overload Zend\Form\Form
 * This is better to initialize somes values, retrieve adapter
 * add dynamic content, etc...
 */
abstract class AbstractForm extends Form
{
    /**
     * Identifier pattern constante
     * @const IDENTIFIER_PATTERN
     */
    const IDENTIFIER_PATTERN = '~^[a-zA-Z0-9_-]+$~';

    /**
     * Constructor
     *
     * @param null|string|int $name Optional name for the element
     */
    public function __construct($name = null)
    {
        parent::__construct($name);
        $this->setAttribute('method', 'post');
        $this->setUseInputFilterDefaults(FALSE);
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
     * @param AbstractTable $table
     * @return AbstractForm
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
                    $element = $this->get($element_name);
                    $this->get($element_name)->setValue($element_value);
                }

                if($input_filter->has($element_name))
                {
                    $validators = $input_filter->get($element_name)->getValidatorChain()->getValidators();

                    foreach($validators as $validator)
                    {
                        if($validator['instance'] instanceof \Zend\Validator\Db\NoRecordExists)
                        {
                            $validator['instance']->setExclude(array('field' => 'id', 'value' => $table->getId()));
                        }
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Add content to form
     * @param Fieldset $form
     * @param mixed $elements
     * @param string $prefix add belong to for each elements
     * @static
     * @return void
     */
    static function addContent(Fieldset $form, $elements, $prefix = NULL)
    {
        if(empty($elements))
        {
            return;
        }

        if(is_array($elements))
        {
            foreach($elements as $element)
            {
                self::addContent($form, $element, $prefix);
            }
        }
        elseif($elements instanceof Element)
        {
            if(!empty($prefix))
            {
                $id = $elements->getAttribute('id');
                if(empty($id))
                {
                    $id = $elements->getAttribute('name');
                }

                $elements->setAttribute('id', $id . mt_rand());
                $elements->setAttribute('name', $prefix.'['.$elements->getAttribute('name').']');
            }

            $form->add($elements);
        }
        elseif(is_string($elements))
        {
            if(!empty($prefix))
            {
                $rand_id = mt_rand();
                $elements = preg_replace('~name="(.+)(\[.*\])?"~iU', 'name="' . $prefix . '[$1]$2"', $elements);
                $elements = preg_replace('~id="(.+)"~iU', 'id="${1}' . $rand_id . '"', $elements);
                $elements = preg_replace('~("|\')#(.+)("|\')~iU', '${1}#${2}' . $rand_id . '${3}', $elements);
            }

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
    public function getValue($name = NULL)
    {
        if($this->has($name))
        {
            return $this->get($name)->getValue();
        }

        return NULL;
    }
}
