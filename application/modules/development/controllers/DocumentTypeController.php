<?php

class Development_DocumentTypeController extends Zend_Controller_Action
{

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
		$form = new Development_Form_DocumentType();
		$request = $this->getRequest();
		if($request->isPost())
		{

		}

		$this->view->form = $form;
	}
}

