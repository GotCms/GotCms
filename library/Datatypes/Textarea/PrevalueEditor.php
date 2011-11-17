<?php
class Datatypes_Textarea_PrevalueEditor extends Es_Model_DbTable_Datatype_Abstract_PrevalueEditor
{
    public function save()
    {
        //Save prevalue in column datatypes_prevalue_value
        $rows = $this->getParam('rows', '');
        $cols = $this->getParam('cols','');
        $wrap = $this->getParam('wrap','');
        $options = $this->getParam('Options','');
        $disabled = !empty($options[0]) ? $options[0] : '';
        $readonly = !empty($options[1]) ? $options[1] : '';

        $this->setConfig(array(
            'cols' => $cols
            , 'rows' => $rows
            , 'wrap' => $wrap
            , 'disabled' => $disabled
            , 'readonly' => $readonly)
        );
    }

    public function load()
    {
        /*
            - cols     :   Number of caracters display per line
            - rows     :   Define the number of line visible in text zone
            - wrap     :   Possible values are : hard / off / soft
                                define if line returns are automatic (hard / soft)
                                or if the horizontal display if exceeded (off)
            - disabled :   Render the zone grey and unmodifiable
            - readonly :   Render the zone unmodifiable but don't change appearance
        */

        $config = $this->getConfig();
        $cols = new Zend_Form_Element_Text('cols');
        $cols->setLabel('Cols');
        if(!empty($config['cols']))
        {
            $cols->setValue($config['cols']);
        }

        $rows = new Zend_Form_Element_Text('rows');
        $rows->setLabel('Rows');
        if(!empty($config['rows']))
        {
            $rows->setValue($config['rows']);
        }

        $wrap = new Zend_Form_Element_Select('wrap');
        $wrap->setLabel('Wrap');
        $wrap->addMultiOptions(array('hard'=>'hard', 'off'=>'off', 'soft'=>'soft'));
        if(!empty($config['wrap']))
        {
            $wrap->setValue($config['wrap']);
        }


        $options = new Zend_Form_Element_MultiCheckbox('Options', array(
                'multiOptions' => array(
                'disabled' => 'Disabled',
                'readonly' => 'Readonly'
            )
        ));

        $arrayOptions = array();
        if(!empty($config['disabled']))
        {
            $arrayOptions[] = $config['disabled'];
        }

        if(!empty($config['readonly']))
        {
            $arrayOptions[] = $config['readonly'];
        }

        $options->setValue($arrayOptions);

        return array($cols, $rows, $wrap, $options);
    }
}
