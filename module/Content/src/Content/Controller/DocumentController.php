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
 * @category   Gc_Application
 * @package    Content
 * @subpackage Controller
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Content\Controller;

use Gc\Mvc\Controller\Action;
use Gc\Datatype;
use Gc\Document\Collection as DocumentCollection;
use Gc\Document\Model as DocumentModel;
use Gc\DocumentType;
use Gc\Property;
use Gc\Form\AbstractForm;
use Content\Form;
use Gc\Component;
use Zend\Form as ZendForm;
use Zend\Json\Json;

/**
 * Document controller
 *
 * @category   Gc_Application
 * @package    Content
 * @subpackage Controller
 */
class DocumentController extends Action
{
    /**
     * Contains information about acl
     *
     * @var array $_aclPage
     */
    protected $aclPage = array('resource' => 'Content', 'permission' => 'document');

    /**
     * Initialize Document Controller
     *
     * @return void
     */
    public function init()
    {
        $documents = new DocumentCollection();
        $documents->load(0);

        $this->layout()->setVariable('treeview', Component\TreeView::render(array($documents)));

        $routes = array(
            'edit' => 'documentEdit',
            'new' => 'documentCreate',
            'delete' => 'documentDelete',
            'copy' => 'documentCopy',
            'cut' => 'documentCut',
            'paste' => 'documentPaste',
            'refresh' => 'documentRefreshTreeview',
        );

        $array_routes = array();
        foreach ($routes as $key => $route) {
            $array_routes[$key] = $this->url()->fromRoute($route, array('id' => 'itemId'));
        }

        $this->layout()->setVariable('routes', Json::encode($array_routes));
    }

    /**
     * Create document
     *
     * @return \Zend\View\Model\ViewModel|array
     */
    public function createAction()
    {
        $document_form = new Form\Document();
        $document_form->setAttribute('action', $this->url()->fromRoute('documentCreate'));
        $parent_id = $this->getRouteMatch()->getParam('id');

        $document_type_collection = new DocumentType\Collection();
        $document_type_element    = $document_form->get('document_type');
        if (empty($parent_id)) {
            $document_type_element->setValueOptions(
                array('' => 'Select document type') + $document_type_collection->getSelect()
            );
        } else {
            $document_form->get('parent')->setValue($parent_id);
            $document_type_collection->init(DocumentModel::fromId($parent_id)->getDocumentTypeId());
            $document_type_element->setValueOptions(
                array('' => 'Select document type') + $document_type_collection->getSelect()
            );
        }

        if ($this->getRequest()->isPost()) {
            $document_form->getInputFilter()->add(
                array(
                    'required' => true,
                    'validators' => array(
                        array(
                            'name' => 'not_empty'
                        ),
                    ),
                ),
                'document_type'
            );
            $document_form->setData($this->getRequest()->getPost()->toArray());
            if (!$document_form->isValid()) {
                $this->flashMessenger()->addErrorMessage('Invalid document data');
                $this->useFlashMessenger();
            } else {
                $document_name    = $document_form->getValue('document-name');
                $document_url_key = $document_form->getValue('document-url_key');
                $document_type_id = $document_form->getValue('document_type');
                $parent_id        = $document_form->getValue('parent');
                $document         = new DocumentModel();
                $document->setName($document_name)
                    ->setDocumentTypeId($document_type_id)
                    ->setParentId($parent_id)
                    ->setUrlKey(!empty($document_url_key) ? $document_url_key : $this->checkUrlKey($document_name))
                    ->setUserId($this->getAuth()->getIdentity()->getId());

                $document_id = $document->save();
                if (empty($document_id)) {
                    $this->flashMessenger()->addErrorMessage('Can not add document');
                } else {
                    $this->flashMessenger()->addSuccessMessage('Document successfuly add');
                    $this->redirect()->toRoute('documentEdit', array('id' => $document_id));
                }
            }
        }

        return array('form' => $document_form);
    }

    /**
     * Delete document
     *
     * @return \Zend\View\Model\ViewModel|array
     */
    public function deleteAction()
    {
        $document = DocumentModel::fromId($this->getRouteMatch()->getParam('id', ''));
        if (empty($document)) {
            return $this->returnJson(array('success' => false, 'message' => 'Document does not exists!'));
        } else {
            try {
                if ($document->delete()) {
                    return $this->returnJson(
                        array(
                            'success' => true,
                            'message' => 'This document was succefully delete'
                        )
                    );
                }
            } catch (\Exception $e) {
                throw new \Gc\Exception($e->getMessage(), $e->getCode(), $e);
            }
        }

        return $this->returnJson(
            array(
                'success' => false
                , 'message' => 'There were problems during the removal of this document'
            )
        );
    }

    /**
     * Edit Document
     *
     * @return \Zend\View\Model\ViewModel|array
     */
    public function editAction()
    {
        $document_id = $this->getRouteMatch()->getParam('id');
        $document    = DocumentModel::fromId($document_id);
        if (empty($document)) {
            $this->flashMessenger()->addErrorMessage('Document does not exists !');
            return $this->redirect()->toRoute('content');
        } else {
            $document_form = new ZendForm\Form();
            $document_form->setAttribute(
                'action',
                $this->url()->fromRoute('documentEdit', array('id' => $document_id))
            );
            $this->layout()->setVariable('documentId', $document_id);
            $document_type_id = $document->getDocumentTypeId();
            $layout_id        = $this->getRouteMatch()->getParam('layout_id', '');

            if ($this->getRequest()->isPost()) {
                $has_error     = false;
                $document_vars = $this->getRequest()->getPost()->toArray();
                $old_url_key   = $document->getUrlKey();
                $document->setName(
                    empty($document_vars['document-name']) ?
                    $document->getName() :
                    $document_vars['document-name']
                );
                $document->setStatus(
                    empty($document_vars['document-status']) ?
                    DocumentModel::STATUS_DISABLE :
                    DocumentModel::STATUS_ENABLE
                );
                $document->showInNav(
                    empty($document_vars['document-show_in_nav']) ?
                    false :
                    $document_vars['document-show_in_nav']
                );
                $document->setLayoutId(
                    empty($document_vars['document-layout']) ?
                    false :
                    $document_vars['document-layout']
                );
                $document->setViewId(
                    empty($document_vars['document-view']) ?
                    $document->getViewId() :
                    $document_vars['document-view']
                );
                $document->setUrlKey(
                    empty($document_vars['document-url_key']) ?
                    '' :
                    $document_vars['document-url_key']
                );
            }

            $tabs       = $this->loadTabs($document_type_id);
            $tabs_array = array();
            $datatypes  = array();

            $idx = 1;
            foreach ($tabs as $tab) {
                $tabs_array[] = $tab->getName();
                $properties   = $this->loadProperties($document_type_id, $tab->getId(), $document->getId());

                $fieldset = new ZendForm\Fieldset('tabs-' . $idx);
                if ($this->getRequest()->isPost()) {
                    $connection = $document->getAdapter()->getDriver()->getConnection();
                    try {
                        $connection->beginTransaction();
                        foreach ($properties as $property) {
                            $property->setDocumentId($document->getId())->loadValue();
                            if (!Datatype\Model::saveEditor($property, $document)) {
                                $has_error = true;
                            }
                        }

                        if ($has_error) {
                            $connection->rollBack();
                        } else {
                            $connection->commit();
                        }
                    } catch (Exception $e) {
                        $connection->rollBack();
                    }
                }

                foreach ($properties as $property) {
                    AbstractForm::addContent($fieldset, Datatype\Model::loadEditor($property, $document));
                }

                $document_form->add($fieldset);
                $idx++;
            }

            $tabs_array[] = 'Document information';

            $form_document_add = new Form\Document();
            $form_document_add->load($document);
            $form_document_add->setAttribute('name', 'tabs-' . $idx);

            $document_form->add($form_document_add);

            if ($this->getRequest()->isPost()) {
                $form_document_add->setData($this->getRequest()->getPost()->toArray());

                if ($has_error or !$form_document_add->isValid()) {
                    $document->setStatus(DocumentModel::STATUS_DISABLE);
                    $document->setUrlKey($old_url_key);
                    $this->flashMessenger()->addErrorMessage(
                        'This document cannot be saved because one or more properties values are required !'
                    );
                    $this->useFlashMessenger();
                } else {
                    $this->flashMessenger()->addSuccessMessage('Document saved !');
                    $document->addData($form_document_add->getInputFilter()->getValues());
                    $document->save();

                    return $this->redirect()->toRoute('documentEdit', array('id' => $document_id));
                }
            }

            $tabs = new Component\Tabs($tabs_array);

            return array('form' => $document_form, 'tabs' => $tabs, 'document' => $document);
        }
    }

    /**
     * Copy document
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function copyAction()
    {
        $document_id = $this->getRouteMatch()->getParam('id');
        if (empty($document_id)) {
            return $this->returnJson(array('success' => false));
        }

        $session = $this->getSession();
        if (!empty($session['document-cut'])) {
            unset($session['document-cut']);
        }

        $session['document-copy'] = $document_id;
        return $this->returnJson(array('success' => true));
    }

    /**
     * Cut document
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function cutAction()
    {
        $document_id = $this->getRouteMatch()->getParam('id');
        if (empty($document_id)) {
            return $this->returnJson(array('success' => false));
        }

        $session = $this->getSession();
        if (!empty($session['document-copy'])) {
            unset($session['document-copy']);
        }

        $session['document-cut'] = $document_id;
        return $this->returnJson(array('success' => true));
    }

    /**
     * Paste document
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function pasteAction()
    {
        $parent_id = $this->getRouteMatch()->getParam('id', null);
        $session   = $this->getSession();
        if (!empty($parent_id)) {
            $parent_document = DocumentModel::fromId($parent_id);
            if (empty($parent_id)) {
                return $this->returnJson(array('success' => false));
            }
        }

        if (!empty($session['document-cut'])) {
            $document = DocumentModel::fromId($session['document-cut']);
            if (empty($document)) {
                return $this->returnJson(array('success' => false));
            }

            if (!empty($parent_document)) {
                $available_children = $parent_document->getDocumentType()->getDependencies();
                if (!in_array($document->getDocumentType()->getId(), $available_children)) {
                    return $this->returnJson(array('success' => false));
                }
            }

            $search_document = DocumentModel::fromUrlKey($document->getUrlKey(), $parent_id);
            if (!empty($search_document)) {
                return $this->returnJson(array('success' => false));
            }

            $document->setParentId($parent_id);
            $document->save();
            unset($session['document-cut']);
            return $this->returnJson(array('success' => true));
        } elseif (!empty($session['document-copy'])) {
            $url_key         = $this->getRequest()->getQuery('url_key');
            $search_document = DocumentModel::fromUrlKey($url_key, $parent_id);
            if (!empty($search_document)) {
                return $this->returnJson(array('success' => false));
            }

            $document = DocumentModel::fromId($session['document-copy']);

            if (!empty($parent_document)) {
                $available_children = $parent_document->getDocumentType()->getDependencies();
                if (!in_array($document->getDocumentType()->getId(), $available_children)) {
                    return $this->returnJson(array('success' => false));
                }
            }

            $copy_document            = new DocumentModel();
            $copy_document_properties = new Property\Collection();
            $copy_document_properties->load(null, null, $document->getId());

            $copy_document->addData($document->getData());
            $copy_document->setId(null);
            $copy_document->setParentId($parent_id);
            $copy_document->setName($this->getRequest()->getQuery('name'));
            $copy_document->setUrlKey($url_key);
            $copy_document->save();

            foreach ($copy_document_properties->getProperties() as $property) {
                $value = $property->getValueModel();
                if (empty($value)) {
                    continue;
                }

                $copy_property = new Property\Value\Model();
                $copy_property->addData($value->getData());
                $copy_property->setId(null);
                $copy_property->setDocumentId($copy_document->getId());
                $copy_property->save();
            }

            return $this->returnJson(array('success' => true));
        } else {
            return $this->returnJson(array('success' => false));
        }
    }

    /**
     * Refresh treeview
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function refreshTreeviewAction()
    {
        $document_id = $this->getRouteMatch()->getParam('id', 0);
        if (empty($document_id)) {
            $documents = new DocumentCollection();
            $documents->load($document_id);
            $documents_list = $documents->getChildren();
        } else {
            $documents      = DocumentModel::fromId($document_id);
            $documents_list = $documents->getChildren();
        }


        return $this->returnJson(array('treeview' => Component\TreeView::render($documents_list, false)));
    }

    /**
     * Sort document action
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function sortOrderAction()
    {
        $order = $this->getRequest()->getPost()->get('order');
        $list  = explode(',', str_replace('document_', '', $order));

        foreach ($list as $order => $document_id) {
            $document_model = DocumentModel::fromId($document_id);
            if (!empty($document_model)) {
                $document_model->setSortOrder($order);
                $document_model->save();
            }
        }

        return $this->returnJson(array('success' => true));
    }

    /**
     * Check url key with deleted space and special chars
     *
     * @param string $string Url key
     *
     * @return string
     */
    protected function checkUrlKey($string)
    {
        $replace = array(
            ' ',
            'àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'
        );
        $to      = array(
            '-',
            'aaaaaceeeeiiiinooooouuuuyyaaaaaceeeeiiiinooooouuuuy'
        );
        $string  = strtolower(str_replace($replace, $to, trim($string)));

        return $string;
    }

    /**
     * Load tabs from document type
     *
     * @param integer $document_type_id Document type id
     *
     * @return \Gc\Tab\Collection
     */
    protected function loadTabs($document_type_id)
    {
        $document_type = DocumentType\Model::fromId($document_type_id);
        $tabs          = $document_type->getTabs();

        return $tabs;
    }

    /**
     * Load properties from document type, tab and document
     *
     * @param integer $document_type_id Document type id
     * @param integer $tab_id           Tab id
     * @param integer $document_id      Document id
     *
     * @return \Gc\Property\Collection
     */
    protected function loadProperties($document_type_id, $tab_id, $document_id)
    {
        $properties = new Property\Collection();
        $properties->load($document_type_id, $tab_id, $document_id);

        return $properties->getProperties();
    }
}
