<?php

namespace Development\Controller;

use Es\Mvc\Controller\Action;

class DatatypeController extends Action
{

    protected $_datatype;

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        // action body
    }

    public function addAction()
    {
        $datatype = new Es_Model_DbTable_Datatype_Model();
        $form = new Development_Form_Datatype();
        $form->setAction($this->url()->fromRoute('datatypeAdd'));
        if($this->_request->isPost() AND $form->isValid($this->_request->getPost()))
        {
            $datatype->addData($form->getValues(TRUE));
            try
            {
                if($id = $datatype->save())
                {
                    $this->redirect()->toRoute('datatypeEdit', array('id' => $id));
                }
                else
                {
                    throw new Es_Core_Exception("Error during insert new datatype");
                }
            }
            catch(Exception $e)
            {
                /**
                * TODO(Make Es_Error)
                */
                Es_Error::set(get_class($this), $e);
            }

            $form->populate($data);
        }

        $this->view->form = $form;

    }

    public function listAction()
    {
        $datatypes = new Es_Model_DbTable_Datatype_Collection();
        $this->view->datatypes = $datatypes->getDatatypes();
    }

    public function editAction()
    {
        $datatype = Es_Model_DbTable_Datatype_Model::loadDatatype($this->getRequest()->getParam('id'));
        if(empty($datatype))
        {
            return $this->redirect()->toRoute('datatypeList');
        }

        $datatype_model = $datatype->getDatatype();

        $form = new Development_Form_Datatype();
        $form->setAction($this->url()->fromRoute('datatypeEdit'));

        Es_Form::addContent($form, Es_Model_DbTable_Datatype_Model::loadPrevalueEditor($datatype));
        $form->loadValues($datatype_model);

        if($this->_request->isPost())
        {
            if($form->isValid($this->_request->getPost()))
            {
                if($datatype_model->getModel() != $form->getValue('model'))
                {
                    $datatype_model->setValue(array());
                }
                else
                {
                    $datatype_model->setValue(Es_Model_DbTable_Datatype_Model::savePrevalueEditor($datatype));
                }

                try
                {
                    if($datatype_model->save())
                    {
                        return $this->redirect()->toRoute('datatypeEdit', array('id' => $datatype_model->getId()));
                    }
                }
                catch(Exception $e)
                {
                    /**
                    * TODO(Make Es_Error)
                    */
                    Es_Error::set(get_class($this), $e);
                }
            }
            else
            {
                $this->view->message .='There are errors in the data sent. <br />';
            }
        }

        $this->view->form = $form;
    }

    public function deleteAction()
    {
        $datatype_id = $this->getRequest()->getParam('id', NULL);
        $datatype = Es_Model_DbTable_Datatype_Model::fromId($datatype_id);
        if(empty($datatype))
        {
            $this->_helper->flashMessenger->setNameSpace('error')->addMessage('Can not delete this view');
        }
        else
        {
            $this->_helper->flashMessenger->setNameSpace('success')->addMessage('This view has been deleted');
            $datatype->delete();
        }

        return $this->redirect()->toRoute('datatypeList');
    }
}

