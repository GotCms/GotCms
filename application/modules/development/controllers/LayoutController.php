<?php

class Development_LayoutController extends Es_Controller_Action
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

                $this->_helper->redirector->goToRoute(array(), 'layoutAdd');
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
            return $this->_helper->redirector->goToRoute(array(), 'layoutList');
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
                $this->_helper->redirector->goToRoute(array('id' => $layout_id), 'layoutEdit');
            }

            $form->populate($data);
        }

        $this->view->form = $form;
    }

    public function deleteAction()
    {
        $layout_id = $this->getRequest()->getParam('id', NULL);
        $layout = Es_Model_DbTable_Layout_Model::fromId($layout_id);
        if(empty($layout_id) or empty($layout))
        {
            $this->_helper->flashMessenger->setNameSpace('error')->addMessage('Can not delete this layout');
        }
        else
        {
            $this->_helper->flashMessenger->setNameSpace('success')->addMessage('This layout has been deleted');
            $layout->delete();
        }

        return $this->_helper->redirector->goToRoute(array(), 'layoutList');
    }
}

