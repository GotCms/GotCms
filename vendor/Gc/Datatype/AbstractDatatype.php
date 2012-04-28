<?php

namespace Gc\Datatype;

use Gc\Db\AbstractTable,
    Gc\Property;

abstract class AbstractDatatype extends AbstractTable
{
    protected     $_editor;
    protected     $_prevalueEditor;
    protected     $_property;
    protected     $_document_id;
    protected     $_helper;
    protected     $_loaders = array();
    protected     $_loaderTypes = array('filter', 'helper');
    protected     $_name = 'datatypes';

    /**
    * @param Gc\Component\Property\Model $property
    * @return Gc\Model\DbTable\Datatype\Abstract\Editor
    */
    abstract public function getEditor(Property\Model $property);

    /**
    * @return Gc\Model\DbTable\Datatype\Abstract\PrevalueEditor
    */
    abstract public function getPrevalueEditor();

    public function load($datatype = NULL, $document_id = NULL)
    {
        if(empty($datatype))
        {
            return FALSE;
        }

        $this->setData('datatype', $datatype);
        $this->setData('document_id', $document_id);
    }

    public function getConfig()
    {
        return $this->getDatatype()->getData('prevalue_value');
    }

    public function setConfig($value)
    {
        $this->getDatatype()->setData('prevalue_value', $value);
        return $this;
    }

    public function getUploadUrl()
    {
        $baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
        return $baseUrl.'/media/upload/document/'.$this->_document_id;
    }

    /**
    * Get a helper by name
    *
    * @param  string $name
    * @return object
    */
    public function getHelper($name)
    {
        return $this->_getPlugin('helper', $name);
    }

    /**
    * Retrieve a plugin object
    *
    * @param  string $type
    * @param  string $name
    * @return object
    */
    private function _getPlugin($type = 'helper', $name)
    {
        $name = ucfirst($name);
        switch ($type)
        {
            case 'helper':
                $storeVar = '_helper';
                $store    = $this->_helper;
                break;
        }

        if (!isset($store[$name]))
        {
            $class = $this->getPluginLoader($type)->load($name);
            $store[$name] = new $class();
        }

        return $store[$name];
    }

    /**
    * Retrieve plugin loader for a specific plugin type
    *
    * @param  string $type
    * @return Zend_Loader_PluginLoader
    */
    public function getPluginLoader($type)
    {
        $type = strtolower($type);
        if (!in_array($type, $this->_loaderTypes))
        {
            require_once 'Zend/View/Exception.php';
            $e = new Zend_View_Exception(sprintf('Invalid plugin loader type "%s"; cannot retrieve', $type));
            $e->setView($this);
            throw $e;
        }

        if (!array_key_exists($type, $this->_loaders))
        {
            $prefix     = 'Zend_View_';
            $pathPrefix = 'Zend/View/';

            $pType = ucfirst($type);
            switch ($type)
            {
                case 'filter':
                case 'helper':
                default:
                    $prefix     .= $pType;
                    $pathPrefix .= $pType;
                    $loader = new Zend_Loader_PluginLoader(array(
                        $prefix => $pathPrefix
                    ));
                    $loader->addPrefixPath($prefix, './app/views/helpers');

                    $this->_loaders[$type] = $loader;
                    break;
            }
        }

        return $this->_loaders[$type];
    }

    public function getProperty()
    {
        return $this->_property;
    }

    public function getName()
    {
        return $this->_name;
    }
}
