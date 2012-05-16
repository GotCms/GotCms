<?php

namespace Gc\Datatype;

use Gc\Db\AbstractTable,
    Gc\Component\IterableInterface,
    Gc\Property\Model as PropertyModel,
    Datatypes;

class Model extends AbstractTable implements IterableInterface
{
    protected $_name = 'datatype';

    public function setPrevalueValue($value)
    {
        if(is_string($value)) $value = unserialize($value);
        $this->setData('prevalue_value', $value);

        return $this;
    }

    /**
    * @param array $array
    * @return Gc\Datatype\Model
    */
    static function fromArray(Array $array)
    {
        $datatype_table = new Model();
        $datatype_table->setData($array);

        return $datatype_table;
    }

    /**
    * @param integer $datatype_id
    * @return Gc\Datatype\Model
    */
    static function fromId($datatype_id)
    {
        $datatype_table = new Model();
        $row = $datatype_table->select(array('id' => $datatype_id));
        if(!empty($row))
        {
            return $datatype_table->setData((array)$row->current());
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
                $this->insert($array_save);
                $this->setId($this->getLastInsertId());
            }
            else
            {
                $this->update($array_save, sprintf('id = %d', $this->getId()));
            }

            return $this->getId();
        }
        catch (Exception $e)
        {
            /**
            * TODO(Make \Gc\Error)
            */
            \Gc\Error::set(get_class($this),$e);
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
    * @see include/Gc/Interface/Gc\Component\IterableInterface#getId()
    */
    public function getId()
    {
        return $this->getData('id');
    }

    /* (non-PHPdoc)
    * @see include/Gc/Interface/Gc\Component\IterableInterface#getName()
    */
    public function getName()
    {
        return $this->getData('name');
    }

    /* (non-PHPdoc)
    * @see include/Gc/Interface/Gc\Component\IterableInterface#getParent()
    */
    public function getParent()
    {
        return FALSE;
    }

    /* (non-PHPdoc)
    * @see include/Gc/Interface/Gc\Component\IterableInterface#getChildren()
    */
    public function getChildren()
    {
        return FALSE;
    }

    /* (non-PHPdoc)
    * @see include/Gc/Interface/Gc\Component\IterableInterface#getIterableId()
    */
    public function getIterableId()
    {
        return 'datatype_'.$this->getId();
    }

    /* (non-PHPdoc)
    * @see include/Gc/Interface/Gc\Component\IterableInterface#getUrl()
    */
    public function getUrl()
    {
        return '';
    }

    /* (non-PHPdoc)
    * @see include/Gc/Interface/Gc\Component\IterableInterface#getIcon()
    */
    public function getIcon()
    {
        return 'file';
    }

    /**
    *
    * @param Gc\Datatype\AbstractDatatype $datatype_model
    *
    * @return Model
    */
    static function savePrevalueEditor(AbstractDatatype $datatype)
    {
        $datatype->getPrevalueEditor()->save();
        return $datatype->getConfig();
    }

    /**
    *
    * @param Gc\Property\Model $property
    *
    * @return mixte
    */
    static function saveEditor(\Gc\Property\Model $property)
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
    * @param Gc\Datatype\AbstractDatatype $datatype
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
    * @param Gc\Document\Model $document
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
    * @return Gc\Datatype\AbstractDatatype
    */
    static function loadDatatype($datatype_id, $document_id = NULL)
    {
        $datatype = Model::fromId($datatype_id);
        $class = 'Datatypes\\'.$datatype->getModel().'\Datatype';

        $object = new $class();
        $object->load($datatype, $document_id);
        return $object;
    }
}
