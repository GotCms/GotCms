<?php

class Content_DocumentController extends Es_Controller_Action
{
    public function init()
    {
        /* Initialize action controller here */
    }

    public function addAction()
    {
        $document_form = new Content_Form_DocumentAdd();

        if($this->_request->isPost())
        {
            if(!$document_form->isValid($this->_request->getPost()))
            {
                $this->_helper->flashMessenger->setNameSpace('error')->addMessage('Invalid document data');
            }
            else
            {
                $document_name = $this->_request->getParam('name', '');
                $document_url_key = $this->_request->getParam('url_key', $this->checkUrlKey($document_name));
                $document_type_id = $this->_request->getParam('document_type_id', '');
                $parent_id = $this->_request->getParam('parent_id', 0);
                $document = new Es_Model_DbTable_Document_Model();
                $document->setName($document_name)
                    ->setDocumentTypeId($document_type_id)
                    ->setParentId($parent_id)
                    ->setUrlKey($document_url_key);

                $document_id = $document->save();
                if(empty($document_id))
                {
                    $this->_helper->flashMessenger->setNameSpace('error')->addMessage('Can not add document');
                }
                else
                {
                    $this->_helper->flashMessenger->setNameSpace('success')->addMessage('Document successfuly add');
                    $this->_helper->redirector->goToRoute(array('id' => $document_id), 'documentEdit');
                }
            }
        }

        $this->view->form = $document_form;

    }

    public function deleteAction()
    {
        $document = Es_Model_DbTable_Document_Model::fromId($this->_request->getParam('id', ''));
        if(empty($document))
        {
            $this->_helper->flashMessenger->setNameSpace('error')->addMessage('Document does not exists !');
        }
        else
        {
            try
            {
                if($document->delete())
                {
                    $this->_helper->flashMessenger->setNameSpace('success')->addMessage('This document was succefully delete');
                }
                else
                {
                    $this->_helper->flashMessenger->setNameSpace('success')->addMessage('There were problems during the removal of this document');
                }
            }
            catch (Exception $e)
            {
                Es_Error::set(get_class($this), $e);
            }
        }
    }

    public function editAction()
    {

        $document = Es_Model_DbTable_Document_Model::fromId($this->_request->getParam('id', ''));
        if(empty($document))
        {
            $this->_helper->flashMessenger->setNameSpace('error')->addMessage('Document does not exists !');
        }
        else
        {
            $document_type_id = $document->getDocumentTypeId();
            $isPost = $this->getRequest()->isPost();
            $layout_id = $this->_request->getParam('layout_id', '');
            if($layout_id === null OR $layout_id == '')
            {
                $isPost = false;
            }

            if($isPost)
            {
                $document->setDocumentName($this->getRequest()->getParam('document_name', $document->getDocumentName()));
                $document->setDocumentStatus($this->getRequest()->getParam('document_status', false));
                $document->setDocumentShowInNav($this->getRequest()->getParam('document_show_in_nav', false));
                $document->setLayoutId($this->getRequest()->getParam('layout_id', false));
                $view_id = $this->getRequest()->getParam('view_id', $document->getViewId());
                if($view_id == "null")
                {
                    $view_id = null;
                }

                $document->setViewId($view_id);
                if($document->setDocumentUrlKey($this->getRequest()->getParam('document_url_key', $document->getDocumentUrlKey()))=== false)
                {
                    $document->setDocumentUrlKey($this->setNameToUrlKey($document->getDocumentName()));
                }
            }

            $tabs = $this->loadTabs($document_type_id);
            $tabs_array = array();
            $datatypes = array();

            $i = 1;
            foreach($tabs as $tab)
            {
                $tabs_array[] = $tab->getName();
                $properties = $this->loadProperties($document_type_id, $tab->getId(), $document->getId());
                $subForm = new Zend_Form_SubForm();
                $subForm->addDecorators(array('FormElements',array('HtmlTag', array('tag' => 'dl','id' => 'tabs-'.$i))));
                $subForm->removeDecorator('Fieldset');
                $subForm->removeDecorator('DtDdWrapper');
                $subForm->setIsArray(false);
                foreach($properties as $property)
                {
                    if($isPost)
                    {
                        if($this->saveDatatypeEditor($this->loadDatatype($property->getDatatypeId(), $document->getId()), $property) == false) {
                            $hasError = true;
                        }
                    }

                    Es_Form_Model::addFormContent($subForm, $this->loadDatatypeEditor($this->loadDatatype($property->getDatatypeId(), $document->getId()), $property));
                }

                $document_form->addSubForm($subForm, 'tabs-'.$i, $i);
                $i++;
            }

            $tabs_array[] = 'Document information';

            $document_form->addSubForm(new Form_Content($document, $i), 'tabs-'.$i, $i);
            if($hasError)
            {
                $document->setDocumentShowInNav(false);
                $document->setDocumentStatus(false);
                $this->view->message = 'This document cannot be published and show in nav because one or more properties are required !';
            }

            $document->save();
            $this->view->tabs = new Es_Component_Tabs($tabs_array);
            $this->view->content = $document_form;
        }
    }

    public function checkUrlKey($string)
    {
        $replace = array(' ', 'àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ');
        $to = array('-', 'aaaaaceeeeiiiinooooouuuuyyaaaaaceeeeiiiinooooouuuuy');
        $string = strtolower(str_replace($replace, $to, trim($string)));

        return $string;
    }

    protected function loadTabs($document_type_id)
    {
        $document_type = Es_DocumentType_Model::fromId($document_type_id);
        $tabs = $document_type->getTabs();

        return $tabs;
    }

    protected function loadProperties($document_type_id, $tab_id, $document_id)
    {
        $properties = new Es_Component_Property_Collection($document_type_id, $tab_id, $document_id);

        return $properties->getProperties();
    }

    protected function saveEditor(Es_Datatype_Abstract $datatype, $property_id)
    {
        return $datatype->getEditor($property_id)->save($this->_request);
    }

    protected function loadEditor(Es_Datatype_Abstract $datatype, $property_id)
    {
        return $datatype->getEditor($property_id)->load();
    }

    protected function loadDatatype($datatype_id, $document_id)
    {
        $datatype = Es_Datatype_Model::fromId($datatype_id);
        $model = $datatype->getModel();
        $class = 'Datatypes_'.$model->getAlias().'_Datatype';

        return new $class($datatype->getId(), $document_id);
    }
}
