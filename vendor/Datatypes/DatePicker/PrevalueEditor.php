<?php
namespace Datatypes\DatePicker;

use Gc\Datatype\AbstractDatatype\AbstractPrevalueEditor;

class PrevalueEditor extends AbstractPrevalueEditor
{

    public function save($request = null) {
        //Save prevalue in column Datatypes\prevalue_value

        $this->setConfiguration(array());

        return $this->getConfig();
    }

    public function load() {
        $configuration = $this->getConfig();

        return array($required, $length);
    }
}
