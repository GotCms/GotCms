<?php

abstract class Es_Form extends Zend_Form
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

    public function addFormContent($elements)
    {
        if(is_array($elements))
        {
            foreach($elements as $element)
            {
                $this->addFormContent($element);
            }
        }
        elseif($elements instanceof Zend_Form_Element)
        {
            if($elements->getBelongsTo() === NULL)
            {
                $elements->setIsArray(FALSE);
            }

            $this->addElement($elements);
        }
        elseif(is_string($elements))
        {
            $hiddenElement = new Zend_Form_Element_Hidden('hidden'.mt_rand());
            $hiddenElement->addDecorator('Description', array('escape' => false));
            $hiddenElement->setDescription($elements);
            $this->addElement($hiddenElement);
        }
        else
        {
            throw new Es_Exception("Invalid element ".get_class($this)."::".__METHOD__.")");
        }

        return $this;
    }
}
