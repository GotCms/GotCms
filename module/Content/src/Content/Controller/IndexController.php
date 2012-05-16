<?php

namespace Content\Controller;

use Gc\Mvc\Controller\Action,
    Gc\Document\Collection as DocumentCollection,
    Gc\Component,
    Zend\Json\Json;

class IndexController extends Action
{
    /**
     * Initialize Content Index Controller
     */
    public function init()
    {
        $documents = new DocumentCollection();
        $documents->load(0);

        $this->layout()->setVariable('treeview',  Component\TreeView::render(array($documents)));

        $routes = array(
            'edit' => 'documentEdit'
            , 'new' => 'documentCreate'
            , 'delete' => 'documentDelete'
            , 'copy' => 'documentCopy'
            , 'cut' => 'documentCut'
            , 'paste' => 'documentPaste'
        );

        $array_routes = array();
        foreach($routes as $key => $route)
        {
            $array_routes[$key] = $this->url()->fromRoute($route, array('id' => 'itemId'));
        }

        $this->layout()->setVariable('routes', Json::encode($array_routes));
    }

    public function indexAction()
    {
    }
}
