<?php

class Development_DatatypeController extends Zend_Controller_Action
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
		$form->setAction($this->_helper->url->url(array(), 'datatypeAdd'));
		if($this->_request->isPost() AND $form->isValid($this->_request->getPost()))
		{
			$datatype->addData($form->getValues(TRUE));
			try
			{
				if($id = $datatype->save())
				{
					$this->_helper->redirector->goToRoute(array('id' => $id), 'datatypeEdit');
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
		$datatype = Es_Model_DbTable_Datatype_Model::fromId($this->getRequest()->getParam('id'));
		if(empty($datatype))
		{
			return $this->_helper->redirector->goToRoute(array(), 'datatypeList');
		}

		$form = new Development_Form_Datatype();
		$form->setAction($this->_helper->url->url(array(), 'datatypeEdit'));
		$form->addFormContent($this->loadDatatypePrevalueEditor($datatype));
		$form->loadValues($datatype);

		if($this->_request->isPost())
		{
			if($form->isValid($this->_request->getPost()))
			{
				$datatype->addData($form->getValues(TRUE));

				if($datatype->getModelId() != $form->getValue('model_id'))
				{
					$datatype->setValue(array());
					$datatype->setModelId($form->getValue('model_id'));
				}
				else
				{
					$datatype->setValue($this->saveDatatypePrevalueEditor($datatype));
				}

				try
				{
					if($datatype->save())
					{
						return $this->_helper->redirector->goToRoute(array('id' => $datatype->getId()), 'datatypeEdit');
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

		return $this->_helper->redirector->goToRoute(array(), 'datatypeList');
	}




	/**
	 *
	 * @param Es_Model_DbTable_Datatype_Model $datatype_model
	 *
	 * @return Es_Model_DbTable_Datatype_Abstract
	 */
	private function loadDatatype(Es_Model_DbTable_Datatype_Model $datatype_model)
	{
		if($this->_datatype === null OR $this->_datatype->getModelId() != $datatype_model->getId())
		{
			$model = $datatype_model->getModel();
			$class = 'Datatypes_'.$model->getIdentifier().'_Datatype';
			$datatype =  new $class();
			$datatype->init($datatype_model);
			$this->_datatype = $datatype;
		}

		return $this->_datatype;
	}

	/**
	 *
	 * @param Es_Model_DbTable_Datatype_Model $datatype_model
	 *
	 * @return Es_Model_DbTable_Datatype_Abstract_PrevalueEditor
	 */
	private function loadDatatypePrevalueEditor(Es_Model_DbTable_Datatype_Model $datatype_model)
	{
		$datatype = $this->loadDatatype($datatype_model);
		return $datatype->getPrevalueEditor()->load();
	}

	/**
	 *
	 * @param Es_Model_DbTable_Datatype_Model $datatype_model
	 *
	 * @return Es_Model_DbTable_Datatype_Abstract_Editor
	 */
	private function saveDatatypePrevalueEditor(Es_Model_DbTable_Datatype_Model $datatype_model)
	{
		$datatype = $this->loadDatatype($datatype_model);
		$datatype->getPrevalueEditor()->save();

		return $datatype->getConfig();
	}
}

