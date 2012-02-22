<?php

namespace Content\Controller;

use Es\Mvc\Controller\Action;

class IndexController extends Action
{
    public function indexAction()
    {
        return array('message' => 'azdazd');
    }
}
