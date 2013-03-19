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
use Development\Form\Script as ScriptForm;
use Gc\Script;
use Zend\Http\Headers;
use Zend\File\Transfer\Adapter\Http as FileTransfer;
use ZipArchive;

/**
 * Script controller
 *
 * @category   Gc_Application
 * @package    Development
 * @subpackage Controller
 */
class ScriptController extends Action
{
    /**
     * Contains information about acl
     *
     * @var array $_aclPage
     */
    protected $aclPage = array('resource' => 'Development', 'permission' => 'script');

    /**
     * List all scripts
     *
     * @return \Zend\Script\Model\ScriptModel
     */
    public function indexAction()
    {
        $script_collection = new Script\Collection();
        return array('scripts' => $script_collection->getScripts());
    }

    /**
     * Create script
     *
     * @return \Zend\Script\Model\ScriptModel
     */
    public function createAction()
    {
        $script_form = new ScriptForm();
        $script_form->setAttribute('action', $this->url()->fromRoute('scriptCreate'));

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost()->toArray();
            $script_form->setData($data);
            if (!$script_form->isValid()) {
                $this->flashMessenger()->addErrorMessage('Can not save script');
                $this->useFlashMessenger();
            } else {
                $script_model = new Script\Model();
                $script_model->setName($script_form->getValue('name'));
                $script_model->setIdentifier($script_form->getValue('identifier'));
                $script_model->setDescription($script_form->getValue('description'));
                $script_model->setContent($script_form->getValue('content'));
                $script_model->save();

                $this->flashMessenger()->addSuccessMessage('This script has been created');
                return $this->redirect()->toRoute('scriptEdit', array('id' => $script_model->getId()));
            }
        }

        return array('form' => $script_form);
    }

    /**
     * Edit script
     *
     * @return \Zend\Script\Model\ScriptModel
     */
    public function editAction()
    {
        $script_id    = $this->getRouteMatch()->getParam('id', null);
        $script_model = Script\Model::fromId($script_id);
        if (empty($script_id) or empty($script_model)) {
            return $this->redirect()->toRoute('scriptList');
        }

        $script_form = new ScriptForm();
        $script_form->setAttribute('action', $this->url()->fromRoute('scriptEdit', array('id' => $script_id)));
        $script_form->loadValues($script_model);

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost()->toArray();
            $script_form->setData($data);
            if (!$script_form->isValid()) {
                $this->flashMessenger()->addErrorMessage('Can not save script');
                $this->useFlashMessenger();
            } else {
                $script_model->setName($script_form->getValue('name'));
                $script_model->setIdentifier($script_form->getValue('identifier'));
                $script_model->setDescription($script_form->getValue('description'));
                $script_model->setContent($script_form->getValue('content'));
                $script_model->save();

                $this->flashMessenger()->addSuccessMessage('This script has been saved');
                return $this->redirect()->toRoute('scriptEdit', array('id' => $script_id));
            }
        }

        return array('form' => $script_form, 'scriptId' => $script_id);
    }

    /**
     * Delete Script
     *
     * @return \Zend\Script\Model\ScriptModel
     */
    public function deleteAction()
    {
        $script = Script\Model::fromId($this->getRouteMatch()->getParam('id', null));
        if (!empty($script)) {
            if ($script->delete()) {
                return $this->returnJson(array('success' => true, 'message' => 'This script has been deleted'));
            }
        }

        return $this->returnJson(array('success' => false, 'message' => 'Script does not exists'));
    }

    /**
     * Upload a file to the server
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function uploadAction()
    {
        $script_id = $this->getRouteMatch()->getParam('id', null);
        if (!empty($script_id)) {
            $script = Script\Model::fromId($script_id);
            if (empty($script)or empty($_FILES['upload']['tmp_name']) or $_FILES['upload']['error'] != UPLOAD_ERR_OK) {
                $this->flashMessenger()->addErrorMessage('Can not upload script');
                return $this->redirect()->toRoute('scriptEdit', array('id' => $script_id));
            }

            $script->setContent(file_get_contents($_FILES['upload']['tmp_name']));
            $script->save();

            $this->flashMessenger()->addSuccessMessage('Script updated');
            return $this->redirect()->toRoute('scriptEdit', array('id' => $script_id));
        } else {
            if (empty($_FILES['upload'])) {
                $this->flashMessenger()->addErrorMessage('Can not upload scripts');
                return $this->redirect()->toRoute('scriptList');
            }

            foreach ($_FILES['upload']['name'] as $idx => $name) {
                if ($_FILES['upload']['error'][$idx] != UPLOAD_ERR_OK) {
                    continue;
                }

                $identifier = preg_replace('~\.phtml$~', '', $name);
                $script     = Script\Model::fromIdentifier($identifier);
                if (empty($script)) {
                    continue;
                }

                $script->setContent(file_get_contents($_FILES['upload']['tmp_name'][$idx]));
                $script->save();
            }

            $this->flashMessenger()->addSuccessMessage('Scripts updated');
            return $this->redirect()->toRoute('scriptList');
        }
    }

    /**
     * Send a file to the browser
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function downloadAction()
    {
        $script_id = $this->getRouteMatch()->getParam('id', null);
        if (!empty($script_id)) {
            $script = Script\Model::fromId($script_id);
            if (empty($script)) {
                $this->flashMessenger()->addErrorMessage('This script can not be download');
                return $this->redirect()->toRoute('scriptEdit', array('id' => $script_id));
            }

            $content  = $script->getContent();
            $filename = $script->getIdentifier() . 'phtml';
        } else {
            $scripts      = new Script\Collection();
            $children     = $scripts->getScripts();
            $zip          = new ZipArchive;
            $tmp_filename = tempnam(sys_get_temp_dir(), 'zip');
            $res          = $zip->open($tmp_filename, ZipArchive::CREATE);
            if ($res === true) {
                foreach ($children as $child) {
                    $zip->addFromString($child->getIdentifier() . '.phtml', $child->getContent());
                }

                $zip->close();
                $content  = file_get_contents($tmp_filename);
                $filename = 'scripts.zip';
                unlink($tmp_filename);
            }
        }

        if (empty($content) or empty($filename)) {
            $this->flashMessenger()->addErrorMessage('Can not save scripts');
            return $this->redirect()->toRoute('scriptList');
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
