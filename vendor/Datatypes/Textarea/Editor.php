<?php
namespace Datatypes\Textarea;

use Application\Model\Datatype\AbstractDatatype\AbstractEditor,
    Zend\Form\Element;

class Editor extends AbstractEditor
{
    public function save()
    {
        $value = $this->getRequest()->getParam($this->getName());
        $this->setValue($value);
    }

    public function load()
    {
        $textarea = new Element\Textarea($this->getName());
        $textarea->setLabel($this->getProperty()->getName());
        $textarea->setValue($this->getProperty()->getValue());

        return $textarea;
    }
}

