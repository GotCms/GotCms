<?php

namespace Config\Controller;

use Gc\Mvc\Controller\Action,
    Gc\Core\Config,
    Config\Form\Config as configForm;

class CmsController extends Action
{
    /**
     * @var \Config\Form\Config $_form
     */
    protected $_form;

    /**
     * Contains information about acl
     * @var array
     */
    protected $_acl_page = array('resource' => 'Config', 'permission' => 'system');

    /**
     * Initialize controller
     * @return void
     */
    public function init()
    {
        $this->_form = new configForm();
    }

    /**
     * Generate general configuration form
     *
     * @return void
     */
    public function editGeneralAction()
    {
        $this->_form->initGeneral();
        $this->forward('edit');
    }

    /**
     * Generate system configuration form
     *
     * @return void
     */
    public function editSystemAction()
    {
        $this->_form->initSystem();
        $this->forward('edit');
    }

    /**
     * Generate server configuration form
     *
     * @return void
     */
    public function editServerAction()
    {
        $this->_form->initServer();
        $this->forward('edit');
    }

    /**
     * Generate form and display
     *
     * @return \Zend\View\Model\ViewModel|array
     */
    public function editAction()
    {

    }
}
