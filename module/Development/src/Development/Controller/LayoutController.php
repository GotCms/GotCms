<?php
/**
 * This source file is part of Got CMS.
 *
 * Got CMS is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Got CMS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License along
 * with Got CMS. If not, see <http://www.gnu.org/licenses/lgpl-3.0.html>.
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
    Gc\Layout;

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
            $data = $this->getRequest()->post()->toArray();
            $layout_form->setData($data);
            if($layout_form->isValid())
            {
                $layout_model = new Layout\Model();
                $layout_model->setName($layout_form->getValue('name'));
                $layout_model->setIdentifier($layout_form->getValue('identifier'));
                $layout_model->setDescription($layout_form->getValue('description'));
                $layout_model->setContent($layout_form->getValue('content'));
                $layout_model->save();

                $this->redirect()->toRoute('layoutCreate');
                return;
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
            $data = $this->getRequest()->post()->toArray();

            $layout_form->setData($data);
            if($layout_form->isValid())
            {
                $layout_model->setName($layout_form->getValue('name'));
                $layout_model->setIdentifier($layout_form->getValue('identifier'));
                $layout_model->setDescription($layout_form->getValue('description'));
                $layout_model->setContent($layout_form->getValue('content'));
                $layout_model->save();
                return $this->redirect()->toRoute('layoutEdit', array('id' => $layout_id));
            }
        }

        return array('form' => $layout_form);
    }

    /**
     * Delete layout
     * @return \Zend\View\Model\ViewModel
     */
    public function deleteAction()
    {
        $layout_id = $this->getRouteMatch()->getParam('id', NULL);
        $layout = Layout\Model::fromId($layout_id);
        if(empty($layout_id) or empty($layout) or !$layout->delete())
        {
            $this->flashMessenger()->setNameSpace('error')->addMessage('Can not delete this layout');
        }
        else
        {
            $this->flashMessenger()->setNameSpace('success')->addMessage('This layout has been deleted');
        }

        return $this->redirect()->toRoute('layoutList');
    }
}

