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
 * @author     Pierre Rambaud (GoT) http://rambaudpierre.fr
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
     * @var array $_aclPage
     */
    protected $aclPage = array('resource' => 'Development', 'permission' => 'view');

    /**
     * List all views
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        $view_collection = new View\Collection();
        return array('views' => $view_collection->getViews());
    }

    /**
     * Create view
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function createAction()
    {
        $view_form = new ViewForm();
        $view_form->setAttribute('action', $this->url()->fromRoute('viewCreate'));

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost()->toArray();
            $view_form->setData($data);
            if (!$view_form->isValid()) {
                $this->flashMessenger()->addErrorMessage('Can not save view');
                $this->useFlashMessenger();
            } else {
                $view_model = new View\Model();
                $view_model->setName($view_form->getValue('name'));
                $view_model->setIdentifier($view_form->getValue('identifier'));
                $view_model->setDescription($view_form->getValue('description'));
                $view_model->setContent($view_form->getValue('content'));
                $view_model->save();

                $this->flashMessenger()->addSuccessMessage('This view has been created');
                return $this->redirect()->toRoute('viewEdit', array('id' => $view_model->getId()));
            }
        }

        return array('form' => $view_form);
    }

    /**
     * Edit view
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function editAction()
    {
        $view_id    = $this->getRouteMatch()->getParam('id', null);
        $view_model = View\Model::fromId($view_id);
        if (empty($view_id) or empty($view_model)) {
            return $this->redirect()->toRoute('viewList');
        }

        $view_form = new ViewForm();
        $view_form->setAttribute('action', $this->url()->fromRoute('viewEdit', array('id' => $view_id)));
        $view_form->loadValues($view_model);

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost()->toArray();
            $view_form->setData($data);
            if (!$view_form->isValid()) {
                $this->flashMessenger()->addErrorMessage('Can not save view');
                $this->useFlashMessenger();
            } else {
                $view_model->setName($view_form->getValue('name'));
                $view_model->setIdentifier($view_form->getValue('identifier'));
                $view_model->setDescription($view_form->getValue('description'));
                $view_model->setContent($view_form->getValue('content'));
                $view_model->save();

                $this->flashMessenger()->addSuccessMessage('This view has been saved');
                return $this->redirect()->toRoute('viewEdit', array('id' => $view_id));
            }
        }

        return array('form' => $view_form, 'viewId' => $view_id);
    }

    /**
     * Delete View
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function deleteAction()
    {
        $view = View\Model::fromId($this->getRouteMatch()->getParam('id', null));
        if (!empty($view)) {
            if ($view->delete()) {
                return $this->returnJson(array('success' => true, 'message' => 'This view has been deleted'));
            }
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
        $view_id = $this->getRouteMatch()->getParam('id', null);
        if (!empty($view_id)) {
            $view = View\Model::fromId($view_id);
            if (empty($view)or empty($_FILES['upload']['tmp_name']) or $_FILES['upload']['error'] != UPLOAD_ERR_OK) {
                $this->flashMessenger()->addErrorMessage('Can not upload view');
                return $this->redirect()->toRoute('viewEdit', array('id' => $view_id));
            }

            $view->setContent(file_get_contents($_FILES['upload']['tmp_name']));
            $view->save();
            $this->flashMessenger()->addSuccessMessage('View updated');
            return $this->redirect()->toRoute('viewEdit', array('id' => $view_id));
        } else {
            if (empty($_FILES['upload'])) {
                $this->flashMessenger()->addErrorMessage('Can not upload views');
                return $this->redirect()->toRoute('viewList');
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
            return $this->redirect()->toRoute('viewList');
        }
    }

    /**
     * Send a file to the browser
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function downloadAction()
    {
        $view_id = $this->getRouteMatch()->getParam('id', null);
        if (!empty($view_id)) {
            $view = View\Model::fromId($view_id);
            if (empty($view)) {
                $this->flashMessenger()->addErrorMessage('This view can not be download');
                return $this->redirect()->toRoute('viewEdit', array('id' => $view_id));
            }

            $content  = $view->getContent();
            $filename = $view->getIdentifier() . 'phtml';
        } else {
            $views        = new View\Collection();
            $children     = $views->getViews();
            $zip          = new ZipArchive;
            $tmp_filename = tempnam(sys_get_temp_dir(), 'zip');
            $res          = $zip->open($tmp_filename, ZipArchive::CREATE);
            if ($res === true) {
                foreach ($children as $child) {
                    $zip->addFromString($child->getIdentifier() . '.phtml', $child->getContent());
                }

                $zip->close();
                $content  = file_get_contents($tmp_filename);
                $filename = 'views.zip';
                unlink($tmp_filename);
            }
        }

        if (empty($content) or empty($filename)) {
            $this->flashMessenger()->addErrorMessage('Can not save views');
            return $this->redirect()->toRoute('viewList');
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
