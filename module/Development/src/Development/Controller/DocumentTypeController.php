<?php
/**
 * This source file is part of Got CMS.
 *
 * Got CMS is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Got CMS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with Got CMS. If not, see <http://www.gnu.org/licenses/gpl-3.0.html>.
 *
 * PHP Version >=5.3
 *
 * @category Controller
 * @package  Development\Controller
 * @author   Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license  GNU/GPL http://www.gnu.org/licenses/gpl-3.0.html
 * @link     http://www.got-cms.com
 * @license  http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Development\Controller;

use Development\Form\DocumentType as DocumentTypeForm,
    Gc\DocumentType,
    Gc\Mvc\Controller\Action,
    Gc\Property,
    Gc\Tab;

class DocumentTypeController extends Action
{
    /**
     * Contains information about acl
     * @var array $_acl_page
     */
    protected $_acl_page = array('resource' => 'Development', 'permission' => 'document-type');

    /**
     * List all document types
     * @return \Zend\View\Model\ViewModel|array
     */
    public function indexAction()
    {
        $documents = new DocumentType\Collection();
        return array('documents' => $documents->getDocumentTypes());
    }

    /**
     * Create document type
     * @return \Zend\View\Model\ViewModel|array
     */
    public function createAction()
    {
        $document_type = new DocumentType\Model();

        $form = new DocumentTypeForm();
        $form->setView($this->getLocator()->get('view'));
        $form->setAction($this->url()->fromRoute('documentTypeCreate', array()));
        $request = $this->getRequest();
        $session = $this->getSession();

        if(!$request->isPost())
        {
            $session['document-type'] = array();
            $session['document-type']['tabs'] = array();
        }
        else
        {
            $post_data = $this->getRequest()->post()->toArray();
            if(!$form->isValid($post_data))
            {
                $this->flashMessenger()->setNameSpace('error')->addMessage('Can save document_type');
            }
            else
            {
                $property_collection = new Property\Collection();

                $infos_subform = $form->getSubForm('infos');
                $views_subform = $form->getSubForm('views');
                $tabs_subform = $form->getSubForm('tabs');

                $document_type->addData(array(
                    'user_id' => $this->getAuth()->getIdentity()->getId()
                    , 'name' => $infos_subform->getValue('name')
                    , 'description' => $infos_subform->getValue('description')
                    , 'default_view_id' => $views_subform->getValue('default_view')
                ));

                $document_type->getAdapter()->getDriver()->getConnection()->beginTransaction();
                try
                {
                    $document_type->addViews($views_subform->getValue('available_views'));
                    $document_type->save();
                    $document_type_id = $document_type->getId();

                    $values = $form->getValues();
                    $tabs_array = array();
                    $existing_tabs = array();
                    $idx = 0;
                    foreach($values['tabs'] as $tab_id => $tab_values)
                    {
                        if(!preg_match('~^tab(\d+)$~', $tab_id, $matches))
                        {
                            continue;
                        }

                        $tab_id = $matches[1];

                        $tab_model = Tab\Model::fromId($tab_id);
                        if(empty($tab_model) or $tab_model->getDocumentTypeId() != $document_type->getId())
                        {
                            $tab_model = new Tab\Model();
                        }

                        $tab_model->setDescription($tab_values['description']);
                        $tab_model->setName($tab_values['name']);
                        $tab_model->setDocumentTypeId($document_type->getId());
                        $tab_model->setOrder(++$idx);
                        $tab_model->save();
                        $existing_tabs[$tab_id] = $tab_model->getId();
                    }

                    $idx = 0;
                    foreach($values['properties'] as $property_id => $property_values)
                    {
                        if(!preg_match('~^property(\d+)$~', $property_id, $matches))
                        {
                            continue;
                        }

                        $property_id = $matches[1];

                        $property_model = Property\Model::fromId($property_id);
                        if(empty($property_model))
                        {
                            $property_model = new Property\Model();
                        }

                        $property_model->setDescription($property_values['description']);
                        $property_model->setName($property_values['name']);
                        $property_model->setIdentifier($property_values['identifier']);
                        $property_model->setTabId($existing_tabs[$property_values['tab']]);
                        $property_model->setDatatypeId($property_values['datatype']);
                        $property_model->isRequired(!empty($property_values['required']) ? TRUE : FALSE);
                        $property_model->setOrder(++$idx);
                        $property_model->save();
                    }

                    $document_type->getAdapter()->getDriver()->getConnection()->commit();

                    return $this->redirect()->toRoute('documentTypeEdit', array('id' => $document_type_id));
                }
                catch(Exception $e)
                {
                    $document_type->getAdapter()->getDriver()->getConnection()->rollBack();
                    throw new \Gc\Exception("Error Processing Request ".print_r($e, TRUE), 1);
                }
            }
        }

        return array('form' => $form);
    }

    /**
     * Edit document type
     * @return \Zend\View\Model\ViewModel|array
     */
    public function editAction()
    {
        $document_type_id = $this->getRouteMatch()->getParam('id');
        $document_type = DocumentType\Model::fromId($document_type_id);
        if(empty($document_type))
        {
            return $this->redirect()->toRoute('documentTypeCreate');
        }

        $form = new DocumentTypeForm();
        $form->setView($this->getLocator()->get('view'));
        $form->setAction($this->url()->fromRoute('documentTypeEdit', array('id' => $document_type_id)));
        $request = $this->getRequest();
        $session = $this->getSession();


        if(!$request->isPost())
        {
            $form->setValues($document_type);

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
        }
        else
        {
            if($validator = $form->getSubForm('infos')->getElement('name')->getValidator('Zend\Validator\Db\NoRecordExists'))
            {
                $validator->setExclude(array('field' => 'id', 'value' => $document_type->getId()));
            }

            $post_data = $this->getRequest()->post()->toArray();
            if(!$form->isValid($post_data))
            {
                $this->flashMessenger()->setNameSpace('error')->addMessage('Can save document_type');
                var_dump($post_data);
                var_dump($form->getErrors());
                die();
            }
            else
            {
                $property_collection = new Property\Collection();

                $infos_subform = $form->getSubForm('infos');
                $views_subform = $form->getSubForm('views');
                $tabs_subform = $form->getSubForm('tabs');

                $document_type->addData(array(
                    'name' => $infos_subform->getValue('name')
                    , 'description' => $infos_subform->getValue('description')
                    , 'default_view_id' => $views_subform->getValue('default_view')
                ));

                $document_type->getAdapter()->getDriver()->getConnection()->beginTransaction();
                try
                {
                    $document_type->addViews($views_subform->getValue('available_views'));
                    $document_type->save();

                    $values = $form->getValues();
                    $tabs_array = array();
                    $existing_tabs = array();
                    $idx = 0;
                    foreach($values['tabs'] as $tab_id => $tab_values)
                    {
                        if(!preg_match('~^tab(\d+)$~', $tab_id, $matches))
                        {
                            continue;
                        }

                        $tab_id = $matches[1];

                        $tab_model = Tab\Model::fromId($tab_id);
                        if(empty($tab_model) or $tab_model->getDocumentTypeId() != $document_type->getId())
                        {
                            $tab_model = new Tab\Model();
                        }

                        $tab_model->setDescription($tab_values['description']);
                        $tab_model->setName($tab_values['name']);
                        $tab_model->setDocumentTypeId($document_type->getId());
                        $tab_model->setOrder(++$idx);
                        $tab_model->save();
                        $existing_tabs[$tab_id] = $tab_model->getId();
                    }

                    $tab_collection = new Tab\Collection();
                    $tabs = $tab_collection->load($document_type->getId())->getTabs();
                    foreach($tabs as $tab)
                    {
                        if(!in_array($tab->getId(), $existing_tabs))
                        {
                            $tab->delete();
                        }
                    }

                    $idx = 0;
                    foreach($values['properties'] as $property_id => $property_values)
                    {
                        if(!preg_match('~^property(\d+)$~', $property_id, $matches))
                        {
                            continue;
                        }

                        $property_id = $matches[1];

                        $property_model = Property\Model::fromId($property_id);
                        if(empty($property_model))
                        {
                            $property_model = new Property\Model();
                        }

                        $property_model->setDescription($property_values['description']);
                        $property_model->setName($property_values['name']);
                        $property_model->setIdentifier($property_values['identifier']);
                        $property_model->setTabId($existing_tabs[$property_values['tab']]);
                        $property_model->setDatatypeId($property_values['datatype']);
                        $property_model->isRequired(!empty($property_values['required']) ? TRUE : FALSE);
                        $property_model->setOrder(++$idx);
                        $property_model->save();
                    }

                    $document_type->getAdapter()->getDriver()->getConnection()->commit();

                    return $this->redirect()->toRoute('documentTypeEdit', array('id' => $document_type_id));
                }
                catch(Exception $e)
                {
                    $document_type->getAdapter()->getDriver()->getConnection()->rollBack();
                    throw new \Gc\Exception("Error Processing Request ".print_r($e, TRUE), 1);
                }
            }
        }

        return array('form' => $form);
    }

    /**
     * Delete Document type
     * @return \Zend\View\Model\ViewModel|array
     */
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

    /**
     * Add tab in session
     * @return \Zend\View\Model\JsonModel
     */
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

    /**
     * Delete tab in session
     * @return \Zend\View\Model\JsonModel
     */
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
                $document_type = $session['document-type'];
                unset($document_type['tabs'][$id]);
                $session->offsetSet('document-type', $document_type);

                return $this->_returnJson(array('success' => TRUE, 'message' => 'Tab successfullty deleted'));
            }
        }

        return $this->_returnJson(array('success' => FALSE, 'message' => 'Error'));
    }

    /**
     * Add property in session
     * @return \Zend\View\Model\JsonModel
     */
    public function addPropertyAction()
    {
        if($this->getRequest()->isPost())
        {
            $post           = $this->getRequest()->post();
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

    /**
     * Delete property in session
     * @return \Zend\View\Model\JsonModel
     */
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

                    $document_type = $session['document-type'];
                    unset($document_type['tabs'][$tab_id]['properties'][$id]);
                    $session->offsetSet('document-type', $document_type);

                    return $this->_returnJson(array('success' => TRUE, 'message' => 'Property successfullty deleted'));
                }
            }
        }

        return $this->_returnJson(array('success' => FALSE, 'message' => 'Error'));
    }
}
