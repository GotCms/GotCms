<?php

class Content_DocumentController extends Es_Controller_Action
{
    public function init()
    {
        $this->_helper->layout->assign('treeview', Es_Component_TreeView::render(array(new Es_Model_DbTable_Document_Collection())));

        $router = $this->getFrontController()->getRouter();
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
            if($router->hasRoute($route))
            {
                $array_routes[$key] = $router->assemble(array('id' => ':id'), $route);
            }
        }

        $this->_helper->layout->assign('routes', Zend_Json::encode($array_routes));
    }

    public function addAction()
    {
        $document_form = new Content_Form_DocumentAdd();
        $document_form->setAction($this->getFrontController()->getRouter()->assemble(array(), 'documentAdd'));

        if($this->getRequest()->isPost())
        {
            if(!$document_form->isValid($this->getRequest()->getPost()))
            {
                $this->_helper->flashMessenger->setNameSpace('error')->addMessage('Invalid document data');
            }
            else
            {
                $document_name = $document_form->getValue('name');
                $document_url_key = $document_form->getValue('url_key');
                $document_type_id = $document_form->getValue('document_type');
                $parent_id = $this->getRequest()->getPost('parent_id');
                $document = new Es_Model_DbTable_Document_Model();
                $document->setName($document_name)
                    ->setDocumentTypeId($document_type_id)
                    ->setParentId($parent_id)
                    ->setUrlKey(!empty($document_url_key) ? $document_url_key : $this->checkUrlKey($document_name));

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
        $document = Es_Model_DbTable_Document_Model::fromId($this->getRequest()->getParam('id', ''));
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
        $document = Es_Model_DbTable_Document_Model::fromId($this->getRequest()->getParam('id', ''));
        $document_form = new Zend_Form();
        if(empty($document))
        {
            $this->_helper->flashMessenger->setNameSpace('error')->addMessage('Document does not exists !');
        }
        else
        {
            $has_error = FALSE;
            $document_type_id = $document->getDocumentTypeId();
            $isPost = $this->getRequest()->isPost();
            $layout_id = $this->getRequest()->getParam('layout_id', '');
            if($layout_id === null OR $layout_id == '')
            {
                $isPost = FALSE;
            }

            if($isPost)
            {
                $document->setName($this->getRequest()->getPost('document_name', $document->getDocumentName()));
                $document->setStatus($this->getRequest()->getPost('document_status', FALSE));
                $document->showInNav($this->getRequest()->getPost('document_show_in_nav', FALSE));
                $document->setLayoutId($this->getRequest()->getPost('layout_id', FALSE));
                $view_id = $this->getRequest()->getPost('view_id', $document->getViewId());
                if($view_id == "null")
                {
                    $view_id = null;
                }

                $document->setViewId($view_id);
                if($document->setUrlKey($this->getRequest()->getParam('document_url_key', $document->getDocumentUrlKey()))=== FALSE)
                {
                    $document->setUrlKey($this->checkUrlKey($document->getDocumentName()));
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
                $subForm->setIsArray(FALSE);
                foreach($properties as $property)
                {
                    if($isPost)
                    {
                        if($this->saveDatatypeEditor($this->loadDatatype($property->getDatatypeId(), $document->getId()), $property) == FALSE) {
                            $hasError = TRUE;
                        }
                    }

                    Es_Form_Model::addFormContent($subForm, $this->loadDatatypeEditor($this->loadDatatype($property->getDatatypeId(), $document->getId()), $property));
                }

                $document_form->addSubForm($subForm, 'tabs-'.$i, $i);
                $i++;
            }

            $tabs_array[] = 'Document information';

            $form_document_add = new Content_Form_DocumentAdd();
            $form_document_add->init($document, $i);

            $document_form->addSubForm($form_document_add, 'tabs-'.$i, $i);
            if($has_error)
            {
                $document->setDocumentShowInNav(FALSE);
                $document->setDocumentStatus(FALSE);
                $this->view->message = 'This document cannot be published and show in nav because one or more properties are required !';
            }

            $document->save();
            $this->view->tabs = new Es_Component_Tabs($tabs_array);
            $this->view->form = $document_form;
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
        $document_type = Es_Model_DbTable_DocumentType_Model::fromId($document_type_id);
        $tabs = $document_type->getTabs();

        return $tabs;
    }

    protected function loadProperties($document_type_id, $tab_id, $document_id)
    {
        $properties = new Es_Model_DbTable_Property_Collection();
        $properties->init($document_type_id, $tab_id, $document_id);

        return $properties->getProperties();
    }

    protected function saveEditor(Es_Datatype_Abstract $datatype, $property_id)
    {
        return $datatype->getEditor($property_id)->save($this->getRequest());
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
