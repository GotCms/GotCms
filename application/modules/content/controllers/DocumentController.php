<?php

class Content_DocumentController extends Es_Controller_Action
{
    public function init()
    {
        /* Initialize action controller here */
    }

    public function addAction()
    {
        $form = new Content_Form_DocumentAdd();

        if(!$this->_request->isPost())
        {
            $this->view->form = $form;
        }
        else
        {
            $name = $this->_request->getParam('name', '');
            $url_key = $this->_request->getParam('url_key', $this->checkUrlKey($name));
            $document_type_id = $this->_request->getParam('document_type_id', '');
            $parent_id = $this->_request->getParam('parent_id', 0);
            if(empty($name) or empty($document_type_id))
            {
                echo 'There are errors !';
            }
            else
            {
                $document = new Es_Document_Model();
                $document->setDocumentName($name)
                        ->setDocumentTypeId($document_type_id)
                        ->setParentId($parent_id)
                        ->setDocumentUrlKey($url_key);
                if(!$document->save())
                {
                    throw new Es_Core_Exception(get_class($this).' -> Method : '.__METHOD__);
                }
                else
                {
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
                            if($this->_request->isPost())
                            {
                                $this->saveDatatypeEditor($this->loadDatatype($property->getDatatypeId(), $document->getId()), $property);
                            }

                            Es_Form_Model::addFormContent($subForm, $this->loadDatatypeEditor($this->loadDatatype($property->getDatatypeId(), $document->getId()), $property));
                        }

                        $formDocument->addSubForm($subForm, 'tabs-'.$i, $i);
                        $i++;
                    }

                    $tabs_array[] = 'Document information';

                    $formDocument->addSubForm(new Form_Content($document, $i), 'tabs-'.$i, $i);

                    $this->view->tabs = new Es_Component_Tabs($tabs_array);
                    $this->view->content = $formDocument;
                }
            }
        }

    }

    public function deleteAction()
    {
        $document = Es_Document_Model::fromId($this->_request->getParam('id', ''));
        if($document === null)
        {
            $this->message = "This document don't exist !";
        }
        else
        {
            try
            {
                if($document->delete())
                {
                    $this->message = "This document was succefully delete";
                }
                else
                {
                    $this->message = "There were problems during the removal of this document";
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
        //Init for required fields
        $hasError = false;

        $document = Es_Document_Model::fromId($this->_request->getParam('id', ''));
        if($document === null)
        {
            echo 'There are errors !';
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

                $formDocument->addSubForm($subForm, 'tabs-'.$i, $i);
                $i++;
            }

            $tabs_array[] = 'Document information';

            $formDocument->addSubForm(new Form_Content($document, $i), 'tabs-'.$i, $i);
            if($hasError)
            {
                $document->setDocumentShowInNav(false);
                $document->setDocumentStatus(false);
                $this->view->message = 'This document cannot be published and show in nav because one or more properties are required !';
            }

            $document->save();
            $this->view->tabs = new Es_Component_Tabs($tabs_array);
            $this->view->content = $formDocument;
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
