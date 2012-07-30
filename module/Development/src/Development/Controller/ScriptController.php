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
    Development\Form\Script as ScriptForm,
    Gc\Script;

class ScriptController extends Action
{
    /**
     * Contains information about acl
     * @var array $_acl_page
     */
    protected $_acl_page = array('resource' => 'Development', 'permission' => 'script');

    /**
     * List all scripts
     * @return \Zend\Script\Model\ScriptModel
     */
    public function indexAction()
    {
        $script_collection = new Script\Collection();
        return array('scripts' => $script_collection->getScripts());
    }

    /**
     * Create script
     * @return \Zend\Script\Model\ScriptModel
     */
    public function createAction()
    {
        $script_form = new ScriptForm();
        $script_form->setAttribute('action', $this->url()->fromRoute('scriptCreate'));

        if($this->getRequest()->isPost())
        {
            $data = $this->getRequest()->getPost()->toArray();
            $script_form->setData($data);
            if(!$script_form->isValid())
            {
                $this->flashMessenger()->setNameSpace('error')->addMessage('Can not save script');
                $this->useFlashMessenger();
            }
            else
            {
                $script_model = new Script\Model();
                $script_model->setName($script_form->getValue('name'));
                $script_model->setIdentifier($script_form->getValue('identifier'));
                $script_model->setDescription($script_form->getValue('description'));
                $script_model->setContent($script_form->getValue('content'));
                $script_model->save();

                $this->flashMessenger()->setNameSpace('success')->addMessage('This script has been created');
                return $this->redirect()->toRoute('scriptEdit', array('id' => $script_model->getId()));
            }
        }

        return array('form' => $script_form);
    }

    /**
     * Edit script
     * @return \Zend\Script\Model\ScriptModel
     */
    public function editAction()
    {
        $script_id = $this->getRouteMatch()->getParam('id', NULL);
        $script_model = Script\Model::fromId($script_id);
        if(empty($script_id) or empty($script_model))
        {
            return $this->redirect()->toRoute('scriptList');
        }

        $script_form = new ScriptForm();
        $script_form->setAttribute('action', $this->url()->fromRoute('scriptEdit',array('id' => $script_id)));
        $script_form->loadValues($script_model);

        if($this->getRequest()->isPost())
        {
            $data = $this->getRequest()->getPost()->toArray();
            $script_form->setData($data);
            if(!$script_form->isValid())
            {
                $this->flashMessenger()->setNameSpace('error')->addMessage('Can not save script');
                $this->useFlashMessenger();
            }
            else
            {
                $script_model->setName($script_form->getValue('name'));
                $script_model->setIdentifier($script_form->getValue('identifier'));
                $script_model->setDescription($script_form->getValue('description'));
                $script_model->setContent($script_form->getValue('content'));
                $script_model->save();

                $this->flashMessenger()->setNameSpace('success')->addMessage('This script has been edited');
                return $this->redirect()->toRoute('scriptEdit', array('id' => $script_id));
            }
        }

        return array('form' => $script_form, 'scriptId' => $script_id);
    }

    /**
     * Delete Script
     * @return \Zend\Script\Model\ScriptModel
     */
    public function deleteAction()
    {
        $script_id = $this->getRouteMatch()->getParam('id', NULL);
        $script = Script\Model::fromId($script_id);
        if(empty($script_id) or empty($script))
        {
            $this->flashMessenger()->setNameSpace('error')->addMessage('Can not delete this script');
        }
        else
        {
            $this->flashMessenger()->setNameSpace('success')->addMessage('This script has been deleted');
            $script->delete();
        }

        return $this->redirect()->toRoute('scriptList');
    }

    /**
     * Upload a file to the server
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function uploadAction()
    {
        $script_id = $this->getRouteMatch()->getParam('id', NULL);
        $script = Script\Model::fromId($script_id);
        if(empty($script_id) or empty($script))
        {
            $this->flashMessenger()->setNameSpace('success')->addMessage('This script can not be download');
            return $this->redirect()->toRoute('scriptEdit', array('id' => $script_id));
        }
    }

    /**
     * Send a file to the browser
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function downloadAction()
    {
        $script_id = $this->getRouteMatch()->getParam('id', NULL);
        $script = Script\Model::fromId($script_id);
        if(empty($script_id) or empty($script))
        {
            $this->flashMessenger()->setNameSpace('success')->addMessage('This script can not be download');
            return $this->redirect()->toRoute('scriptEdit', array('id' => $script_id));
        }

        $headers = new Headers();
        $headers->addHeaderLine("Pragma", "public")
            ->addHeaderLine('Cache-control', 'must-revalidate, post-check=0, pre-check=0')
            ->addHeaderLine('Cache-control', 'private')
            ->addHeaderLine('Expires', -1)
            ->addHeaderLine('Content-Type', 'application/octet-stream')
            ->addHeaderLine('Content-Transfer-Encoding', 'binary')
            ->addHeaderLine('Content-Length', strlen($script->getContent()))
            ->addHeaderLine('Content-Disposition', 'attachment; filename=' . $script->getIdentifier(). '.phtml');

        $response = $this->getResponse();
        $response->setHeaders($headers);

        $response->setContent($script->getContent());

        return $response;
    }
}
