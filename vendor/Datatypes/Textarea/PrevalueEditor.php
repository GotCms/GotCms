<?php
namespace Datatypes\Textarea;

use Gc\Datatype\AbstractDatatype\AbstractPrevalueEditor,
    Zend\Form\Element;

class PrevalueEditor extends AbstractPrevalueEditor
{
    public function save()
    {
        //Save prevalue in column Datatypes\prevalue_value
        $post = $this->getRequest()->post();
        $rows = $post->get('rows', NULL);
        $cols = $post->get('cols', NULL);
        $wrap = $post->get('wrap', NULL);

        $this->setConfig(array(
            'cols' => $cols
            , 'rows' => $rows
            , 'wrap' => $wrap
        ));
    }

    public function load()
    {
        /*
            - cols     :   Number of caracters display per line
            - rows     :   Define the number of line visible in text zone
            - wrap     :   Possible values are : hard / off / soft
                                define if line returns are automatic (hard / soft)
                                or if the horizontal display if exceeded (off)
        */

        $config = $this->getConfig();
        $cols = new Element\Text('cols');
        $cols->setLabel('Cols');
        if(!empty($config['cols']))
        {
            $cols->setValue($config['cols']);
        }

        $rows = new Element\Text('rows');
        $rows->setLabel('Rows');
        if(!empty($config['rows']))
        {
            $rows->setValue($config['rows']);
        }

        $wrap = new Element\Select('wrap');
        $wrap->setLabel('Wrap');
        $wrap->addMultiOptions(array('hard'=>'hard', 'off'=>'off', 'soft'=>'soft'));
        if(!empty($config['wrap']))
        {
            $wrap->setValue($config['wrap']);
        }

        return array($cols, $rows, $wrap);
    }
}
