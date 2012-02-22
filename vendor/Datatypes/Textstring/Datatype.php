<?php
namespace Datatypes\Textstring;

use Application\Model\Datatype\AbstractDatatype
    , Application\Model\Property\Model as PropertyModel;

class Datatype extends AbstractDatatype
{
    protected $_name = 'textstring';

    public function getEditor(PropertyModel $property)
    {
        $this->_property = $property;
        if($this->_editor === null)
        {
            $this->_editor = new Editor($this);
        }

        return $this->_editor;
    }

    public function getPrevalueEditor()
    {
        if($this->_prevalueEditor === null)
        {
            $this->_prevalueEditor = new PrevalueEditor($this);
        }

        return $this->_prevalueEditor;
    }
}

