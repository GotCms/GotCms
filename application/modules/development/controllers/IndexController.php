<?php

class Development_IndexController extends Es_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        $document_types = new Es_Model_DbTable_DocumentType_Model();
    }
}

