<?php
namespace Datatypes\Textarea;

use Gc\Datatype\AbstractDatatype\AbstractEditor,
    Zend\Form\Element;

class Editor extends AbstractEditor
{
    public function save()
    {
        $value = $this->getRequest()->post()->get($this->getName());
        $this->setValue($value);
    }

    public function load()
    {
        $config = $this->getConfig();
        $textarea = new Element\Textarea($this->getName());
        $textarea->setLabel($this->getProperty()->getName());
        $textarea->setValue($this->getProperty()->getValue());

        foreach($config as $key => $value)
        {
            if(!empty($value))
            {
                $textarea->setAttrib($key, $value);
            }
        }

        return $textarea;
    }
}

