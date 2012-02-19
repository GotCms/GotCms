<?php

namespace Development\Controller;

use Es\Mvc\Controller\Action,
    Development\Form\Layout as LayoutForm,
    Application\Model\Layout;

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
        $layout_form = new LayoutForm();
        $layout_form->setAction($this->url()->fromRoute('layoutAdd'));

        if($this->getRequest()->isPost())
        {
            if(!$layout_form->isValid($this->getRequest()->post()->toArray()))
            {
            }
            else
            {
                $layout = new Layout\Model();
                $layout->setName($layout_form->getValue('name'));
                $layout->setIdentifier($layout_form->getValue('identifier'));
                $layout->setDescription($layout_form->getValue('description'));
                $layout->setContent($layout_form->getValue('content'));
                $layout->save();

                $this->redirect()->toRoute('layoutAdd');
                return;
            }
        }

        return array('form' => $layout_form);
    }

    public function listAction()
    {
        $layouts = new Layout\Collection();
        return array('layouts' => $layouts->getLayouts());
    }

    public function editAction()
    {
        $layout_id = $this->getRouteMatch()->getParam('id', NULL);
        $layout = Layout\Model::fromId($layout_id);
        if(empty($layout_id) or empty($layout))
        {
            return $this->redirect()->toRoute('layoutList');
        }

        $layout_form = new LayoutForm();
        $layout_form->setAction($this->url()->fromRoute('layoutEdit', array('id' => $layout_id)));
        $layout_form->loadValues($layout);

        if($this->getRequest()->isPost())
        {
            $data = $this->getRequest()->post()->toArray();
            if($layout_form->isValid($data))
            {
                $layout->addData($layout_form->getValues(TRUE));
                $layout->save();
                $this->redirect()->toRoute('layoutEdit', array('id' => $layout_id));
            }

            $layout_form->populate($data);
        }

        return array('form' => $layout_form);
    }

    public function deleteAction()
    {
        $layout_id = $this->getRouteMatch()->getParam('id', NULL);
        $layout = Layout\Model::fromId($layout_id);
        if(empty($layout_id) or empty($layout) or !$layout->delete())
        {
            $this->flashMessenger()->setNameSpace('error')->addMessage('Can not delete this layout');
        }
        else
        {
            $this->flashMessenger()->setNameSpace('success')->addMessage('This layout has been deleted');
        }

        return $this->redirect()->toRoute('layoutList');
    }
}

