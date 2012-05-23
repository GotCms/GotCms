<?php
/**
 * This source file is part of Got CMS.
 *
 * Got CMS is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Got CMS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License along
 * with Got CMS. If not, see <http://www.gnu.org/licenses/lgpl-3.0.html>.
 *
 * PHP Version >=5.3
 *
 * @category Controller
 * @package  Content\Controller
 * @author   Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license  GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link     http://www.got-cms.com
 */

namespace Content\Controller;

use Gc\Mvc\Controller\Action,
    Gc\Datatype,
    Gc\Document\Collection as DocumentCollection,
    Gc\Document\Model as DocumentModel,
    Gc\DocumentType,
    Gc\Property,
    Content\Form,
    Gc\Component,
    Zend\Json\Json;

class DocumentController extends Action
{
    /**
     * Contains information about acl
     * @var array $_acl_page
     */
    protected $_acl_page = array('resource' => 'Content', 'permission' => 'document');

    /**
     * Initialize Document Controller
     * @return void
     */
    public function init()
    {
        $documents = new DocumentCollection();
        $documents->load(0);

        $this->layout()->setVariable('treeview',  Component\TreeView::render(array($documents)));

        $routes = array(
            'edit' => 'documentEdit'
            , 'new' => 'documentCreate'
            , 'delete' => 'documentDelete'
            , 'copy' => 'documentCopy'
            , 'cut' => 'documentCut'
            , 'paste' => 'documentPaste'
        );

        $array_routes = array();
        foreach($routes as $key => $route)
        {
            $array_routes[$key] = $this->url()->fromRoute($route, array('id' => 'itemId'));
        }

        $this->layout()->setVariable('routes', Json::encode($array_routes));
    }

    /**
     *
     *
     * @return \Zend\View\Model\ViewModel|array
     */
    public function indexAction()
    {

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
        if(!empty($parent_id))
        {
            $document_form->getElement('parent')->setValue($parent_id);
        }

        if($this->getRequest()->isPost())
        {
            if(!$document_form->isValid($this->getRequest()->post()->toArray()))
            {
                $this->flashMessenger()->setNameSpace('error')->addMessage('Invalid document data');
            }
            else
            {
                $document_name = $document_form->getValue('name');
                $document_url_key = $document_form->getValue('url_key');
                $document_type_id = $document_form->getValue('document_type');
                $parent_id = $document_form->getValue('parent');
                $document = new DocumentModel();
                $document->setName($document_name)
                    ->setDocumentTypeId($document_type_id)
                    ->setParentId($parent_id)
                    ->setUrlKey(!empty($document_url_key) ? $document_url_key : $this->_checkUrlKey($document_name))
                    ->setUserId($this->getAuth()->getIdentity()->getId());

                $document_id = $document->save();
                if(empty($document_id))
                {
                    $this->flashMessenger()->setNameSpace('error')->addMessage('Can not add document');
                }
                else
                {
                    $this->flashMessenger()->setNameSpace('success')->addMessage('Document successfuly add');
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
        if(empty($document))
        {
            $this->flashMessenger()->setNameSpace('error')->addMessage('Document does not exists !');
        }
        else
        {
            try
            {
                if($document->delete())
                {
                    $this->flashMessenger()->setNameSpace('success')->addMessage('This document was succefully delete');
                }
                else
                {
                    $this->flashMessenger()->setNameSpace('success')->addMessage('There were problems during the removal of this document');
                }
            }
            catch (Exception $e)
            {
                \Gc\Error::set(get_class($this), $e);
            }
        }

        return $this->redirect()->toRoute('content');
    }

    /**
     * Edit Document
     *
     * @return \Zend\View\Model\ViewModel|array
     */
    public function editAction()
    {
        $document = DocumentModel::fromId($this->getRouteMatch()->getParam('id', ''));
        $document_form = new \Zend\Form\Form();
        if(empty($document))
        {
            $this->flashMessenger()->setNameSpace('error')->addMessage('Document does not exists !');
        }
        else
        {
            $document_type_id = $document->getDocumentTypeId();
            $layout_id = $this->getRouteMatch()->getParam('layout_id', '');

            if($this->getRequest()->isPost())
            {
                $has_error = FALSE;
                $document_vars = $this->getRequest()->post()->get('document');

                $document->setName(empty($document_vars['name']) ? $document->getName() : $document_vars['name']);
                $document->setStatus(empty($document_vars['status']) ? DocumentModel::STATUS_DISABLE : $document_vars['status']);
                $document->showInNav(empty($document_vars['show_in_nav']) ? FALSE : $document_vars['show_in_nav']);
                $document->setLayoutId(empty($document_vars['layout']) ? FALSE : $document_vars['layout']);
                $document->setViewId(empty($document_vars['view']) ? $document->getViewId() : $document_vars['view']);
                $document->setUrlKey(empty($document_vars['url_key']) ? '' : $document_vars['url_key']);
            }

            $tabs = $this->_loadTabs($document_type_id);
            $tabs_array = array();
            $datatypes = array();

            $idx = 1;
            foreach($tabs as $tab)
            {
                $tabs_array[] = $tab->getName();
                $properties = $this->_loadProperties($document_type_id, $tab->getId(), $document->getId());

                $fieldset = new \Zend\Form\Fieldset('tabs-'.$idx, $idx);
                foreach($properties as $property)
                {
                    $property->setDocumentId($document->getId())->loadValue();
                    if($this->getRequest()->isPost())
                    {
                        if(!Datatype\Model::saveEditor($property, $document))
                        {
                            $has_error = TRUE;
                        }
                    }

                    \Gc\Form\AbstractForm::addContent($fieldset, Datatype\Model::loadEditor($property, $document));
                }

                $document_form->add($fieldset);
                $idx++;
            }

            $tabs_array[] = 'Document information';

            $form_document_add = new Form\Document();
            $form_document_add->load($document, $idx);
            $form_document_add->setAttribute('name', 'tabs-'.$idx, $idx);

            $document_form->add($form_document_add);

            if($this->getRequest()->isPost())
            {
                if($has_error)
                {
                    $document->showInNav(FALSE);
                    $document->setStatus(FALSE);
                    $this->flashMessenger()->setNameSpace('error')->addMessage('This document cannot be published and show in nav because one or more properties values are required !');
                }

                $document->save();
                $this->redirect()->toRoute('documentEdit', array('id' => $document->getId()));
            }

            $tabs = new Component\Tabs($tabs_array);

            return array('form' => $document_form, 'tabs' => $tabs);
        }
    }

    /**
     * Check url key with deleted space and special chars
     * @param string $string
     * @return string
     */
    protected function _checkUrlKey($string)
    {
        $replace = array(' ', 'àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ');
        $to = array('-', 'aaaaaceeeeiiiinooooouuuuyyaaaaaceeeeiiiinooooouuuuy');
        $string = strtolower(str_replace($replace, $to, trim($string)));

        return $string;
    }

    /**
     * Load tabs from document type
     * @param integer $document_type_id
     * @return \Gc\Tab\Collection
     */
    protected function _loadTabs($document_type_id)
    {
        $document_type = DocumentType\Model::fromId($document_type_id);
        $tabs = $document_type->getTabs();

        return $tabs;
    }

    /**
     * Load properties from document type, tab and document
     * @param integer $document_type_id
     * @param integer $tab_id
     * @param integer $document_id
     * @return \Gc\Property\Collection
     */
    protected function _loadProperties($document_type_id, $tab_id, $document_id)
    {
        $properties = new Property\Collection();
        $properties->load($document_type_id, $tab_id, $document_id);

        return $properties->getProperties();
    }
}
