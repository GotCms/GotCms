<?php

namespace Development\Controller;

use Es\Mvc\Controller\Action,
    Development\Form\View as viewForm,
    Application\Model\View;

class ViewController extends Action
{
    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {

    }

    public function addAction()
    {
        $view_form = new viewForm();
        $view_form->setAction($this->url()->fromRoute('viewAdd'));

        if($this->getRequest()->isPost())
        {
            if(!$view_form->isValid($this->getRequest()->getPost()))
            {
            }
            else
            {
                $view_model = new View\Model();
                $view_model->setName($view_form->getValue('name'));
                $view_model->setIdentifier($view_form->getValue('identifier'));
                $view_model->setDescription($view_form->getValue('description'));
                $view_model->setContent($view_form->getValue('content'));
                $view_model->save();

                $this->redirect()->toRoute('viewAdd');
                return;
            }
        }

        return array('form' => $view_form);
    }

    public function listAction()
    {
        $view_collection = new View\Collection();
        return array('views' => $view_collection->getViews());
    }

    public function editAction()
    {
        $view_id = $this->getRequest()->getParam('id', NULL);
        $view = View\Model::fromId($view_id);
        if(empty($view_id) or empty($view))
        {
            return $this->redirect()->toRoute('viewList');
        }

        $form = new viewForm();
        $form->setAction($this->url()->fromRoute('viewEdit',array('id' => $view_id)));
        $form->loadValues($view);

        if($this->getRequest()->isPost())
        {
            $data = $this->getRequest()->getPost();
            if($form->isValid($data))
            {
                $view->addData($form->getValues(TRUE));
                $view->save();
                $this->redirect()->toRoute('viewEdit', array('id' => $view_id));
            }

            $form->populate($data);
        }

        $this->view->form = $form;
    }

    public function deleteAction()
    {
        $view_id = $this->getRequest()->getParam('id', NULL);
        $view = View\Model::fromId($view_id);
        if(empty($view_id) or empty($view))
        {
            $this->_helper->flashMessenger->setNameSpace('error')->addMessage('Can not delete this view');
        }
        else
        {
            $this->_helper->flashMessenger->setNameSpace('success')->addMessage('This view has been deleted');
            $view->delete();
        }

        return $this->redirect()->toRoute('viewList');
    }
}
