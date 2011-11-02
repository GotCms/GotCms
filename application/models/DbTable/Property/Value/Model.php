<?php
class Es_Model_DbTable_Property_Value_Model extends Es_Core_Object
{
    public function __construct($property_value_id = null, $document_id = null, $property_id = null) {
        $this->setPropertyValueId($property_value_id);
        $this->setDocumentId($document_id);
        $this->setPropertyId($property_id);
    }

    /**
    * @param array $array
    * @return Es_Component_Property_Model
    */
    static function fromArray(Array $array){
        if(!empty($array['property_value_id']) && !empty($array['document_id']) && !empty($array['property_id'])) {
            $pv = new Es_Component_Property_Value_Model($array['property_value_id']);
            $pv->setDocumentId($array['document_id']);
            $pv->setPropertyId($array['property_id']);
            $pv->setValue($array['value']);
        }else {
            $pv = null;
        }
        return $pv;
    }

    /**
    * @param integer $property_id
    * @return Es_Component_Property_Model
    */
    static function fromId($property_value_id){
        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_Db::FETCH_ASSOC);
        $select = $db->select();
        $select->from(array('t'=>'properties_value'));
        $select->where('property_value_id = ?', (int)$property_value_id);
        $property = $db->query($select)->fetchAll();
        if(count($property) > 0) {
            return self::fromArray($property[0]);
        } else {
            return null;
        }
    }
    public function save() {
        $db = $this->getResource();
        $arraySave = array('value'=>$this->getvalue(),
                            'document_id'=>$this->getDocumentId(),
                            'property_id'=>$this->getpropertyId()
                            );
        try {
            if($this->getPropertyValueId() === NULL ){
                $db->insert('properties_value', $arraySave);
                $this->setPropertyValueId($db->lastInsertId('properties_value','property_value_id'));
            }
            else{
                $db->update('properties_value', $arraySave, 'property_value_id = '.$this->getPropertyValueId());
            }
            return true;
        } catch (Exception $e){
            /**
            * TODO(Make Es_Error)
            */
            Es_Error::set(get_class($this),$e);
        }
        return false;
    }
}
