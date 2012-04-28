<?php

namespace Application\Controller;

use Es\Mvc\Controller\Action,
    Es\Component,
    Es\Document,
    Es\DocumentType,
    Es\Layout,
    Es\Property,
    Es\View,
    Zend\Config\Reader\Xml,
    Zend\Navigation\Navigation,
    Zend\View\Model\ViewModel;

class IndexController extends Action
{
    protected $_viewStream  = 'zend.view';
    protected $_viewName = 'index/view_content';
    protected $_layoutName = 'index/layout_content';
    protected $_viewPath;
    protected $_layoutPath;

    public function indexAction()
    {
        $url = parse_url($this->getRequest()->getRequestUri());
        $path = $url['path'];
        if($path == '/')
        {
            $document = Document\Model::fromUrlKey('home');
        }
        else
        {
            $explode_path = $this->explodePath($path);

            $children = NULL;
            $key = array();
            $document = NULL;
            $has_document = FALSE;

            foreach($explode_path as $url_key)
            {
                $document_tmp = NULL;
                if($has_document == FALSE)
                {
                    $document_tmp = Document\Model::fromUrlKey($url_key);
                    break; //No document, 404 errors
                }

                if((is_array($children) and !empty($children) and !in_array($document_tmp, $children) and $children !== NULL) or $document_tmp === NULL)
                {
                    $has_document = true;
                }
                else
                {
                    $children = $document_tmp->getChildren();
                    $document = $document_tmp;
                }
            }
        }

        //construct the tree menu
        /*
        * @TODO
        $nav = new Component\Navigation();
        $config = new Xml();
        $config->fromString($nav->render());

        //var_dump(get_class_methods($this->getLocator()->get('Zend\View\Renderer\PhpRenderer')->navigation()->setContainer(new Navigation($config))));
        */
        $view_model = new ViewModel();
        $existed = in_array($this->_viewStream, stream_get_wrappers());
        if ($existed)
        {
            stream_wrapper_unregister($this->_viewStream);
        }

        stream_wrapper_register($this->_viewStream, "Es\View\Stream");
        $template_path_stack = $this->getLocator()->get('Zend\View\Resolver\TemplatePathStack');
        $template_path_stack->setUseStreamWrapper(TRUE);
        $this->_viewPath = $template_path_stack->resolve($this->_viewName);
        $this->_layoutPath = $template_path_stack->resolve($this->_layoutName);

        $view_model->setTemplate($this->_viewName);
        $this->layout()->setTemplate($this->_layoutName);

        if(empty($document) or !$document->isPublished())
        {
            // 404
            file_put_contents($this->_layoutPath, 'Error 404 - page not found');
        }
        else
        {
            //set current page active
            //$page = $this->view->navigation()->findOneByLabel($document->getName()); /* @var $page Zend_Navigation_Page */
            //if($page)
            //{
            //    $page->setActive();
            //}

            //Get all tabs of document
            $tabs = $this->loadTabs($document->getDocumentTypeId());
            //get Tabs and Properties to construct property in view
            foreach($tabs as $tab)
            {
                $tabs_array[] = $tab->getName();
                $properties = $this->loadProperties($document->getDocumentTypeId(), $tab->getId(), $document->getId());
                foreach($properties as $property)
                {
                    $value = $property->getValue();
                    if($this->is_serialized($property->getValue()))
                    {
                        $value = unserialize($property->getValue());
                    }

                    $view_model->setVariable($property->getIdentifier(), $value);
                }
            }

            //Set view from database
            $view = View\Model::fromId($document->getViewId());
            $layout = Layout\Model::fromId($document->getLayoutId());

            file_put_contents($this->_layoutPath, $layout->getContent());
            file_put_contents($this->_viewPath, $view->getContent());
        }

        return $view_model;
    }

    /**
     * @param integer $document_type_id
     * @return Gc_Component_Tab_Model
     */
    private function loadTabs($document_type_id)
    {
        $document_type = DocumentType\Model::fromId($document_type_id);

        return $document_type->getTabs();
    }


    /**
    * @param integer $document_type_id
    * @param integer $tab_id
    * @param integer $document_id
    * @return Gc_Component_Property_Model
    */
    private function loadProperties($document_type_id, $tab_id, $document_id)
    {
        $properties = new Property\Collection($document_type_id, $tab_id, $document_id);

        return $properties->getProperties();
    }

    /**
    * @param string $path
    */
    private function explodePath($path)
    {
        $explode_path = explode('/', substr($path, 1));
        if(preg_match('/\/$/', $path))
        {
            array_pop($explode_path);
        }

        return $explode_path;
    }

    /**
    * @param mixte $data
    * @return boolean
    */
    private  function is_serialized($data)
    {
        if (trim($data) == "")
        {
            return FALSE;
        }

        if (preg_match("/^(i|s|a|o|d)(.*);/si", $data))
        {
            return TRUE;
        }

        return FALSE;
    }
}
