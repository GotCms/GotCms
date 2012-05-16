<?php

namespace Config\Controller;

use Gc\Mvc\Controller\Action;

class IndexController extends Action
{
    /**
     * @return \Zend\View\Model\ViewModel|array
     */
    public function indexAction()
    {
        return array('message' => 'azdazd');
    }

}
