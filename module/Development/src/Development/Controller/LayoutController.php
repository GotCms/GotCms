<?php

namespace Development\Controller;

use Gc\Mvc\Controller\Action,
    Development\Form\Layout as LayoutForm,
    Gc\Layout;

class LayoutController extends Action
{
    /**
     * Contains information about acl
     * @var array $_acl_page
     */
    protected $_acl_page = array('resource' => 'Development', 'permission' => 'layout');

    public function indexAction()
    {
    }

    /**
     * Create Layout
     */
    public function createAction()
    {
        $layout_form = new LayoutForm();
        $layout_form->setAction($this->url()->fromRoute('layoutCreate'));

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

                $this->redirect()->toRoute('layoutCreate');
                return;
            }
        }

        return array('form' => $layout_form);
    }

    /**
     * List all layouts
     */
    public function listAction()
    {
        $layouts = new Layout\Collection();
        return array('layouts' => $layouts->getLayouts());
    }

    /**
     * Edit layout
     */
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

    /**
     * Delete layout
     */
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

