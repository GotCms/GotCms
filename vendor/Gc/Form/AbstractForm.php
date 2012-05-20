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
    Zend\Form\Element,
    Zend\InputFilter\InputFilter,
    Gc\Exception,
    Gc\Db\AbstractTable;

abstract class AbstractForm extends Form
{

    public function __construct()
    {
        parent::__construct();
        $this->setAttribute('method', 'post');
        $this->init();
    }
    /**
     * Get db adapter
     * @return Zend_Db_Adapter_Abstract
     */
    public function getAdapter()
    {
        return \Zend\Db\TableGateway\StaticAdapterTableGateway::getStaticAdapter();
    }

    /**
     * Load values
     * @param \Gc\Db\AbstractTable
     * @return \Gc\Form\AbstractForm
     */
    public function loadValues(AbstractTable $table)
    {
        $data = $table->getData();
        if(is_array($data))
        {
            foreach($data as $element_name => $element_value)
            {
                if($element = $this->get($element_name))
                {
                    if($element->getAttribute('type') == 'Zend\Form\Element\Password')
                    {
                        continue;
                    }

                    $element->setAttribute('value', $element_value);
                    $validators = $element->getAttribute('validators');
                    if(!empty($validators))
                    {
                        foreach($validators as $validator)
                        {
                            if($validator instanceof \Zend\Validator\Db\NoRecordExists)
                            {
                                $validator->setExclude(array('field' => 'id', 'value' => $table->getId()));
                            }
                        }
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
    static function addContent(Form $form, $elements)
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
            if($elements->getBelongsTo() === NULL)
            {
                $elements->setIsArray(FALSE);
            }

            $form->addElement($elements);
        }
        elseif(is_string($elements))
        {
            $hiddenElement = new Element\Hidden('hidden'.uniqid());
            $hiddenElement->addDecorator('Description', array('escape' => false));
            $hiddenElement->setDescription($elements);
            $form->addElement($hiddenElement);
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
        if($this->getInputFilter()->has($name))
        {
            $element = $this->getInputFilter()->get($name);
            return $element->getValue();
        }

        return NULL;
    }
}
