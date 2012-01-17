<?php
class Datatypes_Textrich_PrevalueEditor extends Es_Datatype_Abstract_PrevalueEditor  {

    public function save($request = null) {
        //Save prevalue in column datatypes_prevalue_value

        $this->setConfiguration(array());

        return $this->getConfiguration();
    }

    public function load() {
        $parameters = $this->getParameters();
        $element = new Zend_Form_Element_MultiCheckbox('textrich', array(
                'multiOptions' => array(
                'resize' => 'Resize',
            )
        ));
        $element->setValue(array('bar', 'bat'));
        return array($element);
    }
}
