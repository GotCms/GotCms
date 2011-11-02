<?php

class Development_ModelController extends Es_Controller_Action
{
    protected $_datatypesPath;

    public function init()
    {
        $this->_datatypesPath = APPLICATION_PATH.'/scripts/Datatypes';
    }

    public function indexAction()
    {
    }

    public function addAction()
    {
        $model_form = new Development_Form_Model();
        $model_form->setAction($this->_helper->url->url(array(), 'modelAdd'));

        if($this->_request->isPost())
        {
            if(!$model_form->isValid($this->_request->getPost()))
            {
            }
            else
            {
                $model = new Es_Model_DbTable_Model_Model();
                $model->setName($model_form->getValue('name'));
                $model->setIdentifier($model_form->getValue('identifier'));
                $model->setDescription($model_form->getValue('description'));
                $model->save();

                $this->_helper->redirector->goToRoute(array(), 'modelAdd');
                return;
            }
        }

        $this->view->form = $model_form;
    }

    public function listAction()
    {
        $models = new Es_Model_DbTable_Model_Collection();
        $this->view->models = $models->getModels();
    }

    public function editAction()
    {
        $model_id = $this->getRequest()->getParam('id', NULL);
        $model = Es_Model_DbTable_Model_Model::fromId($model_id);
        if(empty($model_id) or empty($model))
        {
            return $this->_helper->redirector->goToRoute(array(), 'modelList');
        }

        $form = new Development_Form_Model();
        $form->setAction($this->_helper->url->url(array('id' => $model_id), 'modelEdit'));
        $form->loadValues($model);

        if($this->getRequest()->isPost())
        {
            $data = $this->getRequest()->getPost();
            if($form->isValid($data))
            {
                $model->addData($form->getValues(TRUE));
                $model->save();
                $this->_helper->redirector->goToRoute(array('id' => $model_id), 'modelEdit');
            }

            $form->populate($data);
        }

        $this->view->form = $form;
    }

    public function deleteAction()
    {
        $model_id = $this->getRequest()->getParam('id', NULL);
        $model = Es_Model_DbTable_Model_Model::fromId($model_id);
        if(empty($model_id) or empty($model))
        {
            $this->_helper->flashMessenger->setNameSpace('error')->addMessage('Can not delete this view');
        }
        else
        {
            $this->_helper->flashMessenger->setNameSpace('success')->addMessage('This view has been deleted');
            $model->delete();
        }

        return $this->_helper->redirector->goToRoute(array(), 'modelList');
    }

}

