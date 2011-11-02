<?php

class Es_Controller_Plugin_Messages extends Zend_Controller_Plugin_Abstract
{
    protected $_flashMessengerNamespace = array('error', 'success', 'warning', 'info');

    public function postDispatch(Zend_Controller_Request_Abstract $request)
    {
        $messages = array();

        $flash_messenger = Zend_Controller_Action_HelperBroker::getStaticHelper('flashMessenger');
        foreach($this->_flashMessengerNamespace as $namespace)
        {
            if($flash_messenger->setNamespace($namespace)->hasMessages())
            {
                $messages[$namespace] = $flash_messenger->getMessages();
            }
        }

        $view_renderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
        if (null === $view_renderer->view)
        {
            $view_renderer->initView();
        }

        $view = $view_renderer->view;

        $view->flashMessages = $messages;
    }
}

