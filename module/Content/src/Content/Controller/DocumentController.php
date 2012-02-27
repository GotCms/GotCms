<?php

namespace Content\Controller;

use Es\Mvc\Controller\Action,
    Application\Model\Datatype,
    Application\Model\Document\Collection as DocumentCollection,
    Application\Model\Document\Model as DocumentModel,
    Application\Model\DocumentType,
    Application\Model\Property,
    Content\Form,
    Es\Component,
    Zend\Json\Json;

class DocumentController extends Action
{
    public function init()
    {
        $documents = new DocumentCollection();
        $documents->load(0);

        $this->layout()->setVariable('treeview',  Component\TreeView::render(array($documents)));

        $routes = array(
            'edit' => 'documentEdit'
            , 'new' => 'documentAdd'
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

        $this->layout()->routes = Json::encode($array_routes);
    }

    public function indexAction()
    {

    }

    public function addAction()
    {
        $document_form = new Form\DocumentAdd();
        $document_form->setAction($this->url()->fromRoute('documentAdd'));

        if($this->getRequest()->isPost())
        {
            if(!$document_form->isValid($this->getRequest()->post()))
            {
                $this->flashMessenger()->setNameSpace('error')->addMessage('Invalid document data');
            }
            else
            {
                $document_name = $document_form->getValue('name');
                $document_url_key = $document_form->getValue('url_key');
                $document_type_id = $document_form->getValue('document_type');
                $parent_id = $this->getRequest()->getPost('parent_id');
                $document = new DocumentModel();
                $document->setName($document_name)
                    ->setDocumentTypeId($document_type_id)
                    ->setParentId($parent_id)
                    ->setUrlKey(!empty($document_url_key) ? $document_url_key : $this->_checkUrlKey($document_name));

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
                Es_Error::set(get_class($this), $e);
            }
        }

        return $this->redirect()->toRoute('content');
    }

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
                $document->setStatus(empty($document_vars['status']) ? FALSE : $document_vars['status']);
                $document->showInNav(empty($document_vars['show_in_nav']) ? FALSE : $document_vars['show_in_nav']);
                $document->setLayoutId(empty($document_vars['layout']) ? FALSE : $document_vars['layout']);
                $document->setViewId(empty($document_vars['view']) ? $document->getViewId() : $document_vars['view']);
                $document->setUrlKey(empty($document_vars['url_key']) ? $document->getUrlKey() : $document_vars['url_key']);
            }

            $tabs = $this->_loadTabs($document_type_id);
            $tabs_array = array();
            $datatypes = array();

            $i = 1;
            foreach($tabs as $tab)
            {
                $tabs_array[] = $tab->getName();
                $properties = $this->_loadProperties($document_type_id, $tab->getId(), $document->getId());
                $sub_form = new \Zend\Form\SubForm();
                $sub_form->addDecorators(array('FormElements',array('HtmlTag', array('tag' => 'dl','id' => 'tabs-'.$i))));
                $sub_form->removeDecorator('Fieldset');
                $sub_form->removeDecorator('DtDdWrapper');
                $sub_form->setIsArray(FALSE);
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

                    \Es\Form\AbstractForm::addContent($sub_form, Datatype\Model::loadEditor($property, $document));
                }

                $document_form->addSubForm($sub_form, 'tabs-'.$i, $i);
                $i++;
            }

            $tabs_array[] = 'Document information';

            $form_document_add = new Form\DocumentAdd();
            $form_document_add->load($document, $i);

            $document_form->addSubForm($form_document_add, 'tabs-'.$i, $i);

            $submit = new \Zend\Form\Element\Submit('submit-form');
            $submit->setLabel('Save');
            $document_form->addElement($submit);

            if($this->getRequest()->isPost())
            {
                if($has_error)
                {
                    $document->showInNav(FALSE);
                    $document->setStatus(FALSE);
                    $this->flashMessenger()->setNameSpace('error')->addMessage('This document cannot be published and show in nav because one or more properties are required !');

                }

                $document->save();
                $this->redirect()->toRoute('documentEdit', array('id' => $document->getId()));
            }

            $tabs = new Component\Tabs($tabs_array);
            return array('form' => $document_form, 'tabs' => $tabs);
        }
    }

    protected function _checkUrlKey($string)
    {
        $replace = array(' ', 'àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ');
        $to = array('-', 'aaaaaceeeeiiiinooooouuuuyyaaaaaceeeeiiiinooooouuuuy');
        $string = strtolower(str_replace($replace, $to, trim($string)));

        return $string;
    }

    protected function _loadTabs($document_type_id)
    {
        $document_type = DocumentType\Model::fromId($document_type_id);
        $tabs = $document_type->getTabs();

        return $tabs;
    }

    protected function _loadProperties($document_type_id, $tab_id, $document_id)
    {
        $properties = new Property\Collection();
        $properties->load($document_type_id, $tab_id, $document_id);

        return $properties->getProperties();
    }
}
