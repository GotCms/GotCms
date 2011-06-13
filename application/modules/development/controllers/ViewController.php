<?php

class Development_ViewController extends Es_Controller_Action
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
		$view_form = new Development_Form_View();
		$view_form->setAction($this->_helper->url->url(array(), 'viewAdd'));

		if($this->_request->isPost())
		{
			if(!$view_form->isValid($this->_request->getPost()))
			{
			}
			else
			{
				$view = new Es_Model_DbTable_View_Model();
				$view->setName($view_form->getValue('name'));
				$view->setIdentifier($view_form->getValue('identifier'));
				$view->setDescription($view_form->getValue('description'));
				$view->setContent($view_form->getValue('content'));
				$view->save();

				$this->_helper->redirector->goToRoute(array(), 'viewAdd');
				return;
			}
		}

		$this->view->form = $view_form;
	}

	public function listAction()
	{
		$views = new Es_Model_DbTable_View_Collection();
		$this->view->views = $views->getViews();
	}

	public function editAction()
	{
		$view_id = $this->getRequest()->getParam('id', NULL);
		$view = Es_Model_DbTable_View_Model::fromId($view_id);
		if(empty($view_id) or empty($view))
		{
			return $this->_helper->redirector->goToRoute(array(), 'viewList');
		}

		$form = new Development_Form_View();
		$form->setAction($this->_helper->url->url(array('id' => $view_id), 'viewEdit'));
		$form->loadValues($view);

		if($this->getRequest()->isPost())
		{
			$data = $this->getRequest()->getPost();
			if($form->isValid($data))
			{
				$view->addData($form->getValues(TRUE));
				$view->save();
				$this->_helper->redirector->goToRoute(array('id' => $view_id), 'viewEdit');
			}

			$form->populate($data);
		}

		$this->view->form = $form;
	}

	public function deleteAction()
	{
		$view_id = $this->getRequest()->getParam('id', NULL);
		$view = Es_Model_DbTable_View_Model::fromId($view_id);
		if(empty($view_id) or empty($view))
		{
			$this->_helper->flashMessenger->setNameSpace('error')->addMessage('Can not delete this view');
		}
		else
		{
			$this->_helper->flashMessenger->setNameSpace('success')->addMessage('This view has been deleted');
			$view->delete();
		}

		return $this->_helper->redirector->goToRoute(array(), 'viewList');
	}
}
