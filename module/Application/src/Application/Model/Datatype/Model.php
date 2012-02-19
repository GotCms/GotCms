<?php

namespace Application\Model\Datatype;

use Es\Db\AbstractTable,
    Es\Component\IterableInterface,
    Application\Model\Property\Model as PropertyModel,
    Datatypes;

class Model extends AbstractTable implements IterableInterface
{
    protected $_name = 'datatypes';
    protected $_model;
    static protected $_datatypes = array();

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
        $datatype = new Model();
        $datatype->setData($array);
        return $datatype;
    }

    /**
    * @param integer $datatype_id
    * @return Es_Datatype_Model
    */
    static function fromId($datatype_id)
    {
        $datatype = new Model();
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
        $array_save = array(
            'name' => $this->getName()
            , 'prevalue_value' => serialize($this->getPrevalueValue())
            , 'model' => $this->getModel()
        );

        try
        {
            $id = $this->getId();
            if(empty($id))
            {
                $this->setId($this->insert($array_save));
                $id = $this->getId();
            }
            else
            {
                $this->update($array_save, sprintf('id = %d', $id));
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

    /* (non-PHPdoc)
    * @see include/Es/Interface/Es_Interface_Iterable#getId()
    */
    public function getId()
    {
        return $this->getData('id');
    }

    /* (non-PHPdoc)
    * @see include/Es/Interface/Es_Interface_Iterable#getName()
    */
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
        return '';
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
    * @return Model
    */
    static function savePrevalueEditor(Es_Model_DbTable_Datatype_Abstract $datatype)
    {
        $datatype->getPrevalueEditor()->save();
        return $datatype->getConfig();
    }

    /**
    *
    * @param Es_Model_DbTable_Property_Model $property
    *
    * @return mixte
    */
    static function saveEditor(Es_Model_DbTable_Property_Model $property)
    {
        $datatype = self::loadDatatype($property->getDatatypeId(), $property->getDocumentId());
        $datatype->getEditor($property)->save();
        if(!$property->saveValue())
        {
            return FALSE;
        }
        else
        {
            return TRUE;
        }
    }

    /**
    *
    * @param AbstractDatatype $datatype
    *
    * @return mxite
    */
    static function loadPrevalueEditor(AbstractDatatype $datatype)
    {
        return $datatype->getPrevalueEditor()->load();
    }

    /**
    *
    * @param PropertyModel $property
    * @param Es_Model_DbTable_Document_Model $document
    *
    * @return mixte
    */
    static function loadEditor(PropertyModel $property)
    {
        $datatype = self::loadDatatype($property->getDatatypeId(), $property->getDocumentId());
        return $datatype->getEditor($property)->load();
    }

    /**
    *
    * @param integer $datatype_id
    * @param optional integer $document_id
    *
    * @return Es_Model_DbTable_Datatype_Abstract
    */
    static function loadDatatype($datatype_id, $document_id = NULL)
    {
        if(empty(self::$_datatypes[$datatype_id][$document_id]))
        {
            if(empty(self::$_datatypes[$datatype_id]))
            {
                self::$_datatypes[$datatype_id] = array();
            }

            $datatype = Model::fromId($datatype_id);
            $class = 'Datatypes\\'.$datatype->getModel().'\Datatype';

            $object = new $class();
            $object->load($datatype, $document_id);
            self::$_datatypes[$datatype_id][$document_id] = $object;
        }

        return self::$_datatypes[$datatype_id][$document_id];
    }
}
