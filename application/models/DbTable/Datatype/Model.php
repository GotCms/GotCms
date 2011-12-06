<?php
/**
 * @author Rambaud Pierre
 *
 */
class Es_Model_DbTable_Datatype_Model extends Es_Db_Table implements Es_Interface_Iterable
{
    protected $_name = 'datatypes';
    protected $_model;

    public function setModelId($value)
    {
        $this->setData('model_id', $value);
        $this->_model = null;
        $this->setModel();

        return $this;
    }

    public function setPrevalueValue($value)
    {
        if(is_string($value)) $value = unserialize($value);
        $this->setData('prevalue_value', $value);

        return $this;
    }

    /**
    * @param array $array
    * @return Es_Datatype_Model
    */
    static function fromArray(Array $array)
    {
        $datatype = new Es_Model_DbTable_Datatype_Model();
        $datatype->setData($array);
        return $datatype;
    }

    /**
    * @param integer $datatype_id
    * @return Es_Datatype_Model
    */
    static function fromId($datatype_id)
    {
        $datatype = new Es_Model_DbTable_Datatype_Model();
        $select = $datatype->select()
            ->where('id = ?', $datatype_id);

        $datatype = $datatype->fetchRow($select);
        if(!empty($datatype))
        {
            return self::fromArray($datatype->toArray());
        }
        else
        {
            return FALSE;
        }
    }

    public function save()
    {
        $arraySave = array(
            'name' => $this->getName()
            , 'prevalue_value' => serialize($this->getPrevalueValue())
            , 'model' => $this->getModel()
        );

        try
        {
            $id = $this->getId();
            if(empty($id))
            {
                $this->setId($this->insert($arraySave));
                $id = $this->getId();
            }
            else
            {
                $this->update($arraySave, sprintf('id = %d', $id));
            }

            return $id;
        }
        catch (Exception $e)
        {
            /**
            * TODO(Make Es_Error)
            */
            Es_Error::set(get_class($this),$e);
        }

        return FALSE;
    }

    public function delete()
    {
        $id = $this->getId();
        if(!empty($id))
        {
            if(parent::delete(sprintf('id = %d', $id)))
            {
                unset($this);
                return TRUE;
            }
        }

        return FALSE;
    }

    public function getId()
    {
        return $this->getData('id');
    }

    public function getName()
    {
        return $this->getData('name');
    }

    /* (non-PHPdoc)
    * @see include/Es/Interface/Es_Interface_Iterable#getParent()
    */
    public function getParent()
    {
        return FALSE;
    }

    /* (non-PHPdoc)
    * @see include/Es/Interface/Es_Interface_Iterable#getChildren()
    */
    public function getChildren()
    {
        return FALSE;
    }

    /* (non-PHPdoc)
    * @see include/Es/Interface/Es_Interface_Iterable#getIterableId()
    */
    public function getIterableId()
    {
        return 'datatype_'.$this->getId();
    }

    /* (non-PHPdoc)
    * @see include/Es/Interface/Es_Interface_Iterable#getUrl()
    */
    public function getUrl()
    {
        return 'javascript:loadController(\''.Zend_Controller_Action_HelperBroker::getStaticHelper('url')->url(array('datatype' => 'development', 'action' => 'edit')).'/type/datatype/id/'.$this->getId().'\')';
    }

    /* (non-PHPdoc)
    * @see include/Es/Interface/Es_Interface_Iterable#getIcon()
    */
    public function getIcon()
    {
        return 'file';
    }

    /**
    *
    * @param Es_Model_DbTable_Datatype_Abstract $datatype_model
    *
    * @return Es_Model_DbTable_Datatype_Model
    */
    static function savePrevalueEditor(Es_Model_DbTable_Datatype_Abstract $datatype)
    {
        $datatype->getPrevalueEditor()->save();
        return $datatype->getConfig();
    }

    /**
    *
    * @param Es_Model_DbTable_Datatype_Model $datatype_model
    *
    * @return Es_Model_DbTable_Datatype_Abstract_Editor
    */
    static function saveEditor(Es_Model_DbTable_Datatype_Abstract $datatype, $property_id)
    {
        return $datatype->getEditor($property_id)->save($this->getRequest());
    }

    /**
    *
    * @param Es_Model_DbTable_Datatype_Abstract $datatype_model
    *
    * @return Es_Model_DbTable_Datatype_Abstract_PrevalueEditor
    */
    static function loadPrevalueEditor(Es_Model_DbTable_Datatype_Abstract $datatype)
    {
        return $datatype->getPrevalueEditor()->load();
    }

    /**
    *
    * @param Es_Model_DbTable_Datatype_Model $datatype_model
    *
    * @return Es_Model_DbTable_Datatype_Abstract_Editor
    */
    static function loadEditor(Es_Model_DbTable_Datatype_Abstract $datatype, $property_id)
    {
        return $datatype->getEditor($property_id)->load();
    }

    /**
    *
    * @param integer $datatype_id
    * @param option integer $document_id
    *
    * @return Es_Model_DbTable_Datatype_Abstract
    */
    static function loadDatatype($datatype_id, $document_id = NULL)
    {
        $datatype = Es_Model_DbTable_Datatype_Model::fromId($datatype_id);
        $class = 'Datatypes_'.$datatype->getModel().'_Datatype';

        $object = new $class();
        $object->load($datatype, $document_id, Zend_Controller_Front::getInstance()->getRequest());

        return $object;
    }
}
