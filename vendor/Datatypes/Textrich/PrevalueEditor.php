<?php
namespace Datatypes\Textrich;

use Application\Model\Datatype\AbstractDatatype;

class PrevalueEditor extends AbstractDatatype\PrevalueEditor
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
