<?php

class Content_IndexController extends Es_Controller_Action
{
    public function init()
    {
        $documents = new Es_Model_DbTable_Document_Collection();
        $documents->load(0);
        $this->_helper->layout->assign('treeview', Es_Component_TreeView::render(array($documents)));

        $router = $this->getFrontController()->getRouter();
        $routes = array(
            'edit' => 'documentEdit'
            , 'new' => 'documentAdd'
            , 'delete' => 'documentDelete'
            , 'copy' => 'documentCopy'
            , 'cut' => 'documentCut'
            , 'paste' => 'documentPaste'
        );

        $array_routes = array();
        foreach($routes as $key => $route)
        {
            if($router->hasRoute($route))
            {
                $array_routes[$key] = $router->assemble(array('id' => 'itemId'), $route);
            }
        }

        $this->_helper->layout->assign('routes', Zend_Json::encode($array_routes));
    }

    public function indexAction()
    {
    }
}

