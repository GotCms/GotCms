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

use Gc\Mvc\Controller\Action;
use Gc\Component;
use Gc\Document;
use Gc\Document\Collection as DocumentCollection;
use Gc\Media\File;
use Gc\Property;
use Gc\Registry;
use elFinder\elFinder;
use elFinder\elFinderConnector;
use elFinder\elFinderVolumeDriver;
use elFinder\elFinderLocalFileSystem;
use Zend\Json\Json;
use Zend\File\Transfer\Adapter\Http as FileTransfer;

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
     *
     * @var array $aclPage
     */
    protected $aclPage = array('resource' => 'Content', 'permission' => 'media');

    /**
     * Initialize Content Index Controller
     *
     * @return void
     */
    public function init()
    {
        $documents = new DocumentCollection();
        $documents->load(0);

        $this->layout()->setVariable('treeview', Component\TreeView::render(array($documents)));

        $routes = array(
            'edit' => 'content/document/edit',
            'new' => 'content/document/create',
            'delete' => 'content/document/delete',
            'copy' => 'content/document/copy',
            'cut' => 'content/document/cut',
            'paste' => 'content/document/paste',
            'refresh' => 'content/document/refresh-treeview',
        );

        $arrayRoutes = array();
        foreach ($routes as $key => $route) {
            $arrayRoutes[$key] = $this->url()->fromRoute($route, array('id' => 'itemId'));
        }

        $this->layout()->setVariable('routes', Json::encode($arrayRoutes));
    }

    /**
     * File manager action
     *
     * @return \Zend\View\Model\ViewModel|array
     */
    public function indexAction()
    {

        $helperBroker = $this->getServiceLocator()->get('ViewHelperManager');
        $headscript   = $helperBroker->get('HeadScript');
        $cdn          = $helperBroker->get('CdnBackend');
        $translator   = Registry::get('Application')->getServiceManager()->get('translator');
        $headscript->appendFile($cdn('/backend/js/libs/elfinder.min.js'), 'text/javascript');

        $language = preg_replace('~(.*)_.*~', '$1', $translator->getLocale());
        if ($language != 'en') {
            $headscript->appendFile(
                $cdn(sprintf('/backend/js/libs/i18n/elfinder.%s.js', $language)),
                'text/javascript'
            );
        }

        return array('language' => preg_replace('~(.*)_.*~', '$1', $translator->getLocale()));
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
        if (!$this->getRequest()->isPost() or empty($document) or empty($property)) {
            return $this->returnJson(array('error' => true));
        }

        $fileClass = new File();
        $fileClass->load($property, $document);
        $files = array();
        if ($fileClass->upload()) {
            $files = $fileClass->getFiles();
        }

        if (!empty($files)) {
            return $this->returnJson($files);
        }

        return $this->returnJson(array('error' => true));
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
        if ($this->getRequest()->getMethod() != 'DELETE' or empty($document) or empty($property)) {
            return $this->returnJson(array('error' => true));
        }

        $file      = base64_decode($this->getRouteMatch()->getParam('file'));
        $fileClass = new File();
        $fileClass->load($property, $document);
        return $this->returnJson(array($fileClass->remove($file)));
    }

    /**
     * Connector for elFinder
     *
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
                            'hidden'  => true,
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
