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
 * @package    GcContent
 * @subpackage Controller
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace GcContent\Controller;

use Gc\Component;
use Gc\Datatype;
use Gc\DocumentType;
use Gc\Document\Collection as DocumentCollection;
use Gc\Document\Model as DocumentModel;
use Gc\Property;
use GcContent\Form;
use Exception;

/**
 * Document controller
 *
 * @category   Gc_Application
 * @package    GcContent
 * @subpackage Controller
 */
class DocumentController extends AbstractController
{
    /**
     * Contains information about acl
     *
     * @var array
     */
    protected $aclPage = array('resource' => 'content', 'permission' => 'document');

    /**
     * Create document
     *
     * @return \Zend\View\Model\ViewModel|array
     */
    public function createAction()
    {
        $documentForm = new Form\DocumentInformation();
        $parentId     = $this->getRouteMatch()->getParam('id');
        if (!empty($parentId)) {
            $routeName = 'content/document/create-w-parent';
        } else {
            $routeName = 'content/document/create';
        }

        $documentForm->setAttribute('action', $this->url()->fromRoute($routeName, array('id' => $parentId)));

        $documentTypeCollection = new DocumentType\Collection();
        $documentTypeElement    = $documentForm->get('document_type');
        if (empty($parentId)) {
            $documentTypeElement->setValueOptions(
                array('' => 'Select document type') + $documentTypeCollection->getSelect()
            );
        } else {
            $documentForm->get('parent')->setValue($parentId);
            $documentTypeCollection->init(DocumentModel::fromId($parentId)->getDocumentTypeId());
            $documentTypeElement->setValueOptions(
                array('' => 'Select document type') + $documentTypeCollection->getSelect()
            );
        }

        if ($this->getRequest()->isPost()) {
            $documentForm->getInputFilter()->add(
                array(
                    'required' => true,
                    'validators' => array(
                        array(
                            'name' => 'not_empty'
                        ),
                    ),
                ),
                'document_type'
            );
            $documentForm->setData($this->getRequest()->getPost()->toArray());
            if (!$documentForm->isValid()) {
                $this->flashMessenger()->addErrorMessage('Invalid document data');
                $this->useFlashMessenger();
            } else {
                $documentName   = $documentForm->getValue('document-name');
                $documentUrlKey = $documentForm->getValue('document-url_key');
                $documentTypeId = $documentForm->getValue('document_type');
                $parentId       = $documentForm->getValue('parent');
                $document       = new DocumentModel();
                $document->setName($documentName)
                    ->setDocumentTypeId($documentTypeId)
                    ->setParentId($parentId)
                    ->setUrlKey(!empty($documentUrlKey) ? $documentUrlKey : $this->checkUrlKey($documentName))
                    ->setUserId($this->getServiceLocator()->get('Auth')->getIdentity()->getId());

                $document->save();
                $this->flashMessenger()->addSuccessMessage('This document has been saved');
                $this->redirect()->toRoute('content/document/edit', array('id' => $document->getId()));
            }
        }

        return array('form' => $documentForm);
    }

    /**
     * Delete document
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function deleteAction()
    {
        $document = DocumentModel::fromId($this->getRouteMatch()->getParam('id', ''));
        if (empty($document)) {
            return $this->returnJson(array('success' => false, 'message' => 'Document does not exists!'));
        } else {
            try {
                if ($document->delete()) {
                    return $this->returnJson(
                        array(
                            'success' => true,
                            'message' => 'This document was succefully delete'
                        )
                    );
                }
            } catch (Exception $e) {
                throw new \Gc\Exception($e->getMessage(), $e->getCode(), $e);
            }
        }

        return $this->returnJson(
            array(
                'success' => false
                , 'message' => 'There were problems during the removal of this document'
            )
        );
    }

    /**
     * Edit Document
     *
     * @return \Zend\View\Model\ViewModel|array
     */
    public function editAction()
    {
        $documentId = $this->getRouteMatch()->getParam('id');
        $document   = DocumentModel::fromId($documentId);
        if (empty($document)) {
            $this->flashMessenger()->addErrorMessage('Document does not exists!');
            return $this->redirect()->toRoute('content');
        }

        $documentForm = new Form\Document();
        $documentForm->init(
            $this->url()->fromRoute('content/document/edit', array('id' => $documentId))
        );

        $hasError       = false;
        $documentTypeId = $document->getDocumentTypeId();
        $oldUrlKey      = $document->getUrlKey();

        if ($this->getRequest()->isPost()) {
            $documentVars = $this->getRequest()->getPost()->toArray();
            $document->setName(
                empty($documentVars['document-name']) ?
                $document->getName() :
                $documentVars['document-name']
            );
            $document->setStatus(
                empty($documentVars['document-status']) ?
                DocumentModel::STATUS_DISABLE :
                DocumentModel::STATUS_ENABLE
            );
            $document->showInNav(
                empty($documentVars['document-show_in_nav']) ?
                false :
                $documentVars['document-show_in_nav']
            );
            $document->canBeCached(
                empty($documentVars['document-can_be_cached']) ?
                false :
                $documentVars['document-can_be_cached']
            );
            $document->setLayoutId(
                empty($documentVars['document-layout']) ?
                false :
                $documentVars['document-layout']
            );
            $document->setViewId(
                empty($documentVars['document-view']) ?
                $document->getViewId() :
                $documentVars['document-view']
            );
            $document->setUrlKey(
                empty($documentVars['document-url_key']) ?
                '' :
                $documentVars['document-url_key']
            );

            $tabs = $documentForm->loadTabs($documentTypeId);
            foreach ($tabs as $tab) {
                $properties = $documentForm->loadProperties($documentTypeId, $tab->getId(), $document->getId());
                $connection = $document->getAdapter()->getDriver()->getConnection();
                try {
                    $connection->beginTransaction();
                    foreach ($properties as $property) {
                        $property->setDocumentId($document->getId())->loadValue();
                        if (!Datatype\Model::saveEditor($this->getServiceLocator(), $property)) {
                            $hasError = true;
                        }
                    }

                    if ($hasError) {
                        $connection->rollBack();
                    } else {
                        $connection->commit();
                    }
                } catch (Exception $e) {
                    $connection->rollBack();
                }
            }
        }


        $tabsArray   = $documentForm->load($documentTypeId, $document, $this->getServiceLocator());
        $tabsArray[] = $this->getServiceLocator()->get('MvcTranslator')->translate('Document information');

        $formDocumentAdd = $documentForm->get('tabs-' . count($tabsArray));
        if ($this->getRequest()->isPost()) {
            $formDocumentAdd->setData($this->getRequest()->getPost()->toArray());

            if ($hasError or !$formDocumentAdd->isValid()) {
                $document->setStatus(DocumentModel::STATUS_DISABLE);
                $document->setUrlKey($oldUrlKey);
                $this->flashMessenger()->addErrorMessage(
                    'This document cannot be saved because one or more properties values are required !'
                );
                $this->useFlashMessenger();
            } else {
                $this->flashMessenger()->addSuccessMessage('This document has been saved');
                $document->addData($formDocumentAdd->getInputFilter()->getValues());
                $document->save();

                return $this->redirect()->toRoute('content/document/edit', array('id' => $documentId));
            }
        }

        $this->layout()->setVariable('documentId', $documentId);
        return array('form' => $documentForm, 'tabs' => new Component\Tabs($tabsArray), 'document' => $document);
    }

    /**
     * Copy document
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function copyAction()
    {
        $documentId = $this->getRouteMatch()->getParam('id');
        if (empty($documentId)) {
            return $this->returnJson(array('success' => false));
        }

        $session = $this->getSession();
        if (!empty($session['document-cut'])) {
            unset($session['document-cut']);
        }

        $session['document-copy'] = $documentId;
        return $this->returnJson(array('success' => true));
    }

    /**
     * Cut document
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function cutAction()
    {
        $documentId = $this->getRouteMatch()->getParam('id');
        if (empty($documentId)) {
            return $this->returnJson(array('success' => false));
        }

        $session = $this->getSession();
        if (!empty($session['document-copy'])) {
            unset($session['document-copy']);
        }

        $session['document-cut'] = $documentId;
        return $this->returnJson(array('success' => true));
    }

    /**
     * Paste document
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function pasteAction()
    {
        $parentId = $this->getRouteMatch()->getParam('id', null);
        $session  = $this->getSession();
        if (!empty($parentId)) {
            $parentDocument = DocumentModel::fromId($parentId);
            if (empty($parentDocument)) {
                return $this->returnJson(array('success' => false));
            }
        }

        if (!empty($session['document-cut'])) {
            $document = DocumentModel::fromId($session['document-cut']);
            if (empty($document)) {
                return $this->returnJson(array('success' => false));
            }

            if (!empty($parentDocument)) {
                $availableChildren = $parentDocument->getDocumentType()->getDependencies();
                if (!in_array($document->getDocumentType()->getId(), $availableChildren)) {
                    return $this->returnJson(array('success' => false));
                }
            }

            $searchDocument = DocumentModel::fromUrlKey($document->getUrlKey(), $parentId);
            if (!empty($searchDocument)) {
                return $this->returnJson(array('success' => false));
            }

            $document->setParentId($parentId);
            $document->save();
            unset($session['document-cut']);
            return $this->returnJson(array('success' => true));
        } elseif (!empty($session['document-copy'])) {
            $urlKey         = $this->getRequest()->getQuery('url_key');
            $searchDocument = DocumentModel::fromUrlKey($urlKey, $parentId);
            if (!empty($searchDocument)) {
                return $this->returnJson(array('success' => false));
            }

            $document = DocumentModel::fromId($session['document-copy']);
            if (empty($document)) {
                return $this->returnJson(array('success' => false));
            }

            if (!empty($parentDocument)) {
                $availableChildren = $parentDocument->getDocumentType()->getDependencies();
                if (!in_array($document->getDocumentType()->getId(), $availableChildren)) {
                    return $this->returnJson(array('success' => false));
                }
            }

            $copyDocument           = new DocumentModel();
            $copyDocumentProperties = new Property\Collection();
            $copyDocumentProperties->load(null, null, $document->getId());

            $copyDocument->addData($document->getData());
            $copyDocument->setData('id', null);
            $copyDocument->setParentId($parentId);
            $copyDocument->setName($this->getRequest()->getQuery('name'));
            $copyDocument->setUrlKey($urlKey);
            $copyDocument->save();

            foreach ($copyDocumentProperties->getProperties() as $property) {
                $value = $property->getValueModel();
                if (empty($value)) {
                    continue;
                }

                $copyProperty = new Property\Value\Model();
                $copyProperty->addData($value->getData());
                $copyProperty->setData('id', null);
                $copyProperty->setDocumentId($copyDocument->getId());
                $copyProperty->save();
            }

            return $this->returnJson(array('success' => true));
        } else {
            return $this->returnJson(array('success' => false));
        }
    }

    /**
     * Defined status to document
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function statusAction()
    {
        $status     = $this->getRouteMatch()->getParam('status', null);
        $documentId = $this->getRouteMatch()->getParam('id', null);
        $document   = DocumentModel::fromId($documentId);
        if (empty($document) or $status === null) {
            return $this->returnJson(array('success' => false));
        }

        $document->setStatus($status);
        $document->save();

        return $this->returnJson(array('success' => true));
    }

    /**
     * Refresh treeview
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function refreshTreeviewAction()
    {
        $documentId = $this->getRouteMatch()->getParam('id', 0);
        if (empty($documentId)) {
            $documents = new DocumentCollection();
            $documents->load($documentId);
            $documentsList = $documents->getChildren();
        } else {
            $documents     = DocumentModel::fromId($documentId);
            $documentsList = $documents->getChildren();
        }


        return $this->returnJson(array('treeview' => Component\TreeView::render($documentsList, false)));
    }

    /**
     * Sort document action
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function sortOrderAction()
    {
        $order = $this->getRequest()->getPost()->get('order');
        $list  = explode(',', str_replace('document_', '', $order));

        foreach ($list as $order => $documentId) {
            $documentModel = DocumentModel::fromId($documentId);
            if (!empty($documentModel)) {
                $documentModel->setSortOrder($order);
                $documentModel->save();
            }
        }

        return $this->returnJson(array('success' => true));
    }

    /**
     * Check url key with deleted space and special chars
     *
     * @param string $string Url key
     *
     * @return string
     */
    protected function checkUrlKey($string)
    {
        $charMap = array(
            'Š'=>'S',
            'š'=>'s',
            'Ð'=>'Dj',
            'Ž'=>'Z',
            'ž'=>'z',
            'À'=>'A',
            'Á'=>'A',
            'Â'=>'A',
            'Ã'=>'A',
            'Ä'=>'Ae',
            'Å'=>'A',
            'Æ'=>'A',
            'Ç'=>'C',
            'È'=>'E',
            'É'=>'E',
            'Ê'=>'E',
            'Ë'=>'E',
            'Ì'=>'I',
            'Í'=>'I',
            'Î'=>'I',
            'Ï'=>'I',
            'Ñ'=>'N',
            'Ò'=>'O',
            'Ó'=>'O',
            'Ô'=>'O',
            'Õ'=>'O',
            'Ö'=>'Oe',
            'Ø'=>'O',
            'Ü'=>'Ue',
            'Ù'=>'U',
            'Ú'=>'U',
            'Û'=>'U',
            'Ý'=>'Y',
            'Þ'=>'B',
            'ß'=>'ss',
            'à'=>'a',
            'á'=>'a',
            'â'=>'a',
            'ã'=>'a',
            'ä'=>'ae',
            'å'=>'a',
            'æ'=>'a',
            'ç'=>'c',
            'è'=>'e',
            'é'=>'e',
            'ê'=>'e',
            'ë'=>'e',
            'ì'=>'i',
            'í'=>'i',
            'î'=>'i',
            'ï'=>'i',
            'ð'=>'o',
            'ñ'=>'n',
            'ò'=>'o',
            'ó'=>'o',
            'ô'=>'o',
            'õ'=>'o',
            'ö'=>'oe',
            'ø'=>'o',
            'ü'=>'ue',
            'ù'=>'u',
            'ú'=>'u',
            'û'=>'u',
            'ý'=>'y',
            'ý'=>'y',
            'þ'=>'b',
            'ÿ'=>'y',
            'ƒ'=>'f',
            'Ŕ'=>'R',
            'ŕ'=>'r'
        );

        $string = strtr(mb_strtolower($string, 'utf-8'), $charMap);
        $string = preg_replace('~[^a-zA-Z0-9-]~', '-', $string);

        return $string;
    }
}
