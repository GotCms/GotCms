<?php
//@TODO
class Gc_Media_Icon_Model implements Gc_Interface_Iterable {

    private $_icon_id;
    private $_icon_name;
    private $_icon_url;

    /**
    * @param unknown_type $icon_id
    * @return unknown_type
    */
    public function __construct($_icon_id = -1) {
        $this->setId($_icon_id);
    }

    /**
    * @param array $icon
    * @return Gc_Media_Icon_Model
    */
    static function fromArray($icon = array()) {
        $i = new Gc_Media_Icon_Model($icon['icon_id']);
        $i->setName($icon['icon_name']);
        $i->setIconUrl($icon['icon_url']);
        return $i;
    }


    /**
    * @param integer $icon_id
    * @return Gc_Layout_Model
    */
    static function fromId($icon_id){
        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_Db::FETCH_ASSOC);
        $select = $db->select();
        $select->from(array('i'=>'icons'));
        $select->where('icon_id = ?', $icon_id);
        $icon = $db->query($select)->fetchAll();
        if(count($icon) > 0) {
            return self::fromArray($icon[0]);
        } else {
            return null;
        }
    }
    /**
    * @param unknown_type $icon_id
    * @return unknown_type
    */
    private function setId($icon_id) {
        $this->_icon_id = (int)$icon_id;
    }
    /**
    * @param unknown_type $icon_name
    * @return unknown_type
    */
    public function setName($icon_name) {
        $this->_icon_name = $icon_name;
    }
    /**
    * @param unknown_type $icon_alias
    * @return unknown_type
    */
    public function setIconUrl($icon_url) {
        $this->_icon_url = $icon_url;
    }
    /**
    * @return unknown_type
    */
    public function getIconUrl() {
        return $this->_icon_url;
    }
    /**
    * @return unknown_type
    */
    public function save(){
        $db = Zend_Registry::get('db');
        $array_save = array('icon_name'=>$this->getName(),
                            'icon_url'=>$this->getIconUrl()
                            );

        try {
            if($this->getId() == -1){
                $db->insert('icons', $array_save);
                $this->setId($db->lastInsertId('icons','icon_id'));
            }
            else{
                $db->update('icons', $array_save, 'icon_id = '.$this->getId());
            }
            return true;
        } catch (Exception $e){
            /**
            * TODO(Make \Gc\Error)
            */
            \Gc\Error::set(get_class($this), $e);
        }
        return false;
    }
    public function delete() {
        $db = Zend_Registry::get('db');
        if(!empty($this->_icon_id)) {
            if($db->delete('icons','icon_id = '.$this->getId())) {
                unset($this);
                return true;
            }
        }
        return false;
    }
    /*
    * Gc_Interface Methods
    */
    /* (non-PHPdoc)
    * @see include/Gc/Interface/Gc\Component\IterableInterface#getParent()
    */
    public function getParent() {
        return null;
    }
    /* (non-PHPdoc)
    * @see include/Gc/Interface/Gc\Component\IterableInterface#getChildren()
    */
    public function getChildren() {
        return null;
    }
    /* (non-PHPdoc)
    * @see include/Gc/Interface/Gc\Component\IterableInterface#getId()
    */
    public function getId() {
        return $this->_icon_id;
    }
    /* (non-PHPdoc)
    * @see include/Gc/Interface/Gc\Component\IterableInterface#getIterableId()
    */
    public function getIterableId() {
        return 'icon_'.$this->getId();
    }
    /* (non-PHPdoc)
    * @see include/Gc/Interface/Gc\Component\IterableInterface#getName()
    */
    public function getName() {
        return $this->_icon_name;
    }
    /* (non-PHPdoc)
    * @see include/Gc/Interface/Gc\Component\IterableInterface#getUrl()
    */
    public function getUrl() {
        return 'javascript:loadController(\''.Zend_Controller_Action_HelperBroker::getStaticHelper('url')->url(array('controller'=>'media','action'=>'edit')).'/type/icon/id/'.$this->getId().'\')';
    }
    /* (non-PHPdoc)
    * @see library/Es/Interface/Gc_Interface_Iterable#getIcon()
    */
    public function getIcon() {
        return 'file';
    }
}
