<?php
/**
 * This source file is part of GotCms.
 *
 * GotCms is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * GotCms is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License along
 * with GotCms. If not, see <http://www.gnu.org/licenses/lgpl-3.0.html>.
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
    Gc\User\Visitor,
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
        $visitor = new Visitor();
        $session = $this->getSession();
        $session_id = $this->getSession()->getDefaultManager()->getId();

        try
        {
            $session->visitor_id = $visitor->getVisitorId($session_id);
        }
        catch(Exception $e)
        {
            //don't care
        }

        if(CoreConfig::getValue('site_is_offline') == 1)
        {
            //Site is offline
            if(!$this->getAuth()->hasIdentity())
            {
                $document = Document\Model::fromId(CoreConfig::getValue('site_offline_document'));
                if(empty($document))
                {
                    die('Site offline');
                }
            }
        }

        if(empty($document))
        {
            $is_preview = ($this->getRequest()->getQuery()->get('preview') == 'true');
            $path = $this->getRouteMatch()->getParam('path');
            if(empty($path))
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
                $parent_id = 0;

                foreach($explode_path as $url_key)
                {
                    $document_tmp = NULL;
                    if($has_document === FALSE)
                    {
                        $document_tmp = Document\Model::fromUrlKey($url_key, $parent_id);
                    }

                    if((is_array($children) and !empty($children) and !in_array($document_tmp, $children) and $children !== NULL) or $document_tmp === NULL)
                    {
                        $has_document = TRUE;
                    }
                    else
                    {
                        if(!empty($document_tmp))
                        {
                            if(!$document_tmp->isPublished())
                            {
                                if(!$this->getAuth()->hasIdentity() or $is_preview !== TRUE)
                                {
                                    break;
                                }
                            }

                            $document = $document_tmp;
                            $parent_id = $document->getId();
                            $children = $document->getChildren();
                        }
                    }
                }
            }
        }


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

        if(empty($document))
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
                    $this->layout()->setVariable($property->getIdentifier(), $value);
                }
            }

            $view_model->setVariable('currentDocument', $document);
            $this->layout()->setVariable('currentDocument', $document);

            //Set view from database
            $view = View\Model::fromId($document->getViewId());
            $layout = Layout\Model::fromId($document->getLayoutId());

            file_put_contents($this->_layoutPath, $layout->getContent());
            file_put_contents($this->_viewPath, $view->getContent());
        }

        return $view_model;
    }

    /**
     * Load tabs
     * @param integer $document_type_id
     * @return Gc\Component\Tab\Collection
     */
    protected function loadTabs($document_type_id)
    {
        $document_type = DocumentType\Model::fromId($document_type_id);
        return $document_type->getTabs();
    }


    /**
     * Load properties
     * @param integer $document_type_id
     * @param integer $tab_id
     * @param integer $document_id
     * @return \Gc\Component\Property\Collection
     */
    protected function loadProperties($document_type_id, $tab_id, $document_id)
    {
        $properties = new Property\Collection();
        $properties->load($document_type_id, $tab_id, $document_id);

        return $properties->getProperties();
    }

    /**
     * Explode path
     * @param array $path
     */
    protected function explodePath($path)
    {
        $explode_path = explode('/',$path);
        if(preg_match('/\/$/', $path))
        {
            array_pop($explode_path);
        }

        return $explode_path;
    }

    /**
     * Defined is can unserialize string
     * @param string $data
     * @return boolean
     */
    protected function is_serialized($data)
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
