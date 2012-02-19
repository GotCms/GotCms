<?php
namespace Datatypes\RadioButtonList;

use Application\Model\Datatype\AbstractDatatype
    , Application\Model\Property\Model as PropertyModel;

class Datatype extends AbstractDatatype
{
    protected $_name = 'radio-button-list';

    public function getEditor(PropertyModel $property)
    {
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

