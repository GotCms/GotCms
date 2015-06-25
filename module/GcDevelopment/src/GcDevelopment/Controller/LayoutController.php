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
 * @package    GcDevelopment
 * @subpackage Controller
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace GcDevelopment\Controller;

use Gc\Mvc\Controller\Action;
use GcDevelopment\Form\Layout as LayoutForm;
use Gc\Layout;
use Zend\Http\Headers;
use ZipArchive;

/**
 * Layout controller
 *
 * @category   Gc_Application
 * @package    GcDevelopment
 * @subpackage Controller
 */
class LayoutController extends Action
{
    /**
     * Contains information about acl
     *
     * @var array
     */
    protected $aclPage = array('resource' => 'development', 'permission' => 'layout');

    /**
     * List all layouts
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        $layouts = new Layout\Collection();
        return array('layouts' => $layouts->getLayouts());
    }

    /**
     * Create Layout
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function createAction()
    {
        $layoutForm = new LayoutForm();
        $layoutForm->setAttribute('action', $this->url()->fromRoute('development/layout/create'));

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost()->toArray();
            $layoutForm->setData($data);
            if (!$layoutForm->isValid()) {
                $this->flashMessenger()->addErrorMessage('Can not save layout');
                $this->useFlashMessenger();
            } else {
                $layoutModel = new Layout\Model();
                $layoutModel->setName($layoutForm->getValue('name'));
                $layoutModel->setIdentifier($layoutForm->getValue('identifier'));
                $layoutModel->setDescription($layoutForm->getValue('description'));
                $layoutModel->setContent($layoutForm->getValue('content'));
                $layoutModel->save();

                $this->flashMessenger()->addSuccessMessage('This layout has been created');
                return $this->redirect()->toRoute('development/layout/edit', array('id' => $layoutModel->getId()));
            }
        }

        return array('form' => $layoutForm);
    }

    /**
     * Edit layout
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function editAction()
    {
        $layoutId    = $this->getRouteMatch()->getParam('id', null);
        $layoutModel = Layout\Model::fromId($layoutId);
        if (empty($layoutId) or empty($layoutModel)) {
            return $this->redirect()->toRoute('development/layout');
        }

        $layoutForm = new LayoutForm();
        $layoutForm->setAttribute(
            'action',
            $this->url()->fromRoute('development/layout/edit', array('id' => $layoutId))
        );
        $layoutForm->loadValues($layoutModel);

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost()->toArray();

            $layoutForm->setData($data);
            if (!$layoutForm->isValid()) {
                $this->flashMessenger()->addErrorMessage('Can not save layout');
                $this->useFlashMessenger();
            } else {
                $layoutModel->setName($layoutForm->getValue('name'));
                $layoutModel->setIdentifier($layoutForm->getValue('identifier'));
                $layoutModel->setDescription($layoutForm->getValue('description'));
                $layoutModel->setContent($layoutForm->getValue('content'));
                $layoutModel->save();

                $this->flashMessenger()->addSuccessMessage('This layout has been saved');
                return $this->redirect()->toRoute('development/layout/edit', array('id' => $layoutId));
            }
        }

        return array('form' => $layoutForm, 'layoutId' => $layoutId);
    }

    /**
     * Delete layout
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function deleteAction()
    {
        $layout = Layout\Model::fromId($this->getRouteMatch()->getParam('id', null));
        if (!empty($layout) and $layout->delete()) {
            return $this->returnJson(array('success' => true, 'message' => 'This layout has been deleted'));
        }

        return $this->returnJson(array('success' => false, 'message' => 'Layout does not exists'));
    }

    /**
     * Upload a file to the server
     *
     * @return \Zend\Http\Response
     */
    public function uploadAction()
    {
        $layoutId = $this->getRouteMatch()->getParam('id', null);
        if (!empty($layoutId)) {
            $layout = Layout\Model::fromId($layoutId);
            if (empty($layout)or empty($_FILES['upload']['tmp_name']) or $_FILES['upload']['error'] != UPLOAD_ERR_OK) {
                $this->flashMessenger()->addErrorMessage('Can not upload layout');
                return $this->redirect()->toRoute('development/layout/edit', array('id' => $layoutId));
            }

            $layout->setContent(file_get_contents($_FILES['upload']['tmp_name']));
            $layout->save();

            $this->flashMessenger()->addSuccessMessage('Layout updated');
            return $this->redirect()->toRoute('development/layout/edit', array('id' => $layoutId));
        }

        if (empty($_FILES['upload'])) {
            $this->flashMessenger()->addErrorMessage('Can not upload layouts');
            return $this->redirect()->toRoute('development/layout');
        }

        foreach ($_FILES['upload']['name'] as $idx => $name) {
            if ($_FILES['upload']['error'][$idx] != UPLOAD_ERR_OK) {
                continue;
            }

            $identifier = preg_replace('~\.phtml$~', '', $name);
            $layout     = Layout\Model::fromIdentifier($identifier);
            if (empty($layout)) {
                continue;
            }

            $layout->setContent(file_get_contents($_FILES['upload']['tmp_name'][$idx]));
            $layout->save();
        }

        $this->flashMessenger()->addSuccessMessage('Layouts updated');
        return $this->redirect()->toRoute('development/layout');
    }

    /**
     * Send a file to the browser
     *
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function downloadAction()
    {
        $layoutId = $this->getRouteMatch()->getParam('id', null);
        if (!empty($layoutId)) {
            $layout = Layout\Model::fromId($layoutId);
            if (empty($layout)) {
                $this->flashMessenger()->addErrorMessage('This layout can not be download');
                return $this->redirect()->toRoute('development/layout/edit', array('id' => $layoutId));
            }

            $content  = $layout->getContent();
            $filename = $layout->getIdentifier() . '.phtml';
        } else {
            $layouts     = new Layout\Collection();
            $children    = $layouts->getLayouts();
            $zip         = new ZipArchive;
            $tmpFilename = tempnam(sys_get_temp_dir(), 'zip');
            $res         = $zip->open($tmpFilename, ZipArchive::CREATE);
            if ($res === true) {
                foreach ($children as $child) {
                    $zip->addFromString($child->getIdentifier() . '.phtml', $child->getContent());
                }

                $zip->close();
                $content  = file_get_contents($tmpFilename);
                $filename = 'layout.zip';
                unlink($tmpFilename);
            }
        }

        if (empty($content) or empty($filename)) {
            $this->flashMessenger()->addErrorMessage('Can not save layouts');
            return $this->redirect()->toRoute('development/layout');
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
}
