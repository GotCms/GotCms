<?php
class Es_Datatype_Upload extends Es_Core_Object {
    protected $_property, $_datatype, $_request, $_document_id, $_file, $_name;

    /**
    * @param integer $property_id
    * @param $_FILES $file
    * @param string $name
    */
    public function __construct($property_id, $document_id, $request, $name) {
        $this->_document_id = $document_id;
        if($this->getProperty($property_id) === false) {
            return false;
        }
        if($request === null) {
            return false;
        }
        $this->_name = $name;
        $this->_request = $request;
        $this->getDatatype();
        $this->_params = $this->getDatatype()->getValue();
    }

    /**
    * @param unknown_type $file
    * @return boolean
    */
    public function uploadFile() {
        if($this->_request->isPost()) {
            $file = new Zend_File_Transfer_Adapter_Http();
            $dir = '../medias/'.$this->_document_id.'/';
            if(!is_dir($dir)) mkdir($dir);
            $dir .= $this->getDatatype()->getId().'/';
            if(!is_dir($dir)) mkdir($dir);
            $file->setDestination($dir);
            $fileName = $file->getFileName(null, false);
            $extension = substr($fileName,strpos($fileName, '.'), strlen($fileName) -1);
            $file->addFilter('Rename', array('target' => $file->getDestination().'/'.$this->getProperty()->getId().'-'.$this->_name.$extension, 'overwrite' => true));
            if($file->receive()) {
                $parameters = $this->_datatype->getValue();
                $const = defined('FILEINFO_MIME_TYPE') ? FILEINFO_MIME_TYPE : FILEINFO_MIME;
                $finfo = finfo_open($const); // return mimetype extension
                if(!in_array(finfo_file($finfo, $file->getFileName(null, true)), $parameters['mime_list'])){
                    unlink($file->getFileName(null, true));
                    $value = '';
                } else {
                    $this->_file = str_replace('../', '/', $file->getFileName(null , true));
                    return true;
                }
                finfo_close($finfo);
            }
        }
        return false;
    }

    /**
    * @param integer $property_id
    * @return Es_Component_Property_Model
    */
    protected function getProperty($property_id = null) {
        if($this->_property === null) {
            $this->_property = Es_Component_Property_Model::fromId($property_id);
            if($this->_property === null) {
                return false;
            }
        }
        return $this->_property;
    }

    /**
    * @return Es_Datatype_Model
    */
    protected function getDatatype() {
        if($this->_datatype === null) {
            $datatype_id = $this->_property->getDatatypeId();
            $this->_datatype = Es_Datatype_Model::fromId($datatype_id);
        }
        return $this->_datatype;
    }

    public function getFile() {
        return $this->_file;
    }

}
