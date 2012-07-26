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
 * @package  Config\Controller
 * @author   Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license  GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link     http://www.got-cms.com
 */

namespace Config\Controller;

use Gc\Mvc\Controller\Action,
    Gc\Core\Config,
    Config\Form\Config as configForm;

class CmsController extends Action
{
    /**
     * @var \Config\Form\Config $_form
     */
    protected $_form;

    /**
     * Contains information about acl
     * @var array
     */
    protected $_acl_page = array('resource' => 'Config', 'permission' => 'system');

    /**
     * Generate general configuration form
     *
     * @return void
     */
    public function editGeneralAction()
    {
        $this->_form = new configForm();
        $this->_form->initGeneral();
        return $this->forward()->dispatch('CmsController', array('action' => 'edit'));
    }

    /**
     * Generate system configuration form
     *
     * @return void
     */
    public function editSystemAction()
    {
        $this->_form = new configForm();
        $this->_form->initSystem();
        return $this->forward()->dispatch('CmsController', array('action' => 'edit'));
    }

    /**
     * Generate server configuration form
     *
     * @return void
     */
    public function editServerAction()
    {
        $this->_form = new configForm();
        $this->_form->initServer();
        return $this->forward()->dispatch('CmsController', array('action' => 'edit'));
    }

    /**
     * Generate form and display
     *
     * @return \Zend\View\Model\ViewModel|array
     */
    public function editAction()
    {
        $values = Config::getValues();
        $this->_form->setValues($values);

        if($this->getRequest()->isPost())
        {
            $this->_form->setData($this->getRequest()->getPost()->toArray());

            if(!$this->_form->isValid())
            {
                $this->flashMessenger()->setNameSpace('error')->addMessage('Can not save configuration');
                $this->useFlashMessenger();
            }
            else
            {
                $inputs = $this->_form->getInputFilter()->getValidInput();
                foreach($inputs as $input)
                {
                    if(method_exists($input, 'getName'))
                    {
                        Config::setValue($input->getName(), $input->getValue());
                    }
                }

                $this->flashMessenger()->setNameSpace('success')->addMessage('Configuration saved');
                return $this->redirect()->toRoute($this->getRouteMatch()->getMatchedRouteName());
            }
        }

        return array('form' => $this->_form);
    }
}
