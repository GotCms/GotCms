<?php
namespace Datatypes\Textrich;

use Gc\Datatype\AbstractDatatype\AbstractPrevalueEditor;

class PrevalueEditor extends AbstractPrevalueEditor
{

    public function save()
    {
    }

    public function load()
    {
        $parameters = $this->getParameters();
        $element = new Element\MultiCheckbox('textrich', array(
                'multiOptions' => array(
                'resize' => 'Resize',
            )
        ));
        $element->setValue(array('bar', 'bat'));

        return array($element);
    }
}
