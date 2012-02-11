<?php

namespace Development\Controller;

use Es\Mvc\Controller\Action;

class LayoutController extends Action
{
    public function init()
    {
    }

    public function indexAction()
    {
    }

    public function addAction()
    {
        $layout_form = new Development_Form_Layout();
        $layout_form->setAction($this->_helper->url->url(array(), 'layoutAdd'));

        if($this->_request->isPost())
        {
            if(!$layout_form->isValid($this->_request->getPost()))
            {
            }
            else
            {
                $layout = new Es_Model_DbTable_Layout_Model();
                $layout->setName($layout_form->getValue('name'));
                $layout->setIdentifier($layout_form->getValue('identifier'));
                $layout->setDescription($layout_form->getValue('description'));
                $layout->setContent($layout_form->getValue('content'));
                $layout->save();

                $this->redirect->toRoute(array(), 'layoutAdd');
                return;
            }
        }

        $this->view->form = $layout_form;
    }

    public function listAction()
    {
        $layouts = new Es_Model_DbTable_Layout_Collection();
        $this->view->layouts = $layouts->getLayouts();
    }

    public function editAction()
    {
        $layout_id = $this->getRequest()->getParam('id', NULL);
        $layout = Es_Model_DbTable_Layout_Model::fromId($layout_id);
        if(empty($layout_id) or empty($layout))
        {
            return $this->redirect->toRoute(array(), 'layoutList');
        }

        $form = new Development_Form_Layout();
        $form->setAction($this->_helper->url->url(array('id' => $layout_id), 'layoutEdit'));
        $form->loadValues($layout);

        if($this->getRequest()->isPost())
        {
            $data = $this->getRequest()->getPost();
            if($form->isValid($data))
            {
                $layout->addData($form->getValues(TRUE));
                $layout->save();
                $this->redirect->toRoute(array('id' => $layout_id), 'layoutEdit');
            }

            $form->populate($data);
        }

        $this->view->form = $form;
    }

    public function deleteAction()
    {
        $layout_id = $this->getRequest()->getParam('id', NULL);
        $layout = Es_Model_DbTable_Layout_Model::fromId($layout_id);
        if(empty($layout_id) or empty($layout) or !$layout->delete())
        {
            $this->_helper->flashMessenger->setNameSpace('error')->addMessage('Can not delete this layout');
        }
        else
        {
            $this->_helper->flashMessenger->setNameSpace('success')->addMessage('This layout has been deleted');
        }

        return $this->redirect->toRoute(array(), 'layoutList');
    }
}

