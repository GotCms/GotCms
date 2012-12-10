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
 * @category   Gc_Application
 * @package    Content
 * @subpackage Controller
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Content\Controller;

use Gc\Mvc\Controller\Action,
    Gc\Component,
    Gc\Document,
    Gc\Document\Collection as DocumentCollection,
    Gc\Media\File,
    Gc\Property,
    Gc\Registry,
    elFinder\elFinder,
    elFinder\elFinderConnector,
    elFinder\elFinderVolumeDriver,
    elFinder\elFinderLocalFileSystem,
    Zend\Json\Json,
    Zend\File\Transfer\Adapter\Http as FileTransfer;

/**
 * Media controller
 *
 * @category   Gc_Application
 * @package    Content
 * @subpackage Controller
 */
class MediaController extends Action
{
    /**
     * Contains information about acl
     * @var array $_aclPage
     */
    protected $_aclPage = array('resource' => 'Content', 'permission' => 'media');

    /**
     * Initialize Content Index Controller
     * @return void
     */
    public function init()
    {
        $documents = new DocumentCollection();
        $documents->load(0);

        $this->layout()->setVariable('treeview', Component\TreeView::render(array($documents)));

        $routes = array(
            'edit' => 'documentEdit',
            'new' => 'documentCreate',
            'delete' => 'documentDelete',
            'copy' => 'documentCopy',
            'cut' => 'documentCut',
            'paste' => 'documentPaste',
            'refresh' => 'documentRefreshTreeview',
        );

        $array_routes = array();
        foreach($routes as $key => $route)
        {
            $array_routes[$key] = $this->url()->fromRoute($route, array('id' => 'itemId'));
        }

        $this->layout()->setVariable('routes', Json::encode($array_routes));
    }

    /**
     * File mananger action
     *
     * @return \Zend\View\Model\ViewModel|array
     */
    public function indexAction()
    {

        $helper_broker = $this->getServiceLocator()->get('ViewHelperManager');
        $helper_broker->get('HeadScript')->appendFile('/backend/js/libs/elfinder.min.js', 'text/javascript');

        $language = preg_replace('~(.*)_.*~', '$1', Registry::get('Translator')->getLocale());
        if($language != 'en')
        {
            $headscript->appendFile(sprintf('/backend/js/libs/i18n/elfinder.%s.js', $language), 'text/javascript');
        }

        return array('language' => preg_replace('~(.*)_.*~', '$1', \Gc\Registry::get('Translator')->getLocale()));
    }

    /**
     * Upload file action
     *
     * @return \Zend\View\Model\ViewModel|array
     */
    public function uploadAction()
    {
        $property = Property\Model::fromId($this->getRouteMatch()->getParam('property_id'));
        $document = Document\Model::fromId($this->getRouteMatch()->getParam('document_id'));
        if(!$this->getRequest()->isPost() or empty($document) or empty($property))
        {
            return $this->_returnJson(array('error' => TRUE));
        }

        $file_class = new File();
        $file_class->init($property, $document);
        $files = array();
        if($file_class->upload())
        {
            $files = $file_class->getFiles();
        }

        if(!empty($files))
        {
            return $this->_returnJson($files);
        }

        return $this->_returnJson(array('error' => TRUE));
    }

    /**
     * Delete file
     *
     * @return \Zend\View\Model\ViewModel|array
     */
    public function removeAction()
    {
        $property = Property\Model::fromId($this->getRouteMatch()->getParam('property_id'));
        $document = Document\Model::fromId($this->getRouteMatch()->getParam('document_id'));
        if($this->getRequest()->getMethod() != 'DELETE' or empty($document) or empty($property))
        {
            return $this->_returnJson(array('error' => TRUE));
        }

        $file = base64_decode($this->getRouteMatch()->getParam('file'));
        $file_class = new File();
        $file_class->init($property, $document);
        return $this->_returnJson(array($file_class->remove($file)));
    }

    /**
     * Connector for elFinder
     * @return void
     */
    public function connectorAction()
    {
        $opts = array(
            'roots' => array(
                array(
                    'driver'        => 'LocalFileSystem',
                    'path'          => GC_APPLICATION_PATH . '/public/frontend/',
                    'tmbPath'       => 'thumbnails',
                    'URL'           => '/frontend/',
                    'accessControl' => 'access',
                    //Do not show .gitignore
                    'attributes' => array(
                        array(
                            'pattern' => '~^/\.gitignore$~',
                            'hidden'  => TRUE,
                        ),
                    ),
                )

            )
        );

        // run elFinder
        $connector = new elFinderConnector(new elFinder($opts));
        $connector->run();
    }
}
