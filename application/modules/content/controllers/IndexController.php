<?php

class Content_IndexController extends Es_Controller_Action
{
    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        $this->_helper->layout->assign('treeview', Es_Component_TreeView::render(array(new Es_Model_DbTable_Document_Collection())));
    }
}

