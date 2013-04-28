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
 * @package    Development
 * @subpackage Controller
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Development\Controller;

use Development\Form\DocumentType as DocumentTypeForm;
use Gc\DocumentType;
use Gc\Mvc\Controller\Action;
use Zend\Validator;
use Gc\Property;
use Gc\Tab;
use Exception;

/**
 * Document type controller
 *
 * @category   Gc_Application
 * @package    Development
 * @subpackage Controller
 */
class DocumentTypeController extends Action
{
    /**
     * Contains information about acl
     *
     * @var array $aclPage
     */
    protected $aclPage = array('resource' => 'Development', 'permission' => 'document-type');

    /**
     * List all document types
     *
     * @return \Zend\View\Model\ViewModel|array
     */
    public function indexAction()
    {
        $documents = new DocumentType\Collection();
        return array('documents' => $documents->getDocumentTypes());
    }

    /**
     * Create document type
     *
     * @return \Zend\View\Model\ViewModel|array
     */
    public function createAction()
    {
        $form = new DocumentTypeForm();
        $form->setAttribute('action', $this->url()->fromRoute('documentTypeCreate', array()));
        $request = $this->getRequest();
        $session = $this->getSession();

        if (!$request->isPost()) {
            $session['document-type'] = array('tabs' => array());
        } else {
            $postData = $this->getRequest()->getPost()->toArray();
            $form->setData($postData);
            $form->setValues($postData);
            if (!$form->isValid()) {
                $this->flashMessenger()->addErrorMessage('Can not save document type');
                $this->useFlashMessenger();
            } else {
                $propertyCollection = new Property\Collection();
                $input              = $form->getInputFilter();
                $infosSubform       = $input->get('infos');
                $viewsSubform       = $input->get('views');
                $tabsSubform        = $input->get('tabs');
                $propertiesSubform  = $input->get('properties');
                $documentType       = new DocumentType\Model();

                $documentType->addData(
                    array(
                        'name'            => $infosSubform->getValue('name'),
                        'icon_id'         => $infosSubform->getValue('icon_id'),
                        'description'     => $infosSubform->getValue('description'),
                        'default_view_id' => $viewsSubform->getValue('default_view'),
                        'user_id'         => $this->getAuth()->getIdentity()->getId(),
                    )
                );

                $documentType->getAdapter()->getDriver()->getConnection()->beginTransaction();
                try {
                    $availableViews = $viewsSubform->getValue('available_views');
                    if (empty($availableViews)) {
                        $availableViews = array();
                    }

                    $documentType->addViews($availableViews);
                    $documentType->setDependencies($infosSubform->getValue('dependency'));
                    $documentType->save();

                    $tabsArray    = array();
                    $existingTabs = array();
                    $idx          = 0;

                    foreach ($tabsSubform->getValidInput() as $tabId => $tabValues) {
                        if (!preg_match('~^tab(\d+)$~', $tabId, $matches)) {
                            continue;
                        }

                        $tabId    = $matches[1];
                        $tabModel = new Tab\Model();

                        $tabModel->setDescription($tabValues->getValue('description'));
                        $tabModel->setName($tabValues->getValue('name'));
                        $tabModel->setDocumentTypeId($documentType->getId());
                        $tabModel->setSortOrder(++$idx);
                        $tabModel->save();
                        $existingTabs[$tabId] = $tabModel->getId();
                    }

                    $idx = 0;
                    foreach ($propertiesSubform->getValidInput() as $propertyId => $propertyValues) {
                        if (!preg_match('~^property(\d+)$~', $propertyId, $matches)) {
                            continue;
                        }

                        $propertyId    = $matches[1];
                        $propertyModel = new Property\Model();

                        $propertyModel->setDescription($propertyValues->getValue('description'));
                        $propertyModel->setName($propertyValues->getValue('name'));
                        $propertyModel->setIdentifier($propertyValues->getValue('identifier'));
                        $propertyModel->setTabId($existingTabs[$propertyValues->getValue('tab')]);
                        $propertyModel->setDatatypeId($propertyValues->getValue('datatype'));
                        $required = $propertyValues->getValue('required');
                        $propertyModel->isRequired(!empty($required) ? true : false);
                        $propertyModel->setSortOrder(++$idx);
                        $propertyModel->save();
                    }

                    $documentType->getAdapter()->getDriver()->getConnection()->commit();

                    $this->flashMessenger()->addSuccessMessage('This document type has been saved');
                    return $this->redirect()->toRoute('documentTypeEdit', array('id' => $documentType->getId()));
                } catch (Exception $e) {
                    $documentType->getAdapter()->getDriver()->getConnection()->rollBack();
                    throw new \Gc\Exception('Error Processing Request ' . print_r($e, true), 1);
                }
            }
        }

        return array('form' => $form);
    }

    /**
     * Edit document type
     *
     * @return \Zend\View\Model\ViewModel|array
     */
    public function editAction()
    {
        $documentTypeId = $this->getRouteMatch()->getParam('id');
        $documentType   = DocumentType\Model::fromId($documentTypeId);
        if (empty($documentType)) {
            return $this->redirect()->toRoute('documentTypeCreate');
        }

        $form = new DocumentTypeForm();
        $form->setAttribute('action', $this->url()->fromRoute('documentTypeEdit', array('id' => $documentTypeId)));
        $request = $this->getRequest();
        $session = $this->getSession();


        if (!$request->isPost()) {
            $form->setValues($documentType);

            $documentTypeSession = array(
                'tabs' => array(),
                'max-property-id' => 0,
                'max-tab-id' => 0,
            );

            foreach ($documentType->getTabs() as $tab) {
                $documentTypeSession['tabs'][$tab->getId()] = array(
                    'name' => $tab->getName(),
                    'description' => $tab->getDescription(),
                    'properties' => array(),
                );

                if ($tab->getId() > $documentTypeSession['max-tab-id']) {
                    $documentTypeSession['max-tab-id'] = $tab->getId();
                }

                foreach ($tab->getProperties() as $property) {
                    $documentTypeSession['tabs'][$tab->getId()]['properties'][$property->getId()] = array(
                        'name' => $property->getName(),
                        'identifier' => $property->getIdentifier(),
                        'tab' => $property->getTabId(),
                        'description' => $property->getDescription(),
                        'is_required' => $property->isRequired(),
                        'datatype' => $property->getDatatypeId(),
                    );

                    if ($property->getId() > $documentTypeSession['max-property-id']) {
                        $documentTypeSession['max-property-id'] = $property->getId();
                    }
                }
            }

            $session['document-type'] = $documentTypeSession;
        } else {
            $validators = $form->getInputFilter()->get('infos')->get('name')->getValidatorChain()->getValidators();

            foreach ($validators as $validator) {
                if ($validator['instance'] instanceof Validator\Db\NoRecordExists) {
                    $validator['instance']->setExclude(array('field' => 'id', 'value' => $documentType->getId()));
                }
            }

            $postData = $this->getRequest()->getPost()->toArray();
            $form->setData($postData);
            $form->setValues($postData);
            if (!$form->isValid()) {
                $this->flashMessenger()->addErrorMessage('Can not save document type');
                $this->useFlashMessenger();
            } else {
                $propertyCollection = new Property\Collection();
                $input              = $form->getInputFilter();
                $infosSubform       = $input->get('infos');
                $viewsSubform       = $input->get('views');
                $tabsSubform        = $input->get('tabs');
                $propertiesSubform  = $input->get('properties');

                $documentType->addData(
                    array(
                        'name' => $infosSubform->getValue('name'),
                        'icon_id' => $infosSubform->getValue('icon_id'),
                        'description' => $infosSubform->getValue('description'),
                        'default_view_id' => $viewsSubform->getValue('default_view'),
                    )
                );

                $documentType->getAdapter()->getDriver()->getConnection()->beginTransaction();
                try {
                    $availableViews = $viewsSubform->getValue('available_views');
                    if (empty($availableViews)) {
                        $availableViews = array();
                    }

                    $documentType->addViews($availableViews);
                    $documentType->setDependencies($infosSubform->getValue('dependency'));
                    $documentType->save();

                    $tabsArray    = array();
                    $existingTabs = array();
                    $idx          = 0;

                    foreach ($tabsSubform->getValidInput() as $tabId => $tabValues) {
                        if (!preg_match('~^tab(\d+)$~', $tabId, $matches)) {
                            continue;
                        }

                        $tabId    = $matches[1];
                        $tabModel = Tab\Model::fromId($tabId);
                        if (empty($tabModel) or $tabModel->getDocumentTypeId() != $documentType->getId()) {
                            $tabModel = new Tab\Model();
                        }

                        $tabModel->setDescription($tabValues->getValue('description'));
                        $tabModel->setName($tabValues->getValue('name'));
                        $tabModel->setDocumentTypeId($documentType->getId());
                        $tabModel->setSortOrder(++$idx);
                        $tabModel->save();
                        $existingTabs[$tabId] = $tabModel->getId();
                    }

                    $tabCollection = new Tab\Collection();
                    $tabs          = $tabCollection->load($documentType->getId())->getTabs();
                    foreach ($tabs as $tab) {
                        if (!in_array($tab->getId(), $existingTabs)) {
                            $tab->delete();
                        }
                    }

                    $existingProperties = array();
                    $idx                = 0;
                    foreach ($propertiesSubform->getValidInput() as $propertyId => $propertyValues) {
                        if (!preg_match('~^property(\d+)$~', $propertyId, $matches)) {
                            continue;
                        }

                        $propertyId    = $matches[1];
                        $propertyModel = Property\Model::fromId($propertyId);
                        if (empty($propertyModel) or !in_array($propertyModel->getTabId(), $existingTabs)) {
                            $propertyModel = new Property\Model();
                        }

                        $propertyModel->setDescription($propertyValues->getValue('description'));
                        $propertyModel->setName($propertyValues->getValue('name'));
                        $propertyModel->setIdentifier($propertyValues->getValue('identifier'));
                        $propertyModel->setTabId($existingTabs[$propertyValues->getValue('tab')]);
                        $propertyModel->setDatatypeId($propertyValues->getValue('datatype'));
                        $required = $propertyValues->getValue('required');
                        $propertyModel->isRequired(!empty($required) ? true : false);
                        $propertyModel->setSortOrder(++$idx);
                        $propertyModel->save();
                        $existingProperties[] = $propertyModel->getId();
                    }

                    $propertyCollection = new Property\Collection();
                    $properties         = $propertyCollection->load($documentType->getId())->getProperties();
                    foreach ($properties as $property) {
                        if (!in_array($property->getId(), $existingProperties)) {
                            $property->delete();
                        }
                    }

                    $documentType->getAdapter()->getDriver()->getConnection()->commit();

                    $this->flashMessenger()->addSuccessMessage('This document type has been saved');
                    return $this->redirect()->toRoute('documentTypeEdit', array('id' => $documentTypeId));
                } catch (Exception $e) {
                    $documentType->getAdapter()->getDriver()->getConnection()->rollBack();
                    throw new \Gc\Exception('Error Processing Request ' . print_r($e, true), 1);
                }
            }
        }

        return array('form' => $form);
    }

    /**
     * Add tab in session
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function importTabAction()
    {
        if ($this->getRequest()->isPost()) {
            $tabId          = $this->getRequest()->getPost()->get('tab_id');
            $tabModel       = Tab\Model::fromId($tabId);
            $propertiesList = $tabModel->getProperties();


            $properties = array();
            foreach ($propertiesList as $property) {
                $properties[] = array(
                    'name' => $property->getName(),
                    'identifier' => $property->getIdentifier(),
                    'description' => $property->getDescription(),
                    'datatype' => $property->getDatatypeId(),
                    'is_required' => $property->isRequired()
                );
            }

            $tab = array(
                'name' => $tabModel->getName(),
                'description' => $tabModel->getDescription(),
                'properties' => $properties
            );

            return $this->returnJson(
                array(
                    'success' => true,
                    'tab' => $tab
                )
            );
        }

        return $this->returnJson(array('success' => false, 'message' => 'Error'));
    }

    /**
     * Delete Document type
     *
     * @return \Zend\View\Model\ViewModel|array
     */
    public function deleteAction()
    {
        $documentType = DocumentType\Model::fromId($this->getRouteMatch()->getParam('id', null));
        if (!empty($documentType) and $documentType->delete()) {
            return $this->returnJson(array('success' => true, 'message' => 'This document type has been deleted'));
        }

        return $this->returnJson(array('success' => false, 'message' => 'Document type does not exists'));
    }

    /**
     * Add tab in session
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function addTabAction()
    {
        if ($this->getRequest()->isPost()) {
            $session             = $this->getSession();
            $name                = $this->getRequest()->getPost()->get('name');
            $description         = $this->getRequest()->getPost()->get('description');
            $documentTypeSession = $session['document-type'];

            $tabs = empty($documentTypeSession['tabs']) ? array() : $documentTypeSession['tabs'];

            foreach ($tabs as $tab) {
                if ($name == $tab['name']) {
                    return $this->returnJson(array('success' => false, 'message' => 'Already exists'));
                }
            }

            $lastId    = empty($documentTypeSession['max-tab-id']) ? 0 : $documentTypeSession['max-tab-id'];
            $currentId = $lastId + 1;

            $documentTypeSession['max-tab-id'] = $currentId;
            $tabs[$currentId]                  = array(
                'name' => $name,
                'description' => $description,
                'properties' => array()
            );
            $documentTypeSession['tabs']       = $tabs;
            $session['document-type']          = $documentTypeSession;

            return $this->returnJson(
                array(
                    'success' => true,
                    'id' => $currentId,
                    'name' => $name,
                    'description' => $description,
                )
            );
        }

        return $this->returnJson(array('success' => false, 'message' => 'Error'));
    }

    /**
     * Delete tab in session
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function deleteTabAction()
    {
        if ($this->getRequest()->isPost()) {
            $session = $this->getSession();
            $id      = $this->getRequest()->getPost()->get('tab');

            $tabs = empty($session['document-type']) ? array() : $session['document-type']['tabs'];
            if (array_key_exists($id, $tabs)) {
                $documentTypeSession = $session['document-type'];
                unset($documentTypeSession['tabs'][$id]);
                $session->offsetSet('document-type', $documentTypeSession);

                return $this->returnJson(array('success' => true, 'message' => 'This tab has been deleted'));
            }
        }

        return $this->returnJson(array('success' => false, 'message' => 'Error'));
    }

    /**
     * Add property in session
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function addPropertyAction()
    {
        if ($this->getRequest()->isPost()) {
            $post        = $this->getRequest()->getPost();
            $name        = $post->get('name');
            $identifier  = $post->get('identifier');
            $tabId       = $post->get('tab');
            $description = $post->get('description');
            $isRequired  = $post->get('is_required');
            $datatypeId  = $post->get('datatype');

            $session = $this->getSession();

            $documentTypeSession = $session['document-type'];
            $tabs                = $documentTypeSession['tabs'];

            if (empty($documentTypeSession['tabs'][$tabId])) {
                return $this->returnJson(array('success' => false, 'message' => 'Tab does not exists'));
            }

            $tab        = $documentTypeSession['tabs'][$tabId];
            $properties = $tab['properties'];

            foreach ($tabs as $tab) {
                if (empty($tab['properties'])) {
                    continue;
                }

                foreach ($tab['properties'] as $property) {
                    if (!empty($property['identifier']) and $identifier == $property['identifier']) {
                        return $this->returnJson(array('success' => false, 'message' => 'Identifier already exists'));
                    }
                }
            }

            $lastId = empty($documentTypeSession['max-property-id']) ? 0 : $documentTypeSession['max-property-id'];

            $currentId                              = $lastId + 1;
            $documentTypeSession['max-property-id'] = $currentId;
            $properties[$currentId]                 = array(
                'name' => $name,
                'identifier' => $identifier,
                'tab' => $tabId,
                'description' => $description,
                'is_required' => $isRequired == 1 ? true : false,
                'datatype' => $datatypeId,
            );

            $documentTypeSession['tabs'][$tabId]['properties'] = $properties;
            $session['document-type']                          = $documentTypeSession;
            $properties[$currentId]['success']                 = true;
            $properties[$currentId]['id']                      = $currentId;

            return $this->returnJson($properties[$currentId]);
        }

        return $this->returnJson(array('success' => false, 'message' => 'Error'));
    }

    /**
     * Delete property in session
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function deletePropertyAction()
    {
        if ($this->getRequest()->isPost()) {
            $id      = $this->getRequest()->getPost()->get('property');
            $session = $this->getSession();

            foreach ($session['document-type']['tabs'] as $tabId => $tab) {
                if (empty($tab['properties'])) {
                    continue;
                }

                if (array_key_exists($id, $tab['properties'])) {

                    $documentTypeSession = $session['document-type'];
                    unset($documentTypeSession['tabs'][$tabId]['properties'][$id]);
                    $session->offsetSet('document-type', $documentTypeSession);

                    return $this->returnJson(array('success' => true, 'message' => 'This property has been deleted'));
                }
            }
        }

        return $this->returnJson(array('success' => false, 'message' => 'Error'));
    }
}
