<?php
namespace Datatypes\RadioButtonList;

use Es\Datatype\AbstractDatatype\AbstractPrevalueEditor;

class PrevalueEditor extends AbstractPrevalueEditor
{
    public function save($request = null)
    {
        //Save prevalue in column Datatypes\prevalue_value
        $this->setConfiguration(array());

        return $this->getConfiguration();
    }

    public function load()
    {
        $configuration = $this->getConfiguration();

        return array();
    }
}
