<?php
/**
 * This source file is part of GotCms.
 *
 * GotCms is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * GotCms is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License along
 * with GotCms. If not, see <http://www.gnu.org/licenses/lgpl-3.0.html>.
 *
 * PHP Version >=5.3
 *
 * @category   Gc_Library
 * @package    Modules
 * @subpackage Blog\Model
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Backup\Model;

use Gc\Core\Object;
use Gc\Document;
use Gc\Datatype;
use Gc\DocumentType;
use Gc\Tab;
use Gc\Property;
use Exception;
use Zend\ServiceManager\ServiceManager;

/**
 * Blog comment table
 *
 * @category   Gc_Library
 * @package    Modules
 * @subpackage Blog\Model
 */
class Content extends Object
{
    /**
     * Service manager
     *
     * @var ServiceManager
     */
    protected $serviceLocator;

    /**
     * Initialize serviceManager
     *
     * @param ServiceManager $serviceLocator Service Manager
     */
    public function __construct(ServiceManager $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * Export function
     *
     * @param array $what What will be exported
     *
     * @return string
     */
    public function export(array $what)
    {
        if (empty($what)) {
            return '';
        }

        $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xml .= '<gotcms>';
        foreach ($what as $type) {
            $xml .= $this->createXml($type);
        }

        $xml .= '</gotcms>';

        return $xml;
    }

    /**
     * Load resource data
     *
     * @param string $type Type of the resource
     *
     * @return string
     */
    public function createResource($type)
    {
        $name      = ucfirst($type);
        $className = sprintf('Gc\\%s\\Collection', $name);
        $method    = sprintf('get%ss', $name);

        $class = new $className();
        $array = $class->$method();
        if (empty($array)) {
            return '';
        }

        return $class->toXml($array, $type . 's');
    }

    /**
     * Load Document type data
     *
     * @return string
     */
    public function createDocumentType()
    {
        $documentTypesCollection = new DocumentType\Collection();
        $array                   = $documentTypesCollection->getDocumentTypes();
        if (empty($array)) {
            return '';
        }

        foreach ($documentTypesCollection->getDocumentTypes() as $documentType) {
            //Preload dependencies
            $children     = array();
            $dependencies = $documentType->getDependencies();
            foreach ($dependencies as $dependency) {
                $children[] = array('id' => $dependency);
            }

            $documentType->setData('dependencies', $children);

            //Preload available views
            $children       = array();
            $availableViews = $documentType->getAvailableViews()->getViews();
            foreach ($availableViews as $view) {
                $children[] = array('id' => $view->getId());
            }

            $documentType->setData('available_views', $children);

            foreach ($documentType->getTabs() as $tab) {
                //Preload Tabs
                foreach ($tab->getProperties() as $property) {
                    //Preload Properties
                }
            }
        }

        return $documentTypesCollection->toXml($documentTypesCollection->getDocumentTypes(), 'document_types');
    }

    /**
     * Load Document data
     *
     * @return string
     */
    public function createDocument()
    {
        $documents = new Document\Collection();
        $rows      = $documents->fetchAll(
            $documents->select(
                function ($select) {
                    $select->order('sort_order ASC');
                    $select->order('created_at ASC');
                }
            )
        );

        if (empty($rows)) {
            return '';
        }

        $documentArray = array();
        foreach ($rows as $row) {
            $documentArray[] = Document\Model::fromArray((array) $row);
        }

        $propertiyCollection = new Property\Collection();
        foreach ($documentArray as $document) {
            $array      = array();
            $properties = $propertiyCollection->load(
                null,
                null,
                $document->getId()
            )->getProperties();
            foreach ($properties as $property) {
                $value = $property->getValueModel()->getValue();
                $property->getValueModel()->setValue(base64_encode($value));
                $array[] = $property->getValueModel();
            }

            $document->setProperties($array);
        }

        return $documents->toXml($documentArray, 'documents');
    }

    /**
     * Create xml from type
     *
     * @param string $type Type of element
     *
     * @return string
     */
    protected function createXml($type)
    {
        $xml = '';
        switch ($type) {
            case 'datatype':
            case 'view':
            case 'layout':
            case 'script':
                $xml .= $this->createResource($type);
                break;
            case 'document-type':
                $xml .= $this->createDocumentType();
                break;
            case 'document':
                $xml .= $this->createDocument();
                break;
        }

        return $xml;
    }

    /**
     * Load xml content
     *
     * @param string $content Content
     *
     * @return \SimpleXMLElement
     */
    protected function loadXml($content)
    {
        $xml = simplexml_load_string(
            $content,
            'SimpleXmlElement',
            LIBXML_NOERROR + LIBXML_ERR_FATAL + LIBXML_ERR_NONE
        );

        if (false == $xml) {
            throw new Exception('Can\'t parse Xml');
        }

        return $xml;
    }

    /**
     * Get children orders
     *
     * @param \SimpleXMLElement $xml xml data
     *
     * @return array
     */
    protected function getOrders($xml)
    {
        $orders = array();
        foreach ($xml->children() as $children) {
            $data = array('name' => $children->getName(), 'children' => $children->children());
            switch ($children->getName()) {
                case 'datatypes':
                    $orders[0] = $data;
                    break;
                case 'views':
                    $orders[1] = $data;
                    break;
                case 'layouts':
                    $orders[2] = $data;
                    break;
                case 'scripts':
                    $orders[3] = $data;
                    break;
                case 'document_types':
                    $orders[4] = $data;
                    break;
                case 'documents':
                    $orders[5] = $data;
                    break;
            }
        }

        return $orders;
    }

    /**
     * Import Datatypes
     *
     * @param array &$ids     Ids
     * @param array &$errors  Errors
     * @param array $children Children list
     *
     * @return void
     */
    protected function importDatatypes(&$ids, &$errors, $children)
    {
        foreach ($children['children'] as $child) {
            $attributes = $child->attributes();
            $id         = (integer) $attributes['id'];
            $model      = Datatype\Model::fromId($id);

            if ($model === false) {
                $model = new Datatype\Model();
            }

            $name          = (string) $child->name;
            $datatypeModel = (string) $child->model;
            $model->addData(
                array(
                    'name'  => empty($name) ? $model->getName() : $name,
                    'model' => empty($datatypeModel) ? $model->getModel() : $datatypeModel,
                )
            );
            $model->setPrevalueValue((string) $child->prevalue_value);

            try {
                if (!empty($model)) {
                    $model->save();
                    $ids['datatypes'][$id] = $model->getId();
                }

            } catch (Exception $e) {
                $errors[] = sprintf(
                    $this->serviceLocator->get('MvcTranslator')->translate(
                        'Cannot save datatype with id (%d)'
                    ),
                    $id
                );
            }
        }
    }

    /**
     * Import Document types
     *
     * @param array &$ids     Ids
     * @param array &$errors  Errors
     * @param array $children Children list
     *
     * @return void
     */
    protected function importDocumentTypes(&$ids, &$errors, $children)
    {
        $documentTypes = array();
        foreach ($children['children'] as $child) {
            $attributes = $child->attributes();
            $id         = (integer) $attributes['id'];
            $model      = DocumentType\Model::fromId($id);
            if (empty($model)) {
                $model = new DocumentType\Model();
            }

            $viewid = isset($ids['views'][(integer) $child->default_view_id]) ?
                $ids['views'][(integer) $child->default_view_id] :
                (integer) $child->default_view_id;

            $name = (string) $child->name;
            $model->addData(
                array(
                    'name'            => empty($name) ? $model->getName() : $name,
                    'description'     => (string) $child->description,
                    'icon_id'         => (integer) $child->icon_id,
                    'default_view_id' => $viewid,
                )
            );

            if ($model->getUserId() === null) {
                $model->setUserId($this->serviceLocator->get('Auth')->getIdentity()->getId());
            }

            try {
                if (!empty($model)) {
                    $model->save();

                    $tabs = (array) $child->tabs;
                    if (isset($tabs['tab']) and is_array($tabs['tab'])) {
                        $tabs = $tabs['tab'];
                    }

                    foreach ($tabs as $tab) {
                        $tabAttributes = $tab->attributes();
                        $tabId         = (integer) $tabAttributes['id'];
                        $tabModel      = Tab\Model::fromId($tabId);
                        if (empty($tabModel)) {
                            $tabModel = new Tab\Model();
                        }

                        $tabModel->addData(
                            array(
                                'name' => (string) $tab->name,
                                'description' => (string) $tab->description,
                                'sort_order' => (integer) $tab->sort_order,
                                'document_type_id' => $model->getId(),
                            )
                        );

                        $tabModel->save();
                        $properties = (array) $tab->properties;
                        if (isset($properties['property']) and is_array($properties['property'])) {
                            $properties = $properties['property'];
                        }

                        foreach ($properties as $property) {
                            $propAttributes = $property->attributes();
                            $propertyId     = (integer) $propAttributes['id'];
                            $propertyModel  = Property\Model::fromId($propertyId);
                            if (empty($propertyModel)) {
                                $propertyModel = new Property\Model();
                            }

                            $datatypeId = isset($ids['datatypes'][(integer) $property->datatype_id]) ?
                                $ids['datatypes'][(integer) $property->datatype_id] :
                                (string) $property->datatype_id;
                            $propertyModel->addData(
                                array(
                                    'name'        => (string) $property->name,
                                    'description' => (string) $property->description,
                                    'identifier'  => (string) $property->identifier,
                                    'sort_order'  => (integer) $property->sort_order,
                                    'tab_id'      => $tabModel->getId(),
                                    'datatype_id' => $datatypeId,
                                )
                            );

                            $propertyModel->save();
                            $ids['properties'][$propertyId] = $propertyModel->getId();
                        }
                    }

                    $ids['document_types'][$id] = $model->getId();
                    $documentTypes[]            = array(
                        'model'        => $model,
                        'dependencies' => (array) $child->dependencies,
                        'views'        => (array) $child->available_views,
                    );
                }
            } catch (Exception $e) {
                $errors[] = sprintf(
                    $this->serviceLocator->get('MvcTranslator')->translate(
                        'Cannot save document type with id (%d)'
                    ),
                    $id
                );
            }
        }

        return $documentTypes;
    }

    /**
     * Import Documents
     *
     * @param array &$ids     Ids
     * @param array &$errors  Errors
     * @param array $children Children list
     *
     * @return void
     */
    protected function importDocuments(&$ids, &$errors, $children)
    {
        foreach ($children['children'] as $child) {
            $urlKey     = (string) $child->url_key;
            $model      = Document\Model::fromUrlKey($urlKey);
            $attributes = $child->attributes();
            $id         = (integer) $attributes['id'];
            if (empty($model)) {
                $model = Document\Model::fromId($id);
                if (empty($model)) {
                    $model = new Document\Model();
                }
            }

            $documentTypeId = isset($ids['document_types'][(integer) $child->document_type_id]) ?
                $ids['document_types'][(integer) $child->document_type_id] :
                $model->getDocumentTypeId();
            $viewId         = isset($ids['views'][(integer) $child->view_id]) ?
                $ids['views'][(integer) $child->view_id] :
                $model->getViewId();
            $layoutId       = isset($ids['layouts'][(integer) $child->layout_id]) ?
                $ids['layouts'][(integer) $child->layout_id] :
                $model->getLayoutId();
            $parentId       = isset($ids['layouts'][(integer) $child->parent_id]) ?
                $ids['layouts'][(integer) $child->parent_id] :
                $model->getParentId();
            $name           = (string) $child->name;
            $status         = (string) $child->status;
            $userId         = (integer) $child->user_id;
            $sortOrder      = (integer) $child->sort_order;
            $showInNav      = (integer) $child->show_in_nav;
            $model->addData(
                array(
                    'name'             => empty($name) ? $model->getName() : $name,
                    'url_key'          => $urlKey,
                    'status'           => empty($status) ? $model->getStatus() : $status,
                    'show_in_nav'      => empty($showInNav) ? $model->getShowInNav() : $showInNav,
                    'sort_order'       => empty($sortOrder) ? $model->getSortOrder() : $sortOrder,
                    'icon_id'          => (integer) $child->icon_id,
                    'view_id'          => $viewId,
                    'parent_id'        => $parentId,
                    'user_id'          => $userId,
                    'layout_id'        => $layoutId,
                    'document_type_id' => empty($documentTypeId) ?
                    $model->getDocumentTypeId() :
                    $documentTypeId,
                )
            );

            if ($model->getUserId() === null) {
                $model->setUserId($this->serviceLocator->get('Auth')->getIdentity()->getId());
            }

            try {
                if (!empty($model)) {
                    $model->save();
                    $ids['documents'][$id] = $model->getId();

                    $values = (array) $child->properties;
                    if (isset($values['property_value']) and is_array($values['property_value'])) {
                        $values = $values['property_value'];
                    }

                    foreach ($values as $value) {
                        $documentId = (integer) $value->document_id;
                        $propertyId = (integer) $value->property_id;
                        $valueModel = new Property\Value\Model();
                        $valueModel->load(
                            null,
                            isset($ids['documents'][$documentId]) ?
                            $ids['documents'][$documentId] : $documentId,
                            isset($ids['properties'][$propertyId]) ?
                            $ids['properties'][$propertyId] : $propertyId
                        );

                        $valueModel->setValue((string) base64_decode($value->value));
                        $valueModel->save();
                    }
                }
            } catch (Exception $e) {
                $errors[] = sprintf(
                    $this->serviceLocator->get('MvcTranslator')->translate(
                        'Cannot save document with id (%d)'
                    ),
                    $id
                );
            }
        }
    }

    /**
     * Import templates
     *
     * @param array &$ids     Ids
     * @param array &$errors  Errors
     * @param array $children Children list
     *
     * @return void
     */
    protected function importTemplates(&$ids, &$errors, $children)
    {
        $type  = ucfirst(substr($children['name'], 0, -1));
        $class = 'Gc\\' . $type . '\\Model';

        foreach ($children['children'] as $child) {
            $model      = $class::fromIdentifier((string) $child->identifier);
            $attributes = $child->attributes();
            $id         = (integer) $attributes['id'];
            if (empty($model)) {
                $model = $class::fromId($id);
                if (empty($model)) {
                    $model = new $class();
                }
            }

            $identifier = (string) $child->identifier;
            $name       = (string) $child->name;
            $model->addData(
                array(
                    'name'        => empty($name) ? null : $name,
                    'identifier'  => empty($identifier) ? null : $identifier,
                    'description' => (string) $child->description,
                    'content'     => (string) $child->content,
                )
            );


            try {
                if (!empty($model)) {
                    $model->save();
                    $ids[$children['name']][$id] = $model->getId();
                }
            } catch (Exception $e) {
                $errors[] = sprintf(
                    $this->serviceLocator->get('MvcTranslator')->translate(
                        'Cannot save %s with identifier (%s) or id (%d)'
                    ),
                    $type,
                    $identifier,
                    $id
                );
            }
        }
    }

    /**
     * Import from xml
     *
     * @param string $content Xml Content
     *
     * @return boolean
     */
    public function import($content)
    {
        try {
            $xml = $this->loadXml($content);
        } catch (Exception $e) {
            return false;
        }

        $orders = $this->getOrders($xml);
        ksort($orders);
        $documentTypes = array();

        $errors = array();
        $ids    = array(
            'datatypes'      => array(),
            'views'          => array(),
            'layouts'        => array(),
            'scripts'        => array(),
            'document_types' => array(),
            'documents'      => array(),
            'properties'     => array(),
        );

        foreach ($orders as $children) {
            switch ($children['name']) {
                case 'datatypes':
                    $this->importDatatypes($ids, $errors, $children);
                    break;
                case 'document_types':
                    $documentTypes = $this->importDocumentTypes($ids, $errors, $children);
                    break;
                case 'documents':
                    $this->importDocuments($ids, $errors, $children);
                    break;
                case 'views':
                case 'layouts':
                case 'scripts':
                default:
                    $this->importTemplates($ids, $errors, $children);
                    break;
            }
        }

        //must insert dependencies at the end
        $this->insertDependencies($ids, $errors, $documentTypes);

        if (!empty($errors)) {
            return $errors;
        }

        return true;
    }

    /**
     * Insert dependencies
     *
     * @param array &$ids          Ids
     * @param array &$errors       Errors
     * @param array $documentTypes List of document type
     *
     * @return void
     */
    protected function insertDependencies(&$ids, &$errors, $documentTypes)
    {
        if (!empty($documentTypes)) {
            foreach ($documentTypes as $documentType) {
                $model              = $documentType['model'];
                $availableViews     = $documentType['views'];
                $dependencies       = $documentType['dependencies'];
                $dependenciesValues = array();
                if (isset($dependencies['id']) and is_array($dependencies['id'])) {
                    $dependencies = $dependencies['id'];
                }

                foreach ($dependencies as $dependency) {
                    $documentTypeId = isset($ids['document_types'][(integer) $dependency]) ?
                        $ids['document_types'][(integer) $dependency] :
                        (integer) $dependency;
                    if (empty($documentTypeId)) {
                        continue;
                    }

                    $dependenciesValues[] = $documentTypeId;
                }

                $model->setDependencies($dependenciesValues);

                foreach ($availableViews as $view) {
                    $viewId = isset($ids['views'][(integer) $view]) ?
                        $ids['views'][(integer) $view] :
                        (integer) $view;
                    if (empty($viewId)) {
                        continue;
                    }

                    $model->addView($viewId);
                }

                try {
                    $model->save();
                } catch (Exception $e) {
                    $errors[] = sprintf(
                        $this->serviceLocator->get('MvcTranslator')->translate(
                            'Cannot save dependencies for document type with id (%d)'
                        ),
                        empty($documentTypeId) ? 0 : $documentTypeId
                    );
                }
            }
        }
    }
}
