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
 * @package    Development
 * @subpackage Controller
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Development\Controller;

use Gc\Mvc\Controller\Action;
use Development\Form\View as ViewForm;
use Gc\View;
use Zend\Http\Headers;
use Zend\File\Transfer\Adapter\Http as FileTransfer;
use ZipArchive;

/**
 * View controller
 *
 * @category   Gc_Application
 * @package    Development
 * @subpackage Controller
 */
class ViewController extends Action
{
    /**
     * Contains information about acl
     *
     * @var array
     */
    protected $aclPage = array('resource' => 'development', 'permission' => 'view');

    /**
     * List all views
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        $viewCollection = new View\Collection();
        return array('views' => $viewCollection->getViews());
    }

    /**
     * Create view
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function createAction()
    {
        $viewForm = new ViewForm();
        $viewForm->setAttribute('action', $this->url()->fromRoute('development/view/create'));

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost()->toArray();
            $viewForm->setData($data);
            if (!$viewForm->isValid()) {
                $this->flashMessenger()->addErrorMessage('Can not save view');
                $this->useFlashMessenger();
            } else {
                $viewModel = new View\Model();
                $viewModel->setName($viewForm->getValue('name'));
                $viewModel->setIdentifier($viewForm->getValue('identifier'));
                $viewModel->setDescription($viewForm->getValue('description'));
                $viewModel->setContent($viewForm->getValue('content'));
                $viewModel->save();

                $this->flashMessenger()->addSuccessMessage('This view has been created');
                return $this->redirect()->toRoute('development/view/edit', array('id' => $viewModel->getId()));
            }
        }

        return array('form' => $viewForm);
    }

    /**
     * Edit view
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function editAction()
    {
        $viewId    = $this->getRouteMatch()->getParam('id', null);
        $viewModel = View\Model::fromId($viewId);
        if (empty($viewId) or empty($viewModel)) {
            return $this->redirect()->toRoute('development/view');
        }

        $viewForm = new ViewForm();
        $viewForm->setAttribute('action', $this->url()->fromRoute('development/view/edit', array('id' => $viewId)));
        $viewForm->loadValues($viewModel);

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost()->toArray();
            $viewForm->setData($data);
            if (!$viewForm->isValid()) {
                $this->flashMessenger()->addErrorMessage('Can not save view');
                $this->useFlashMessenger();
            } else {
                $viewModel->setName($viewForm->getValue('name'));
                $viewModel->setIdentifier($viewForm->getValue('identifier'));
                $viewModel->setDescription($viewForm->getValue('description'));
                $viewModel->setContent($viewForm->getValue('content'));
                $viewModel->save();

                $this->flashMessenger()->addSuccessMessage('This view has been saved');
                return $this->redirect()->toRoute('development/view/edit', array('id' => $viewId));
            }
        }

        return array('form' => $viewForm, 'viewId' => $viewId);
    }

    /**
     * Delete View
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function deleteAction()
    {
        $view = View\Model::fromId($this->getRouteMatch()->getParam('id', null));
        if (!empty($view) and $view->delete()) {
            return $this->returnJson(array('success' => true, 'message' => 'This view has been deleted'));
        }

        return $this->returnJson(array('success' => false, 'message' => 'View does not exists'));
    }

    /**
     * Upload a file to the server
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function uploadAction()
    {
        $viewId = $this->getRouteMatch()->getParam('id', null);
        if (!empty($viewId)) {
            $view = View\Model::fromId($viewId);
            if (empty($view)or empty($_FILES['upload']['tmp_name']) or $_FILES['upload']['error'] != UPLOAD_ERR_OK) {
                $this->flashMessenger()->addErrorMessage('Can not upload view');
                return $this->redirect()->toRoute('development/view/edit', array('id' => $viewId));
            }

            $view->setContent(file_get_contents($_FILES['upload']['tmp_name']));
            $view->save();
            $this->flashMessenger()->addSuccessMessage('View updated');
            return $this->redirect()->toRoute('development/view/edit', array('id' => $viewId));
        }

        if (empty($_FILES['upload'])) {
            $this->flashMessenger()->addErrorMessage('Can not upload views');
            return $this->redirect()->toRoute('development/view');
        }

        foreach ($_FILES['upload']['name'] as $idx => $name) {
            if ($_FILES['upload']['error'][$idx] != UPLOAD_ERR_OK) {
                continue;
            }

            $identifier = preg_replace('~\.phtml$~', '', $name);
            $view       = View\Model::fromIdentifier($identifier);
            if (empty($view)) {
                continue;
            }

            $view->setContent(file_get_contents($_FILES['upload']['tmp_name'][$idx]));
            $view->save();
        }

        $this->flashMessenger()->addSuccessMessage('Views updated');
        return $this->redirect()->toRoute('development/view');
    }

    /**
     * Send a file to the browser
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function downloadAction()
    {
        $viewId = $this->getRouteMatch()->getParam('id', null);
        if (!empty($viewId)) {
            $view = View\Model::fromId($viewId);
            if (empty($view)) {
                $this->flashMessenger()->addErrorMessage('This view can not be download');
                return $this->redirect()->toRoute('development/view/edit', array('id' => $viewId));
            }

            $content  = $view->getContent();
            $filename = $view->getIdentifier() . '.phtml';
        } else {
            $views       = new View\Collection();
            $children    = $views->getViews();
            $zip         = new ZipArchive;
            $tmpFilename = tempnam(sys_get_temp_dir(), 'zip');
            $res         = $zip->open($tmpFilename, ZipArchive::CREATE);
            if ($res === true) {
                foreach ($children as $child) {
                    $zip->addFromString($child->getIdentifier() . '.phtml', $child->getContent());
                }

                $zip->close();
                $content  = file_get_contents($tmpFilename);
                $filename = 'views.zip';
                unlink($tmpFilename);
            }
        }

        if (empty($content) or empty($filename)) {
            $this->flashMessenger()->addErrorMessage('Can not save views');
            return $this->redirect()->toRoute('development/view');
        }

        $headers = new Headers();
        $headers->addHeaderLine('Pragma', 'public')
            ->addHeaderLine('Cache-control', 'must-revalidate, post-check=0, pre-check=0')
            ->addHeaderLine('Cache-control', 'private')
            ->addHeaderLine('Expires', -1)
            ->addHeaderLine('Content-Type', 'application/octet-stream')
            ->addHeaderLine('Content-Transfer-Encoding', 'binary')
            ->addHeaderLine('Content-Length', strlen($content))
            ->addHeaderLine('Content-Disposition', 'attachment; filename=' . $filename);

        $response = $this->getResponse();
        $response->setHeaders($headers);
        $response->setContent($content);

        return $response;
    }

    /**
     * Update database from files
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function updateAction()
    {
        $viewId = $this->getRouteMatch()->getParam('id', null);
        if (!empty($viewId)) {
            $view = View\Model::fromId($viewId);
            if (empty($view)) {
                $this->flashMessenger()->addErrorMessage('This view can not be update');
                return $this->redirect()->toRoute('development/view/edit', array('id' => $viewId));
            }

            $view->setContent($view->getFileContents());
            $view->save();
            $this->flashMessenger()->addSuccessMessage('View updated');
            return $this->redirect()->toRoute('development/view/edit', array('id' => $viewId));
        } else {
            $views    = new View\Collection();
            $children = $views->getViews();

            foreach ($children as $child) {
                $child->setContent($child->getFileContents());
                $child->save();
            }
        }

        $this->flashMessenger()->addSuccessMessage('Views updated');
        return $this->redirect()->toRoute('development/view');
    }
}
