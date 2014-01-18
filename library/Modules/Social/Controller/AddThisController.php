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
 * @category   Gc_Library
 * @package    Modules
 * @subpackage Social\Controller
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Social\Controller;

use Gc\Module\Controller\AbstractController;
use Social\Model;
use Social\Form;

/**
 * IndexController
 *
 * @category   Gc_Library
 * @package    Modules
 * @subpackage Social\Controller
 */
class AddThisController extends AbstractController
{
    protected $form;
    protected $model;

    /**
     * Initialize controller
     *
     * @return void
     */
    public function init()
    {
        if (!$this->params('isForwarded')) {
            $this->form  = new Form\AddThis();
            $this->model = $this->getServiceLocator()->get('AddThisModel');
            $this->form->setModel($this->model);
        }
    }
    /**
     * Index action
     *
     * @return array
     */
    public function indexAction()
    {
        if ($this->getRequest()->isPost() and !$this->params('isForwarded')) {
            $postData = $this->getRequest()->getPost()->toArray();
            $this->form->setData($postData);
            foreach ($postData as $idx => $post) {
                if (is_array($post)) {
                    $this->form->addWidget($idx, $post);
                }
            }

            if ($this->form->isValid()) {
                $this->model->addWidgets($this->form->getData(), true);
                $this->flashMessenger()->addSuccessMessage('Widgets saved');
                return $this->redirect()->toRoute('module/social/addthis');
            }

            $this->flashMessenger()->addErrorMessage('Cannot saved widgets');
            return $this->redirect()->toRoute('module/social/addthis');
        }


        if ($this->params('isForwarded') != 'config') {
            $this->form->prepareConfig();
        }
        if ($this->params('isForwarded') != 'widgets') {
            $this->form->prepareWidgets();
        }

        return array(
            'addThis' => $this->model,
            'form'    => $this->form
        );
    }

    /**
     * Save widget
     *
     * @return mixed
     */
    public function addWidgetAction()
    {
        $this->form->addWidget('widget-add');

        $postData = $this->getRequest()->getPost()->toArray();
        $this->form->setData($postData);

        if ($this->getRequest()->isPost()) {
            if ($this->form->isValid()) {
                $this->model->addWidgets($this->form->getData());
                $this->flashMessenger()->addSuccessMessage('Widget added');
                return $this->redirect()->toRoute('module/social/addthis');
            }
        }

        $this->flashMessenger()->addErrorMessage('Cannot saved widget');
        $this->useFlashMessenger();
        return $this->forward()->dispatch(
            'AddThisController',
            array(
                'action' => 'index',
                'isForwarded' => 'widgets'
            )
        );
    }

    /**
     * Save configuration
     *
     * @return mixed
     */
    public function configAction()
    {
        $this->form->prepareConfig();
        $postData = $this->getRequest()->getPost()->toArray();
        $this->form->setData($postData);

        if ($this->getRequest()->isPost()) {
            if ($this->form->isValid()) {
                $data = $this->form->getData();
                if (!empty($data['config']['username']) and
                    !empty($data['config']['password']) and
                    !empty($data['config']['profile_id'])
                ) {
                    $data['config']['valide_credential'] = true;
                }

                $data['config']['valide_credential'] = false;

                $this->model->setConfig($data);
                $this->flashMessenger()->addSuccessMessage('Configuration saved');
                return $this->redirect()->toRoute('module/social/addthis');
            }
        }


        $this->flashMessenger()->addErrorMessage('Cannot saved configuration');
        $this->useFlashMessenger();
        return $this->forward()->dispatch(
            'AddThisController',
            array(
                'action' => 'index',
                'isForwarded' => 'config'
            )
        );
    }
}
