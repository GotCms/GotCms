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
        $this->form  = new Form\AddThis();
        $this->model = new Model\AddThis(
            $this->getServiceLocator()->get('CoreConfig')
        );
        $this->form->setModel($this->model);
    }
    /**
     * Index action
     *
     * @return array
     */
    public function indexAction()
    {
        if ($this->getRequest()->isPost()) {
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

        $this->form->prepareConfig();
        $this->form->prepareWidgets();
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
        return $this->redirect()->toRoute('module/social/addthis');
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
                $this->model->setConfig($this->form->getData());
                $this->flashMessenger()->addSuccessMessage('Configuration saved');
                return $this->redirect()->toRoute('module/social/addthis');
            }
        }

        $this->flashMessenger()->addErrorMessage('Cannot saved configuration');
        return $this->redirect()->toRoute('module/social/addthis');
    }
}
