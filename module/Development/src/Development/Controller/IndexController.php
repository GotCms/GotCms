<?php

namespace Development\Controller;

use Gc\Mvc\Controller\Action;

class IndexController extends Action
{
    public function indexAction()
    {
        return array('message' => 'azdazd');
    }
}
