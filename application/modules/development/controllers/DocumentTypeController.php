<?php

class Development_DocumentTypeController extends Es_Controller_Action
{
    protected $_session = NULL;

    public function init()
    {
        $contextSwitch = $this->_helper->getHelper('contextSwitch');
        $contextSwitch->addActionContext('add-tab', 'json')
            ->addActionContext('add-property', 'json')
            ->setAutoJsonSerialization(true)
            ->initContext();
    }

    public function indexAction()
    {
        // action body
    }

    public function addAction()
    {
        $form = new Development_Form_DocumentType();
        $request = $this->getRequest();

        if($request->isPost())
        {

            if(Zend_Session::namespaceIsset('documentType'))
            {
                $form->setValueFromSession(Zend_Session::namespaceGet('documentType'));
            }

            if($form->isValid($this->_request->getPost()))
            {
                $document_type = new Es_Model_DbTable_DocumentType_Model();
                $property_collection = new Es_Model_DbTable_Property_Collection();

                $infos_subform = $form->getSubForm('infos');
                $views_subform = $form->getSubForm('views');
                $tabs_subform = $form->getSubForm('tabs');

                $document_type->addData(array(
                    //@TODO change user_id
                    'user_id' => 1
                    , 'name' => $infos_subform->getValue('name')
                    , 'description' => $infos_subform->getValue('description')
                    , 'default_view_id' => $views_subform->getValue('default_view')
                ));

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
                foreach($tabs_array as $tab_id => $tab)
                {
                    $t = Es_Model_DbTable_Tab_Model::fromArray($tab);
                    $t->setDocumentTypeId($document_type->getId());
                    $t->setOrder();
                    $t->save();
                    $tabs[$tab_id] = $t->getId();
                }

                $properties_array = array();
                $properties_values = $this->getRequest()->getPost('properties');
                foreach($properties_values as $property_name => $property_value)
                {

                    foreach($property_value as $id => $value)
                    {
                        if($property_name == 'tab')
                        {
                            $properties_array[$id]['tab_id'] = $tabs[$value];
                            continue;
                        }

                        $properties_array[$id][$property_name] = $value;
                    }
                }

                $property_collection->setProperties($properties_array);
                $property_collection->save();
            }
            else
            {
                echo 'Post'.PHP_EOL;
                var_dump($this->_request->getPost());
                echo 'FORM ERRORS'.PHP_EOL;
                var_dump($form->getErrors());
                die();
            }
        }

        if(Zend_Session::namespaceIsset('documentType'))
        {
            Zend_Session::namespaceUnset('documentType');
        }


        $this->view->form = $form;
    }

    public function listAction()
    {
        $documents = new Es_Model_DbTable_DocumentType_Collection();
        $this->view->documents = $documents->getDocumentTypes();
    }

    public function addTabAction()
    {
        if($this->_request->isPost())
        {
            $session = new Zend_Session_Namespace('documentType');
            $name = $this->_request->getPost('name');
            $description = $this->_request->getPost('description');

            $tabs = empty($session->tabs) ? array() : $session->tabs;
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
            $session->tabs = $tabs;

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
        if($this->_request->isPost())
        {
            $session = new Zend_Session_Namespace('documentType');
            $id = $this->_request->getPost('tab');
            $description = $this->_request->getPost('description');

            $tabs = empty($session->tabs) ? array() : $session->tabs;
            if(array_key_exists($id, $tabs))
            {
                unset($session->tabs[$id]);
                return $this->_helper->json(array('success' => TRUE, 'message' => 'Tab successfullty deleted'));

            }
        }

        return $this->_helper->json(array('success' => FALSE, 'message' => 'Error'));
    }

    public function addPropertyAction()
    {
        if($this->_request->isPost())
        {
            $session = new Zend_Session_Namespace('documentType');
            $name = $this->_request->getPost('name');
            $identifier = $this->_request->getPost('identifier');
            $tab_id = $this->_request->getPost('tab');
            $description = $this->_request->getPost('description');
            $is_required = $this->_request->getPost('is_required');
            $datatype_id = $this->_request->getPost('datatype');

            $tabs = $session->tabs;

            if(empty($session->tabs[$tab_id]))
            {
                $this->_helper->json(array('success' => FALSE, 'message' => 'Tab does not exists'));
                return;
            }

            $tab = $session->tabs[$tab_id];
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
                $last_id = array_search($last_element, $session->tabs);
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
                , 'is_required' => $is_required
                , 'datatype' => $datatype_id
            );
            $session->tabs[$tab_id]['properties'] = $properties;

            $this->_helper->json(array(
                'success' => TRUE
                , 'id' => $current_id
                , 'name' => $name
                , 'identifier' => $identifier
                , 'tab' => $tab_id
                , 'description' => $description
                , 'is_required' => $is_required
                , 'datatype' => $datatype_id
            ));
        }

        return $this->_helper->json(array('success' => FALSE, 'message' => 'Error'));
    }


    public function deletePropertyAction()
    {
        if($this->_request->isPost())
        {
            $id = $this->_request->getPost('property');
            $session = new Zend_Session_Namespace('documentType');
            foreach($session->tabs as $tab_id => $tab)
            {
                if(empty($tab['properties']))
                {
                    continue;
                }

                if(array_key_exists($id, $tab['properties']))
                {
                    unset($session->tabs[$tab_id]['properties'][$id]);
                    return $this->_helper->json(array('success' => TRUE, 'message' => 'Property successfullty deleted'));
                }
            }
        }

        return $this->_helper->json(array('success' => FALSE, 'message' => 'Error'));
    }
}
