<?php
/**
 * This source file is part of Got CMS.
 *
 * Got CMS is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Got CMS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with Got CMS. If not, see <http://www.gnu.org/licenses/gpl-3.0.html>.
 *
 * PHP Version >=5.3
 *
 * @category Controller
 * @package  Development\Controller
 * @author   Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license  GNU/GPL http://www.gnu.org/licenses/gpl-3.0.html
 * @link     http://www.got-cms.com
 * @license  http://www.gnu.org/licenses/gpl-3.0.html
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

    public function indexAction()
    {
    }

    /**
     * Create Layout
     */
    public function createAction()
    {
        $layout_form = new LayoutForm();
        $layout_form->setAction($this->url()->fromRoute('layoutCreate'));

        if($this->getRequest()->isPost())
        {
            if(!$layout_form->isValid($this->getRequest()->post()->toArray()))
            {
            }
            else
            {
                $layout = new Layout\Model();
                $layout->setName($layout_form->getValue('name'));
                $layout->setIdentifier($layout_form->getValue('identifier'));
                $layout->setDescription($layout_form->getValue('description'));
                $layout->setContent($layout_form->getValue('content'));
                $layout->save();

                $this->redirect()->toRoute('layoutCreate');
                return;
            }
        }

        return array('form' => $layout_form);
    }

    /**
     * List all layouts
     */
    public function listAction()
    {
        $layouts = new Layout\Collection();
        return array('layouts' => $layouts->getLayouts());
    }

    /**
     * Edit layout
     */
    public function editAction()
    {
        $layout_id = $this->getRouteMatch()->getParam('id', NULL);
        $layout = Layout\Model::fromId($layout_id);
        if(empty($layout_id) or empty($layout))
        {
            return $this->redirect()->toRoute('layoutList');
        }

        $layout_form = new LayoutForm();
        $layout_form->setAction($this->url()->fromRoute('layoutEdit', array('id' => $layout_id)));
        $layout_form->loadValues($layout);

        if($this->getRequest()->isPost())
        {
            $data = $this->getRequest()->post()->toArray();
            if($layout_form->isValid($data))
            {
                $layout->addData($layout_form->getValues(TRUE));
                $layout->save();
                $this->redirect()->toRoute('layoutEdit', array('id' => $layout_id));
            }

            $layout_form->populate($data);
        }

        return array('form' => $layout_form);
    }

    /**
     * Delete layout
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

