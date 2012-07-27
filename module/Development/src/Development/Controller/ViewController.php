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
    Development\Form\View as ViewForm,
    Gc\View;

class ViewController extends Action
{
    /**
     * Contains information about acl
     * @var array $_acl_page
     */
    protected $_acl_page = array('resource' => 'Development', 'permission' => 'view');

    /**
     * List all views
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        $view_collection = new View\Collection();
        return array('views' => $view_collection->getViews());
    }

    /**
     * Create view
     * @return \Zend\View\Model\ViewModel
     */
    public function createAction()
    {
        $view_form = new ViewForm();
        $view_form->setAttribute('action', $this->url()->fromRoute('viewCreate'));

        if($this->getRequest()->isPost())
        {
            $data = $this->getRequest()->getPost()->toArray();
            $view_form->setData($data);
            if(!$view_form->isValid())
            {
                $this->flashMessenger()->setNameSpace('error')->addMessage('Can not save view');
                $this->useFlashMessenger();
            }
            else
            {
                $view_model = new View\Model();
                $view_model->setName($view_form->getValue('name'));
                $view_model->setIdentifier($view_form->getValue('identifier'));
                $view_model->setDescription($view_form->getValue('description'));
                $view_model->setContent($view_form->getValue('content'));
                $view_model->save();

                $this->flashMessenger()->setNameSpace('success')->addMessage('This view has been created');
                return $this->redirect()->toRoute('viewEdit', array('id' => $view_model->getId()));
            }
        }

        return array('form' => $view_form);
    }

    /**
     * Edit view
     * @return \Zend\View\Model\ViewModel
     */
    public function editAction()
    {
        $view_id = $this->getRouteMatch()->getParam('id', NULL);
        $view_model = View\Model::fromId($view_id);
        if(empty($view_id) or empty($view_model))
        {
            return $this->redirect()->toRoute('viewList');
        }

        $view_form = new ViewForm();
        $view_form->setAttribute('action', $this->url()->fromRoute('viewEdit',array('id' => $view_id)));
        $view_form->loadValues($view_model);

        if($this->getRequest()->isPost())
        {
            $data = $this->getRequest()->getPost()->toArray();
            $view_form->setData($data);
            if(!$view_form->isValid())
            {
                $this->flashMessenger()->setNameSpace('error')->addMessage('Can not save view');
                $this->useFlashMessenger();
            }
            else
            {
                $view_model->setName($view_form->getValue('name'));
                $view_model->setIdentifier($view_form->getValue('identifier'));
                $view_model->setDescription($view_form->getValue('description'));
                $view_model->setContent($view_form->getValue('content'));
                $view_model->save();

                $this->flashMessenger()->setNameSpace('success')->addMessage('This view has been edited');
                return $this->redirect()->toRoute('viewEdit', array('id' => $view_id));
            }
        }

        return array('form' => $view_form);
    }

    /**
     * Delete View
     * @return \Zend\View\Model\ViewModel
     */
    public function deleteAction()
    {
        $view_id = $this->getRouteMatch()->getParam('id', NULL);
        $view = View\Model::fromId($view_id);
        if(empty($view_id) or empty($view))
        {
            $this->flashMessenger()->setNameSpace('error')->addMessage('Can not delete this view');
        }
        else
        {
            $this->flashMessenger()->setNameSpace('success')->addMessage('This view has been deleted');
            $view->delete();
        }

        return $this->redirect()->toRoute('viewList');
    }
}
