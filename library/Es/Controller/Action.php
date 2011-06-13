<?php
class Es_Controller_Action extends Zend_Controller_Action
{
	public function preDispatch()
	{
		$auth = Zend_Auth::getInstance();
		/*if(!$auth->hasIdentity() and $this->getRequest()->getModuleName() != 'login')
		{
			return $this->_redirect('admin/login');
		}*/
	}
}