<?php

namespace Config\Controller;

use Gc\Mvc\Controller\Action,
    Gc\Core\Config,
    Config\Form\Config as configForm;

class CmsController extends Action
{
    /**
     * @var \Config\Form\Config
     */
    protected $_form;

    public function init()
    {
        $this->_form = new configForm();
    }

    public function editGeneralAction()
    {
        $this->_form->initGeneral();
        $this->forward('edit');
    }

    public function editSystemAction()
    {
        $this->_form->initSystem();
        $this->forward('edit');
    }

    public function editServerAction()
    {
        $this->_form->initServer();
        $this->forward('edit');
    }

    public function editAction()
    {

    }
}
