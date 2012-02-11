<?php
namespace Es\Form;

use Zend\Form\Form,
    Zend\Form\Element,
    Es\Exception;

abstract class AbstractForm extends Form
{
    /**
    * Get db adapter
    * @return Zend_Db_Adapter_Abstract
    */
    public function getAdapter()
    {
        return Zend_Registry::get('Zend_Db');
    }

    public function loadValues(Es_Db_Table $table)
    {
        $data = $table->getData();
        if(is_array($data))
        {
            foreach($data as $element_name => $element_value)
            {
                if($element = $this->getElement($element_name))
                {
                    $element->setValue($element_value);

                    if($validator = $element->getValidator('Zend_Validate_Db_NoRecordExists'))
                    {
                        $validator->setExclude(array('field' => 'id', 'value' => $table->getId()));
                    }
                }
            }
        }

        return $this;
    }

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
}
