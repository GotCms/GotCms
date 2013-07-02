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

use Gc\Mvc\Controller\Action;
use Development\Form\Datatype as DatatypeForm;
use Gc\Datatype;
use Zend\View\Model\ViewModel;
use Exception;

/**
 * Datatype controller
 *
 * @category   Gc_Application
 * @package    Development
 * @subpackage Controller
 */
class DatatypeController extends Action
{
    /**
     * Contains information about acl
     *
     * @var array $aclPage
     */
    protected $aclPage = array('resource' => 'Development', 'permission' => 'datatype');

    /**
     * List all datatypes
     *
     * @return \Zend\View\Model\ViewModel|array
     */
    public function indexAction()
    {
        $datatypes = new Datatype\Collection();

        return array('datatypes' => $datatypes->getDatatypes());
    }

    /**
     * Create Datatype
     *
     * @return \Zend\View\Model\ViewModel|array
     */
    public function createAction()
    {
        $datatype     = new Datatype\Model();
        $datatypeForm = new DatatypeForm();
        $datatypeForm->setAttribute('action', $this->url()->fromRoute('development/datatype/create'));
        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost()->toArray();
            $datatypeForm->setData($post);
            if (!$datatypeForm->isValid()) {
                $this->flashMessenger()->addErrorMessage('Can not save datatype');
                $this->useFlashMessenger();
            } else {
                $datatype->addData($datatypeForm->getInputFilter()->getValues());
                try {
                    $id = $datatype->save();
                    $this->flashMessenger()->addSuccessMessage('This datatype has been saved');
                    return $this->redirect()->toRoute('development/datatype/edit', array('id' => $id));
                } catch (Exception $e) {
                    throw new \Gc\Exception($e->getMessage(), $e->getCode(), $e);
                }
            }
        }

        return array('form' => $datatypeForm);
    }

    /**
     * Edit datatype
     *
     * @return \Zend\View\Model\ViewModel|array
     */
    public function editAction()
    {
        $datatypeModel = Datatype\Model::fromId($this->getRouteMatch()->getParam('id'));
        if (empty($datatypeModel)) {
            return $this->redirect()->toRoute('development/datatype');
        }

        $datatype = Datatype\Model::loadDatatype($this->getServiceLocator(), $this->getRouteMatch()->getParam('id'));

        $datatypeForm = new DatatypeForm();
        $datatypeForm->setAttribute(
            'action',
            $this->url()->fromRoute(
                'development/datatype/edit',
                array('id' => $this->getRouteMatch()->getParam('id'))
            )
        );

        DatatypeForm::addContent($datatypeForm, Datatype\Model::loadPrevalueEditor($datatype));
        $datatypeForm->loadValues($datatypeModel);

        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost()->toArray();
            $datatypeForm->setData($post);
            if (!$datatypeForm->isValid()) {
                $this->flashMessenger()->addErrorMessage('Can not save datatype');
                $this->useFlashMessenger();
            } else {
                if ($datatypeModel->getModel() != $datatypeForm->getValue('model')) {
                    $datatypeModel->setPrevalueValue(array());
                } else {
                    $datatypeModel->setPrevalueValue(Datatype\Model::savePrevalueEditor($datatype));
                }

                try {
                    $datatypeModel->addData($datatypeForm->getInputFilter()->getValues());
                    if ($datatypeModel->save()) {
                        $this->flashMessenger()->addSuccessMessage('This datatype has been saved');
                        return $this->redirect()->toRoute(
                            'development/datatype/edit',
                            array('id' => $datatypeModel->getId())
                        );
                    }
                } catch (Exception $e) {
                    throw new \Gc\Exception($e->getMessage(), $e->getCode(), $e);
                }

                $this->flashMessenger()->addErrorMessage('Error during editing.');
                return $this->redirect()->toRoute('development/datatype/edit', array('id' => $datatypeModel->getId()));
            }
        }

        return array('form' => $datatypeForm, 'infos' => $datatype->getInfos());
    }

    /**
     * Delete datatype
     *
     * @return \Zend\View\Model\ViewModel|array
     */
    public function deleteAction()
    {
        $datatype = Datatype\Model::fromId($this->getRouteMatch()->getParam('id', null));
        if (!empty($datatype) and $datatype->delete()) {
            return $this->returnJson(array('success' => true, 'message' => 'This datatype has been deleted'));
        }

        return $this->returnJson(array('success' => false, 'message' => 'Datatype does not exists'));
    }
}
