<?php
/**
 * This source file is part of Got CMS.
 *
 * Got CMS is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Got CMS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License along
 * with Got CMS. If not, see <http://www.gnu.org/licenses/lgpl-3.0.html>.
 *
 * PHP Version >=5.3
 *
 * @category Controller
 * @package  Application\Controller
 * @author   Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license  GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link     http://www.got-cms.com
 */

namespace Application\Controller;

use Gc\Mvc\Controller\Action,
    Gc\Core\Config as CoreConfig,
    Gc\Component,
    Gc\Document,
    Gc\DocumentType,
    Gc\Layout,
    Gc\Property,
    Gc\View,
    Zend\Config\Reader\Xml,
    Zend\Navigation\Navigation,
    Zend\View\Model\ViewModel;

class IndexController extends Action
{
    protected $_viewStream  = 'zend.view';
    protected $_viewName = 'application/index/view-content';
    protected $_layoutName = 'application/index/layout-content';
    protected $_viewPath;
    protected $_layoutPath;

    /**
      * Generate frontend from url key
      * @return \Zend\View\Model\ViewModel|array
      */
    public function indexAction()
    {
        if(CoreConfig::getValue('site_is_offline') == 1)
        {
            //Site is offline
            die('Site offline');
        }

        $url = parse_url($this->getRequest()->getRequestUri());
        $path = $url['path'];
        if($path == '/')
        {
            $document = Document\Model::fromUrlKey('');
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
                if($has_document === FALSE)
                {
                    $document_tmp = Document\Model::fromUrlKey($url_key);
                }

                if((is_array($children) and !empty($children) and !in_array($document_tmp, $children) and $children !== NULL) or $document_tmp === NULL)
                {
                    $has_document = TRUE;
                }
                else
                {
                    $document = $document_tmp;
                    if(!empty($document_tmp))
                    {
                        $children = $document_tmp->getChildren();
                    }
                }
            }
        }

        //construct the tree menu
        $view_model = new ViewModel();
        $existed = in_array($this->_viewStream, stream_get_wrappers());
        if($existed)
        {
            stream_wrapper_unregister($this->_viewStream);
        }

        stream_wrapper_register($this->_viewStream, "Gc\View\Stream");
        $template_path_stack = $this->getServiceLocator()->get('Zend\View\Resolver\TemplatePathStack');
        $template_path_stack->setUseStreamWrapper(TRUE);
        $this->_viewPath = $template_path_stack->resolve($this->_viewName);
        $this->_layoutPath = $template_path_stack->resolve($this->_layoutName);

        $view_model->setTemplate($this->_viewName);
        $this->layout()->setTemplate($this->_layoutName);

        if(empty($document) or !$document->isPublished())
        {
            // 404
            $this->getResponse()->setStatusCode(404);
            file_put_contents($this->_layoutPath, 'Error 404 - page not found');
        }
        else
        {
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
      * @return Gc\Component\Tab\Model
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
     * @return Gc\Component\Property\Model
     */
    private function loadProperties($document_type_id, $tab_id, $document_id)
    {
        $properties = new Property\Collection();
        $properties->load($document_type_id, $tab_id, $document_id);

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
     * @param mixed $data
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
