<?php

namespace Development\Controller;

use Development\Form\DocumentType as DocumentTypeForm,
    Gc\DocumentType,
    Gc\Mvc\Controller\Action,
    Gc\Property,
    Gc\Tab;

class DocumentTypeController extends Action
{
    protected $_session = NULL;

    public function init()
    {
    }

    public function indexAction()
    {
        // action body
    }

    public function createAction()
    {
        $form = new DocumentTypeForm();
        $form->setView($this->getLocator()->get('view'));
        $request = $this->getRequest();
        $session = $this->getSession();

        if($request->isPost())
        {
            if(!empty($session['document-type']))
            {
                $form->setValues($session['document-type']);
            }

            if($form->isValid($this->getRequest()->post()->toArray()))
            {
                $document_type = new DocumentType\Model();
                $property_collection = new Property\Collection();

                $infos_subform = $form->getSubForm('infos');
                $views_subform = $form->getSubForm('views');
                $tabs_subform = $form->getSubForm('tabs');

                $document_type->addData(array(
                    'user_id' => $this->getAuth()->getIdentity()->id
                    , 'name' => $infos_subform->getValue('name')
                    , 'description' => $infos_subform->getValue('description')
                    , 'default_view_id' => $views_subform->getValue('default_view')
                ));

                $document_type->getAdapter()->getDriver()->getConnection()->beginTransaction();
                try
                {
                    $document_type->addViews($views_subform->getValue('available_views'));
                    $document_type->save();

                    $tabs_array = array();
                    foreach($tabs_subform->getValues(TRUE) as $tabs_name => $tab_values)
                    {
                        foreach($tab_values as $id => $value)
                        {
                            $tabs_array[$id][$tabs_name] = $value;
                        }
                    }

                    $tabs = array();
                    $idx = 0;
                    foreach($tabs_array as $tab_id => $tab)
                    {
                        $tab_model = Tab\Model::fromArray($tab);
                        $tab_model->setDocumentTypeId($document_type->getId());
                        $tab_model->setOrder(++$idx);
                        $tab_model->save();
                        $tabs[$tab_id] = $tab_model->getId();
                    }

                    $properties = array();
                    $properties_values = $this->getRequest()->post()->get('properties');

                    foreach($properties_values as $property_name => $property_value)
                    {

                        foreach($property_value as $id => $value)
                        {
                            if($property_name == 'tab')
                            {
                                $properties[$id]['tab_id'] = $tabs[$value];
                            }
                            elseif($property_name == 'datatype')
                            {
                                $properties[$id]['datatype_id'] = $value;
                            }
                            elseif($property_name == 'required')
                            {
                                $properties[$id]['is_required'] = $value == 1 ? TRUE : FALSE;
                            }
                            else
                            {
                                $properties[$id][$property_name] = $value;
                            }
                        }

                    }

                    $idx = 0;
                    foreach($properties as $property)
                    {
                        $properties[$id]['order'] = ++$idx;
                    }

                    $property_collection->setProperties($properties);
                    $property_collection->save();

                    $document_type->getAdapter()->getDriver()->getConnection()->commit();
                }
                catch(Exception $e)
                {
                    $document_type->getAdapter()->getDriver()->getConnection()->rollBack();
                    throw new \Gc\Exception("Error Processing Request ".print_r($e, TRUE), 1);
                }
            }
            else
            {
                $this->flashMessenger()->setNameSpace('error')->addMessage('Can save document_type');
            }
        }

        $session['document-type'] = array();

        return array('form' => $form);
    }

    public function editAction()
    {
        $document_type_id = $this->getRouteMatch()->getParam('id');
        $document_type = DocumentType\Model::fromId($document_type_id);
        $form = new DocumentTypeForm();
        $form->setView($this->getLocator()->get('view'));
        $request = $this->getRequest();
        $session = $this->getSession();

        $form->setValues($document_type);

        if(!empty($session['document-type']))
        {
            $form->setValues($session['document-type']);
        }


        if($request->isPost())
        {
            if($form->isValid($this->getRequest()->post()->toArray()))
            {
                $property_collection = new Property\Collection();

                $infos_subform = $form->getSubForm('infos');
                $views_subform = $form->getSubForm('views');
                $tabs_subform = $form->getSubForm('tabs');

                $document_type->addData(array(
                    'user_id' => $this->getAuth()->getIdentity()->id
                    , 'name' => $infos_subform->getValue('name')
                    , 'description' => $infos_subform->getValue('description')
                    , 'default_view_id' => $views_subform->getValue('default_view')
                ));

                $document_type->getAdapter()->getDriver()->getConnection()->beginTransaction();
                try
                {
                    $document_type->addViews($views_subform->getValue('available_views'));
                    $document_type->save();

                    $tabs_array = array();
                    foreach($tabs_subform->getValues(TRUE) as $tabs_name => $tab_values)
                    {
                        foreach($tab_values as $id => $value)
                        {
                            $tabs_array[$id][$tabs_name] = $value;
                        }
                    }

                    $tabs = array();
                    $idx = 0;
                    foreach($tabs_array as $tab_id => $tab)
                    {
                        $tab_model = Tab\Model::fromArray($tab);
                        $tab_model->setDocumentTypeId($document_type->getId());
                        $tab_model->setOrder(++$idx);
                        $tab_model->save();
                        $tabs[$tab_id] = $tab_model->getId();
                    }

                    $properties = array();
                    $properties_values = $this->getRequest()->post()->get('properties');

                    foreach($properties_values as $property_name => $property_value)
                    {

                        foreach($property_value as $id => $value)
                        {
                            if($property_name == 'tab')
                            {
                                $properties[$id]['tab_id'] = $tabs[$value];
                            }
                            elseif($property_name == 'datatype')
                            {
                                $properties[$id]['datatype_id'] = $value;
                            }
                            elseif($property_name == 'required')
                            {
                                $properties[$id]['is_required'] = $value == 1 ? TRUE : FALSE;
                            }
                            else
                            {
                                $properties[$id][$property_name] = $value;
                            }
                        }

                    }

                    $idx = 0;
                    foreach($properties as $property)
                    {
                        $properties[$id]['order'] = ++$idx;
                    }

                    $property_collection->setProperties($properties);
                    $property_collection->save();

                    $document_type->getAdapter()->getDriver()->getConnection()->commit();
                }
                catch(Exception $e)
                {
                    $document_type->getAdapter()->getDriver()->getConnection()->rollBack();
                    throw new \Gc\Exception("Error Processing Request ".print_r($e, TRUE), 1);
                }
            }
            else
            {
                $this->flashMessenger()->setNameSpace('error')->addMessage('Can save document_type');
            }
        }

        $session['document-type'] = array();
        $session['document-type']['tabs'] = array();
        foreach($document_type->getTabs() as $tab)
        {
            $session['document-type']['tabs'][$tab->getId()] = array(
                'name' => $tab->getName()
                , 'description' => $tab->getDescription()
                , 'properties' => array()
            );

            foreach($tab->getProperties() as $property)
            {
                $session['document-type']['tabs'][$tab->getId()]['properties'][$property->getId()] = array(
                    'name' => $property->getName()
                    , 'identifier' => $property->getIdentifier()
                    , 'tab' => $property->getTabId()
                    , 'description' => $property->getDescription()
                    , 'is_required' => $property->isRequired()
                    , 'datatype' => $property->getDatatypeId()
                );
            }
        }

        return array('form' => $form);
    }

    public function listAction()
    {
        $documents = new DocumentType\Collection();
        return array('documents' => $documents->getDocumentTypes());
    }

    public function deleteAction()
    {
        $document_type_id = $this->getRouteMatch()->getParam('id', NULL);
        $document_type = DocumentType\Model::fromId($document_type_id);
        if(empty($document_type_id) or empty($document_type) or !$document_type->delete())
        {
            $this->flashMessenger()->setNameSpace('error')->addMessage('Can not delete this document type');
        }
        else
        {
            $this->flashMessenger()->setNameSpace('success')->addMessage('This document type has been deleted');
        }

        return $this->redirect()->toRoute('documentTypeList');
    }

    public function addTabAction()
    {
        if($this->getRequest()->isPost())
        {
            $session = $this->getSession();
            $name = $this->getRequest()->post()->get('name');
            $description = $this->getRequest()->post()->get('description');

            $tabs = empty($session['document-type']['tabs']) ? array() : $session['document-type']['tabs'];
            $last_element = end($tabs);
            if(empty($last_element))
            {
                $last_id = 0;
            }
            else
            {
                $last_id = array_search($last_element, $tabs);
            }

            foreach($tabs as $tab)
            {
                if($name == $tab['name'])
                {
                    return $this->_returnJson(array('success' => FALSE, 'message' => 'Already exists'));
                }
            }

            $current_id = $last_id + 1;
            $tabs[$current_id] = array('name' => $name, 'description' => $description, 'properties' => array());
            $session['document-type']['tabs'] = $tabs;

            return $this->_returnJson(array('success' => TRUE
                , 'id' => $current_id
                , 'name' => $name
                , 'description' => $description));
        }

        return $this->_returnJson(array('success' => FALSE, 'message' => 'Error'));
    }

    public function deleteTabAction()
    {
        if($this->getRequest()->isPost())
        {
            $session = $this->getSession();
            if(empty($session['document-type']))
            {
                $session['document-type'] = array();
            }

            $id = $this->getRequest()->post()->get('tab');
            $description = $this->getRequest()->post()->get('description');

            $tabs = empty($session['document-type']) ? array() : $session['document-type']['tabs'];
            if(array_key_exists($id, $tabs))
            {
                unset($session['document-type']['tabs'][$id]);

                return $this->_returnJson(array('success' => TRUE, 'message' => 'Tab successfullty deleted'));
            }
        }

        return $this->_returnJson(array('success' => FALSE, 'message' => 'Error'));
    }

    public function addPropertyAction()
    {
        if($this->getRequest()->isPost())
        {
            $post = $this->getRequest()->post();
            $name           = $post->get('name');
            $identifier     = $post->get('identifier');
            $tab_id         = $post->get('tab');
            $description    = $post->get('description');
            $is_required    = $post->get('is_required');
            $datatype_id    = $post->get('datatype');

            $session = $this->getSession();
            $tabs = $session['document-type']['tabs'];

            if(empty($session['document-type']['tabs'][$tab_id]))
            {
                return $this->_returnJson(array('success' => FALSE, 'message' => 'Tab does not exists'));
            }

            $tab = $session['document-type']['tabs'][$tab_id];
            $properties = $tab['properties'];
            $last_element = end($properties);
            if(empty($last_element))
            {
                $last_id = 0;
            }
            else
            {
                $last_id = array_search($last_element, $properties);
            }

            foreach($tabs as $tab)
            {
                if(empty($tab['properties']))
                {
                    continue;
                }

                foreach($tab['properties'] as $property)
                {
                    if(!empty($property['identifier']) and $identifier == $property['identifier'])
                    {
                        return $this->_returnJson(array('success' => FALSE, 'message' => 'Identifier already exists'));
                    }
                }
            }

            $current_id = $last_id + 1;
            $properties[$current_id] = array(
                'name' => $name
                , 'identifier' => $identifier
                , 'tab' => $tab_id
                , 'description' => $description
                , 'is_required' => $is_required == 1 ? TRUE : FALSE
                , 'datatype' => $datatype_id
            );

            $session['document-type']['tabs'][$tab_id]['properties'] = $properties;
            $properties[$current_id]['success'] = TRUE;
            $properties[$current_id]['id'] = $current_id;

            return $this->_returnJson($properties[$current_id]);
        }

        return $this->_returnJson(array('success' => FALSE, 'message' => 'Error'));
    }


    public function deletePropertyAction()
    {
        if($this->getRequest()->isPost())
        {
            $id = $this->getRequest()->post()->get('property');
            $session = $this->getSession();

            foreach($session['document-type']['tabs'] as $tab_id => $tab)
            {
                if(empty($tab['properties']))
                {
                    continue;
                }

                if(array_key_exists($id, $tab['properties']))
                {
                    unset($session['document-type']['tabs'][$tab_id]['properties'][$id]);
                    return $this->_returnJson(array('success' => TRUE, 'message' => 'Property successfullty deleted'));
                }
            }
        }

        return $this->_returnJson(array('success' => FALSE, 'message' => 'Error'));
    }
}
