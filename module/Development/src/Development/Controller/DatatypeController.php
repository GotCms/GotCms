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
     * @var array $_aclPage
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
        $datatype = new Datatype\Model();
        $datatype_form = new DatatypeForm();
        $datatype_form->setAttribute('action', $this->url()->fromRoute('datatypeCreate'));
        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost()->toArray();
            $datatype_form->setData($post);
            if (!$datatype_form->isValid()) {
                $this->flashMessenger()->addErrorMessage('Can not save datatype');
                $this->useFlashMessenger();
            } else {
                $datatype->addData($datatype_form->getInputFilter()->getValues());
                try {
                    if ($id = $datatype->save()) {
                        $this->flashMessenger()->addSuccessMessage('This datatype has been saved');
                        return $this->redirect()->toRoute('datatypeEdit', array('id' => $id));
                    } else {
                        throw new \Gc\Core\Exception('Error during insert new datatype');
                    }
                } catch (\Exception $e) {
                    throw new \Gc\Exception($e->getMessage(), $e->getCode(), $e);
                }
            }
        }

        return array('form' => $datatype_form);
    }

    /**
     * Edit datatype
     *
     * @return \Zend\View\Model\ViewModel|array
     */
    public function editAction()
    {
        $datatype_model = Datatype\Model::fromId($this->_routeMatch->getParam('id'));
        if (empty($datatype_model)) {
            return $this->redirect()->toRoute('datatypeList');
        }

        $datatype = Datatype\Model::loadDatatype($this->_routeMatch->getParam('id'));

        $datatype_form = new DatatypeForm();
        $datatype_form->setAttribute(
            'action',
            $this->url()->fromRoute(
                'datatypeEdit',
                array('id' => $this->_routeMatch->getParam('id'))
            )
        );

        DatatypeForm::addContent($datatype_form, Datatype\Model::loadPrevalueEditor($datatype));
        $datatype_form->loadValues($datatype_model);

        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost()->toArray();
            $datatype_form->setData($post);
            if (!$datatype_form->isValid()) {
                $this->flashMessenger()->addErrorMessage('Can not save datatype');
                $this->useFlashMessenger();
            } else {
                if ($datatype_model->getModel() != $datatype_form->getValue('model')) {
                    $datatype_model->setPrevalueValue(array());
                } else {
                    $datatype_model->setPrevalueValue(Datatype\Model::savePrevalueEditor($datatype));
                }

                try {
                    $datatype_model->addData($datatype_form->getInputFilter()->getValues());
                    if ($datatype_model->save()) {
                        $this->flashMessenger()->addSuccessMessage('This datatype has been saved');
                        return $this->redirect()->toRoute('datatypeEdit', array('id' => $datatype_model->getId()));
                    }
                } catch (\Exception $e) {
                    throw new \Gc\Exception($e->getMessage(), $e->getCode(), $e);
                }

                $this->flashMessenger()->addErrorMessage('Error during editing.');
                return $this->redirect()->toRoute('datatypeEdit', array('id' => $datatype_model->getId()));
            }
        }

        return array('form' => $datatype_form, 'infos' => $datatype->getInfos());
    }

    /**
     * Delete datatype
     *
     * @return \Zend\View\Model\ViewModel|array
     */
    public function deleteAction()
    {
        $datatype = Datatype\Model::fromId($this->getRouteMatch()->getParam('id', null));
        if (!empty($datatype)) {
            if ($datatype->delete()) {
                return $this->returnJson(array('success' => true, 'message' => 'This datatype has been deleted'));
            }
        }

        return $this->returnJson(array('success' => false, 'message' => 'Datatype does not exists'));
    }
}
