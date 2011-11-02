<?php
class Login_DisconnectController extends Es_Controller_Action
{
    public function indexAction() {
        Zend_Auth::getInstance()->clearIdentity();
        $this->_redirect('/');
    }
}
