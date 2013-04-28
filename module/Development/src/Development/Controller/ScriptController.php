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
     * @var array $aclPage
     */
    protected $aclPage = array('resource' => 'Development', 'permission' => 'script');

    /**
     * List all scripts
     *
     * @return \Zend\Script\Model\ScriptModel
     */
    public function indexAction()
    {
        $scriptCollection = new Script\Collection();
        return array('scripts' => $scriptCollection->getScripts());
    }

    /**
     * Create script
     *
     * @return \Zend\Script\Model\ScriptModel
     */
    public function createAction()
    {
        $scriptForm = new ScriptForm();
        $scriptForm->setAttribute('action', $this->url()->fromRoute('scriptCreate'));

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost()->toArray();
            $scriptForm->setData($data);
            if (!$scriptForm->isValid()) {
                $this->flashMessenger()->addErrorMessage('Can not save script');
                $this->useFlashMessenger();
            } else {
                $scriptModel = new Script\Model();
                $scriptModel->setName($scriptForm->getValue('name'));
                $scriptModel->setIdentifier($scriptForm->getValue('identifier'));
                $scriptModel->setDescription($scriptForm->getValue('description'));
                $scriptModel->setContent($scriptForm->getValue('content'));
                $scriptModel->save();

                $this->flashMessenger()->addSuccessMessage('This script has been created');
                return $this->redirect()->toRoute('scriptEdit', array('id' => $scriptModel->getId()));
            }
        }

        return array('form' => $scriptForm);
    }

    /**
     * Edit script
     *
     * @return \Zend\Script\Model\ScriptModel
     */
    public function editAction()
    {
        $scriptId    = $this->getRouteMatch()->getParam('id', null);
        $scriptModel = Script\Model::fromId($scriptId);
        if (empty($scriptId) or empty($scriptModel)) {
            return $this->redirect()->toRoute('scriptList');
        }

        $scriptForm = new ScriptForm();
        $scriptForm->setAttribute('action', $this->url()->fromRoute('scriptEdit', array('id' => $scriptId)));
        $scriptForm->loadValues($scriptModel);

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost()->toArray();
            $scriptForm->setData($data);
            if (!$scriptForm->isValid()) {
                $this->flashMessenger()->addErrorMessage('Can not save script');
                $this->useFlashMessenger();
            } else {
                $scriptModel->setName($scriptForm->getValue('name'));
                $scriptModel->setIdentifier($scriptForm->getValue('identifier'));
                $scriptModel->setDescription($scriptForm->getValue('description'));
                $scriptModel->setContent($scriptForm->getValue('content'));
                $scriptModel->save();

                $this->flashMessenger()->addSuccessMessage('This script has been saved');
                return $this->redirect()->toRoute('scriptEdit', array('id' => $scriptId));
            }
        }

        return array('form' => $scriptForm, 'scriptId' => $scriptId);
    }

    /**
     * Delete Script
     *
     * @return \Zend\Script\Model\ScriptModel
     */
    public function deleteAction()
    {
        $script = Script\Model::fromId($this->getRouteMatch()->getParam('id', null));
        if (!empty($script) and $script->delete()) {
            return $this->returnJson(array('success' => true, 'message' => 'This script has been deleted'));
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
        $scriptId = $this->getRouteMatch()->getParam('id', null);
        if (!empty($scriptId)) {
            $script = Script\Model::fromId($scriptId);
            if (empty($script)or empty($_FILES['upload']['tmp_name']) or $_FILES['upload']['error'] != UPLOAD_ERR_OK) {
                $this->flashMessenger()->addErrorMessage('Can not upload script');
                return $this->redirect()->toRoute('scriptEdit', array('id' => $scriptId));
            }

            $script->setContent(file_get_contents($_FILES['upload']['tmp_name']));
            $script->save();

            $this->flashMessenger()->addSuccessMessage('Script updated');
            return $this->redirect()->toRoute('scriptEdit', array('id' => $scriptId));
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
        $scriptId = $this->getRouteMatch()->getParam('id', null);
        if (!empty($scriptId)) {
            $script = Script\Model::fromId($scriptId);
            if (empty($script)) {
                $this->flashMessenger()->addErrorMessage('This script can not be download');
                return $this->redirect()->toRoute('scriptEdit', array('id' => $scriptId));
            }

            $content  = $script->getContent();
            $filename = $script->getIdentifier() . 'phtml';
        } else {
            $scripts     = new Script\Collection();
            $children    = $scripts->getScripts();
            $zip         = new ZipArchive;
            $tmpFilename = tempnam(sys_get_temp_dir(), 'zip');
            $res         = $zip->open($tmpFilename, ZipArchive::CREATE);
            if ($res === true) {
                foreach ($children as $child) {
                    $zip->addFromString($child->getIdentifier() . '.phtml', $child->getContent());
                }

                $zip->close();
                $content  = file_get_contents($tmpFilename);
                $filename = 'scripts.zip';
                unlink($tmpFilename);
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
