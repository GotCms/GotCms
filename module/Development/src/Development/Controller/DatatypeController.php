<?php

namespace Development\Controller;

use Gc\Mvc\Controller\Action,
    Development\Form\Datatype as DatatypeForm,
    Gc\Datatype,
    Zend\View\Model\ViewModel;

class DatatypeController extends Action
{
    /**
     * Contains information about acl
     * @var array $_acl_page
     */
    protected $_acl_page = array('resource' => 'Development', 'permission' => 'datatype');

    public function indexAction()
    {
        // action body
    }

    /**
     * Create Datatype
     */
    public function createAction()
    {
        $datatype = new Datatype\Model();
        $form = new DatatypeForm();
        $form->setAction($this->url()->fromRoute('datatypeCreate'));
        $post = $this->getRequest()->post()->toArray();
        if($this->getRequest()->isPost() AND $form->isValid($post))
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
                    throw new \Gc\Core\Exception("Error during insert new datatype");
                }
            }
            catch(Exception $e)
            {
                /**
                * TODO(Make \Gc\Error)
                */
                \Gc\Error::set(get_class($this), $e);
            }

            $form->populate($post);
        }

        return array('form' => $form);
    }

    /**
     * List all datatypes
     */
    public function listAction()
    {
        $datatypes = new Datatype\Collection();

        return array('datatypes' => $datatypes->getDatatypes());
    }

    /**
     * Edit datatype
     */
    public function editAction()
    {
        $datatype = Datatype\Model::loadDatatype($this->_routeMatch->getParam('id'));
        if(empty($datatype))
        {
            return $this->redirect()->toRoute('datatypeList');
        }

        $datatype_model = $datatype->getDatatype();

        $form = new DatatypeForm();
        $form->setAction($this->url()->fromRoute('datatypeEdit', array('id' => $this->_routeMatch->getParam('id'))));

        DatatypeForm::addContent($form, Datatype\Model::loadPrevalueEditor($datatype));
        $form->loadValues($datatype_model);

        if($this->getRequest()->isPost())
        {
            if($form->isValid($this->getRequest()->post()->toArray()))
            {
                if($datatype_model->getModel() != $form->getValue('model'))
                {
                    $datatype_model->setValue(array());
                }
                else
                {
                    $datatype_model->setValue(Datatype\Model::savePrevalueEditor($datatype));
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
                    * TODO(Make \Gc\Error)
                    */
                    \Gc\Error::set(get_class($this), $e);
                }
            }
            else
            {
                $this->view->message .='There are errors in the data sent. <br />';
            }
        }

        return array('form' => $form);
    }

    /**
     * Delete datatype
     */
    public function deleteAction()
    {
        $datatype_id = $this->getRouteMatch()->getParam('id', NULL);
        $datatype = Datatype\Model::fromId($datatype_id);
        if(empty($datatype))
        {
            $this->flashMessenger()->setNameSpace('error')->addMessage('Can not delete this view');
        }
        else
        {
            $this->flashMessenger()->setNameSpace('success')->addMessage('This view has been deleted');
            $datatype->delete();
        }

        return $this->redirect()->toRoute('datatypeList');
    }
}

