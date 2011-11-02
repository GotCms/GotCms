<?php
class Datatypes_Textarea_PrevalueEditor extends Es_Datatype_Abstract_PrevalueEditor  {

    public function save($request = null) {
        //Save prevalue in column datatypes_prevalue_value
        $rows = $request->getParam('rows', '');
        $cols = $request->getParam('cols','');
        $wrap = $request->getParam('wrap','');
        $options = $request->getParam('Options','');
        $disabled = !empty($options[0]) ? $options[0] : '';
        $readonly = !empty($options[1]) ? $options[1] : '';

        $this->setConfiguration(array('cols'=>$cols,'rows'=> $rows, 'wrap'=>$wrap, 'disabled'=>$disabled, 'readonly'=>$readonly));

        return $this->getConfiguration();
    }

    public function load() {
        /*
            - cols  :      number of caracters display per line
            - rows :     define the number of line visible in text zone
            - wrap :     Possible values are : hard / off / soft
                        define if line returns are automatic (hard / soft)
                        or if the horizontal display if exceeded (off)
            - disabled :     render the zone grey and unmodifiable
            - readonly :     render the zone unmodifiable but don't change appearance
        */
        $parameters = $this->getParameters();
        $cols = new Zend_Form_Element_Text('cols');
        $cols->setLabel('Cols');
        if(isset($parameters['cols']))
            $cols->setValue($parameters['cols']);

        $rows = new Zend_Form_Element_Text('rows');
        $rows->setLabel('Rows');
        if(isset($parameters['rows']))
            $rows->setValue($parameters['rows']);

        $wrap = new Zend_Form_Element_Select('wrap');
        $wrap->setLabel('Wrap');
        $wrap->addMultiOptions(array('hard'=>'hard', 'off'=>'off', 'soft'=>'soft'));
        if(isset($parameters['wrap']))
            $wrap->setValue($parameters['wrap']);


        $options = new Zend_Form_Element_MultiCheckbox('Options', array(
                'multiOptions' => array(
                'disabled' => 'Disabled',
                'readonly' => 'Readonly'
            )
        ));
        $arrayOptions = array();
        if(isset($parameters['disabled']))
            $arrayOptions[] = $parameters['disabled'];
        if(isset($parameters['readonly']))
            $arrayOptions[] = $parameters['readonly'];
        $options->setValue($arrayOptions);

        return array($cols, $rows, $wrap, $options);
    }
}
