<?php
namespace Datatypes\Textrich;

use Gc\Datatype\AbstractDatatype\AbstractPrevalueEditor;

class PrevalueEditor extends AbstractPrevalueEditor
{

    public function save($request = null) {
        //Save prevalue in column Datatypes\prevalue_value

        $this->setConfiguration(array());

        return $this->getConfiguration();
    }

    public function load() {
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
