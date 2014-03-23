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
use Gc\Layout;
use Gc\Script;
use Gc\Tab;
use Gc\Property;
use Gc\View;
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
     *
     * @return void
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
     * Create xml from type
     *
     * @param string  $type     Type of element
     * @param integer $parentId Parent id
     *
     * @return string
     */
    protected function createXml($type, $parentId = 0)
    {
        $xml = '';
        switch ($type) {
            case 'datatype':
                $datatypes = new Datatype\Collection();
                $array     = $datatypes->getDatatypes();
                if (empty($array)) {
                    continue;
                }

                $xml .= $datatypes->toXml($array, 'datatypes');
                break;
            case 'view':
                $views = new View\Collection();
                $array = $views->getViews();
                if (empty($array)) {
                    continue;
                }

                $xml .= $views->toXml($array, 'views');
                break;
            case 'layout':
                $layouts = new Layout\Collection();
                $array   = $layouts->getLayouts();
                if (empty($array)) {
                    continue;
                }

                $xml .= $layouts->toXml($array, 'layouts');
                break;
            case 'script':
                $scripts = new Script\Collection();
                $array   = $scripts->getScripts();
                if (empty($array)) {
                    continue;
                }

                $xml .= $scripts->toXml($array, 'scripts');
                break;
            case 'document-type':
                $documentTypesCollection = new DocumentType\Collection();
                $array                   = $documentTypesCollection->getDocumentTypes();
                if (empty($array)) {
                    continue;
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

                $xml .= $documentTypesCollection->toXml($documentTypesCollection->getDocumentTypes(), 'document_types');
                break;
            case 'document':
                $documents = new Document\Collection();
                $documents->load($parentId);
                $array = $documents->getDocuments();
                if (empty($array)) {
                    continue;
                }

                $propertiyCollection = new Property\Collection();
                foreach ($documents->getDocuments() as $document) {
                    $array      = array();
                    $properties = $propertiyCollection->load(
                        null,
                        null,
                        $document->getId()
                    )->getProperties();
                    foreach ($properties as $property) {
                        $array[] = $property->getValueModel();
                    }

                    $document->setProperties($array);
                    if ($document->getChildren()) {
                        $xml .= $this->createXml('document', $document->getId());
                    }
                }

                $xml .= $documents->toXml($documents->getDocuments(), 'documents');
                break;
        }

        return $xml;
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
            $xml = simplexml_load_string(
                $content,
                'SimpleXmlElement',
                LIBXML_NOERROR + LIBXML_ERR_FATAL + LIBXML_ERR_NONE
            );
            if (false == $xml) {
                throw new Exception('Can\'t parse Xml');
            }

        } catch (Exception $e) {
            return false;
        }

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

        ksort($orders);

        $errors     = array();
        $translator = $this->serviceLocator->get('MvcTranslator');
        $ids        = array(
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
                                'name'           => empty($name) ? $model->getName() : $name,
                                'model'          => empty($datatypeModel) ? $model->getModel() : $datatypeModel,
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
                                $translator->translate(
                                    'Cannot save datatype with id (%d)'
                                ),
                                $id
                            );
                        }
                    }
                    break;
                case 'document_types':
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
                                $translator->translate(
                                    'Cannot save document type with id (%d)'
                                ),
                                $id
                            );
                        }
                    }
                    break;
                case 'documents':
                    foreach ($children['children'] as $child) {
                        $urlKey = (string) $child->url_key;
                        $model  = Document\Model::fromUrlKey($urlKey);
                        if (empty($model)) {
                            $attributes = $child->attributes();
                            $id         = (integer) $attributes['id'];
                            $model      = Document\Model::fromId($id);
                            if (empty($model)) {
                                $model = new Document\Model();
                            }
                        } else {
                            $id = $model->getId();
                        }

                        $documentTypeId = isset($ids['document_types'][(integer) $child->document_type_id]) ?
                            $ids['document_types'][(integer) $child->document_type_id] :
                            $model->getDocumentTypeId();
                        $viewId         = isset($ids['views'][(integer) $child->view_id]) ?
                            $ids['views'][(integer) $child->view_id] :
                            $model->getView();
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
                                    $valueModel->setValue((string) $value->value);
                                    $valueModel->save();
                                }
                            }
                        } catch (Exception $e) {
                            $errors[] = sprintf(
                                $translator->translate(
                                    'Cannot save document with id (%d)'
                                ),
                                $id
                            );
                        }
                    }
                    break;
                case 'views':
                case 'layouts':
                case 'scripts':
                default:
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
                                $translator->translate(
                                    'Cannot save %s with identifier (%s) or id (%d)'
                                ),
                                $type,
                                $identifier,
                                $id
                            );
                        }
                    }
                    break;
            }
        }

        //must insert dependencies at the end
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
                        $translator->translate(
                            'Cannot save dependencies for document type with id (%d)'
                        ),
                        empty($documentTypeId) ? 0 : $documentTypeId
                    );
                }
            }
        }

        if (!empty($errors)) {
            return $errors;
        }

        return true;
    }
}
