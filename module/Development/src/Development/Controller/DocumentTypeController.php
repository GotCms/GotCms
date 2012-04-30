<?php

namespace Development\Controller;

use Gc\Mvc\Controller\Action,
    Development\Form\DocumentType as DocumentTypeForm,
    Gc\Property,
    Gc\Tab,
    Gc\DocumentType;

class DocumentTypeController extends Action
{
    protected $_session = NULL;

    public function init()
    {
        /*$contextSwitch = $this->_helper->getHelper('contextSwitch');
        $contextSwitch->createActionContext('add-tab', 'json')
            ->createActionContext('add-property', 'json')
            ->setAutoJsonSerialization(true)
            ->initContext();*/
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
        $session = $this->getSession()->toArray();

        if($request->isPost())
        {
            if(!empty($session['document-type']))
            {
                $form->setValueFromSession($session['document-type']);
            }

            if($form->isValid($this->getRequest()->post()->toArray()))
            {
                $document_type = new DocumentType\Model();
                $property_collection = new Gc\Property\Collection();

                $infos_subform = $form->getSubForm('infos');
                $views_subform = $form->getSubForm('views');
                $tabs_subform = $form->getSubForm('tabs');

                $document_type->addData(array(
                    'user_id' => $this->getAuth()->getIdentity()->id
                    , 'name' => $infos_subform->getValue('name')
                    , 'description' => $infos_subform->getValue('description')
                    , 'default_view_id' => $views_subform->getValue('default_view')
                ));

                $this->getAdapter()->beginTransaction();
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
                        $t = Gc\Tab\Model::fromArray($tab);
                        $t->setDocumentTypeId($document_type->getId());
                        $t->setOrder(++$idx);
                        $t->save();
                        $tabs[$tab_id] = $t->getId();
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

                    $this->getAdapter()->commit();
                }
                catch(Exception $e)
                {
                    $this->getAdapter()->rollBack();
                    throw new \Gc\Exception("Error Processing Request ".print_r($e, TRUE), 1);
                }
            }
            else
            {
                $this->flashMessenger()->setNameSpace('error')->addMessage('Can save document_type');
            }
        }

        /*if(!empty($session['document-type']))
        {
            $session->clear('document-type');
        }*/


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
            $session = new Zend_Session_Namespace('documentType');
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

                    $this->_helper->json(array('success' => FALSE, 'message' => 'Already exists'));
                    return;
                }
            }

            $current_id = $last_id + 1;
            $tabs[$current_id] = array('name' => $name, 'description' => $description);
            $session['document-type']['tabs'] = $tabs;

            return $this->_helper->json(array(
                'success' => TRUE
                , 'id' => $current_id
                , 'name' => $name
                , 'description' => $description
            ));
        }

        return $this->_helper->json(array('success' => FALSE, 'message' => 'Error'));
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
                return $this->_helper->json(array('success' => TRUE, 'message' => 'Tab successfullty deleted'));

            }
        }

        return $this->_helper->json(array('success' => FALSE, 'message' => 'Error'));
    }

    public function addPropertyAction()
    {
        if($this->getRequest()->isPost())
        {
            $session = $this->getSession();

            $name = $this->getRequest()->post()->get('name');
            $identifier = $this->getRequest()->post()->get('identifier');
            $tab_id = $this->getRequest()->post()->get('tab');
            $description = $this->getRequest()->post()->get('description');
            $is_required = $this->getRequest()->post()->get('is_required');
            $datatype_id = $this->getRequest()->post()->get('datatype');

            $tabs = $session['document-type']['tabs'];

            if(empty($session['document-type']['tabs'][$tab_id]))
            {
                $this->_helper->json(array('success' => FALSE, 'message' => 'Tab does not exists'));
                return;
            }

            $tab = $session['document-type']['tabs'][$tab_id];
            if(empty($tab['properties']))
            {
                $tab['properties'] = array();
            }

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
                        return $this->_helper->json(array('success' => FALSE, 'message' => 'Identifier already exists'));
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

            return $this->_helper->json($properties[$current_id]);
        }

        return $this->_helper->json(array('success' => FALSE, 'message' => 'Error'));
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
                    return $this->_helper->json(array('success' => TRUE, 'message' => 'Property successfullty deleted'));
                }
            }
        }

        return $this->_helper->json(array('success' => FALSE, 'message' => 'Error'));
    }
}
