<?php
namespace Datatypes\Textarea;

use Es\Datatype\AbstractDatatype as AbstractDatatype
    , Es\Property\Model as PropertyModel;

class Datatype extends AbstractDatatype
{
    protected $_name = 'textarea';

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

