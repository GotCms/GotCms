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
 * @package  Development\Controller
 * @author   Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license  GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link     http://www.got-cms.com
 */

namespace Development\Controller;

use Gc\Mvc\Controller\Action,
    Development\Form\Layout as LayoutForm,
    Gc\Layout,
    Zend\Http\Headers,
    Zend\File\Transfer\Adapter\Http as FileTransfer,
    ZipArchive;

class LayoutController extends Action
{
    /**
     * Contains information about acl
     * @var array $_acl_page
     */
    protected $_acl_page = array('resource' => 'Development', 'permission' => 'layout');

    /**
     * List all layouts
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        $layouts = new Layout\Collection();
        return array('layouts' => $layouts->getLayouts());
    }

    /**
     * Create Layout
     * @return \Zend\View\Model\ViewModel
     */
    public function createAction()
    {
        $layout_form = new LayoutForm();
        $layout_form->setAttribute('action', $this->url()->fromRoute('layoutCreate'));

        if($this->getRequest()->isPost())
        {
            $data = $this->getRequest()->getPost()->toArray();
            $layout_form->setData($data);
            if(!$layout_form->isValid())
            {
                $this->flashMessenger()->setNameSpace('error')->addMessage('Can not save layout');
                $this->useFlashMessenger();
            }
            else
            {
                $layout_model = new Layout\Model();
                $layout_model->setName($layout_form->getValue('name'));
                $layout_model->setIdentifier($layout_form->getValue('identifier'));
                $layout_model->setDescription($layout_form->getValue('description'));
                $layout_model->setContent($layout_form->getValue('content'));
                $layout_model->save();

                $this->flashMessenger()->setNameSpace('success')->addMessage('This layout has been created');
                return $this->redirect()->toRoute('layoutEdit', array('id' => $layout_model->getId()));
            }
        }

        return array('form' => $layout_form);
    }

    /**
     * Edit layout
     * @return \Zend\View\Model\ViewModel
     */
    public function editAction()
    {
        $layout_id = $this->getRouteMatch()->getParam('id', NULL);
        $layout_model = Layout\Model::fromId($layout_id);
        if(empty($layout_id) or empty($layout_model))
        {
            return $this->redirect()->toRoute('layoutList');
        }

        $layout_form = new LayoutForm();
        $layout_form->setAttribute('action', $this->url()->fromRoute('layoutEdit', array('id' => $layout_id)));
        $layout_form->loadValues($layout_model);

        if($this->getRequest()->isPost())
        {
            $data = $this->getRequest()->getPost()->toArray();

            $layout_form->setData($data);
            if(!$layout_form->isValid())
            {
                $this->flashMessenger()->setNameSpace('error')->addMessage('Can not save layout');
                $this->useFlashMessenger();
            }
            else
            {
                $layout_model->setName($layout_form->getValue('name'));
                $layout_model->setIdentifier($layout_form->getValue('identifier'));
                $layout_model->setDescription($layout_form->getValue('description'));
                $layout_model->setContent($layout_form->getValue('content'));
                $layout_model->save();

                $this->flashMessenger()->setNameSpace('success')->addMessage('This layout has been edited');
                return $this->redirect()->toRoute('layoutEdit', array('id' => $layout_id));
            }
        }

        return array('form' => $layout_form, 'layoutId' => $layout_id);
    }

    /**
     * Delete layout
     * @return \Zend\View\Model\ViewModel
     */
    public function deleteAction()
    {
        $layout = Layout\Model::fromId($this->getRouteMatch()->getParam('id', NULL));
        if(!empty($layout))
        {
            if($layout->delete())
            {
                return $this->_returnJson(array('success' => TRUE, 'message' => 'This layout has been deleted!'));
            }
        }

        return $this->_returnJson(array('success' => FALSE, 'message' => 'Can not delete this layout'));
    }

    /**
     * Upload a file to the server
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function uploadAction()
    {
        $layout_id = $this->getRouteMatch()->getParam('id', NULL);
        if(!empty($layout_id))
        {
            $layout = Layout\Model::fromId($layout_id);
            if(empty($layout)or empty($_FILES['upload']['tmp_name']) or $_FILES['upload']['error'] != UPLOAD_ERR_OK)
            {
                $this->flashMessenger()->setNameSpace('error')->addMessage('Can not upload layout');
                return $this->redirect()->toRoute('layoutEdit', array('id' => $layout_id));
            }

            $layout->setContent(file_get_contents($_FILES['upload']['tmp_name']));
            $layout->save();

            $this->flashMessenger()->setNameSpace('success')->addMessage('Layout updated');
            return $this->redirect()->toRoute('layoutEdit', array('id' => $layout_id));
        }
        else
        {
            if(empty($_FILES['upload']))
            {
                $this->flashMessenger()->setNameSpace('error')->addMessage('Can not upload layouts');
                return $this->redirect()->toRoute('layoutList');
            }

            foreach($_FILES['upload']['name'] as $idx => $name)
            {
                if($_FILES['upload']['error'][$idx] != UPLOAD_ERR_OK)
                {
                    continue;
                }

                $identifier = preg_replace('~\.phtml$~', '', $name);
                $layout = Layout\Model::fromIdentifier($identifier);
                if(empty($layout))
                {
                    continue;
                }

                $layout->setContent(file_get_contents($_FILES['upload']['tmp_name'][$idx]));
                $layout->save();
            }

            $this->flashMessenger()->setNameSpace('success')->addMessage('Layouts updated');
            return $this->redirect()->toRoute('layoutList');
        }
    }

    /**
     * Send a file to the browser
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function downloadAction()
    {
        $layout_id = $this->getRouteMatch()->getParam('id', NULL);
        if(!empty($layout_id))
        {
            $layout = Layout\Model::fromId($layout_id);
            if(empty($layout))
            {
                $this->flashMessenger()->setNameSpace('error')->addMessage('This layout can not be download');
                return $this->redirect()->toRoute('layoutEdit', array('id' => $layout_id));
            }

            $content = $layout->getContent();
            $filename = $layout->getIdentifier() . 'phtml';
        }
        else
        {
            $layouts = new Layout\Collection();
            $children = $layouts->getLayouts();
            $zip = new ZipArchive;
            $tmp_filename = tempnam(sys_get_temp_dir(), 'zip');
            $res = $zip->open($tmp_filename, ZipArchive::CREATE);
            if($res === TRUE)
            {
                foreach($children as $child)
                {
                    $zip->addFromString($child->getIdentifier() . '.phtml', $child->getContent());
                }

                $zip->close();
                $content = file_get_contents($tmp_filename);
                $filename = 'layout.zip';
                unlink($tmp_filename);
            }
        }

        if(empty($content) or empty($filename))
        {
            $this->flashMessenger()->setNameSpace('error')->addMessage('Can not save layouts');
            return $this->redirect()->toRoute('layoutList');
        }

        $headers = new Headers();
        $headers->addHeaderLine("Pragma", "public")
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

