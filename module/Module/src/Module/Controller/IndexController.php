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
 * @package    Module
 * @subpackage Controller
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Module\Controller;

use Gc\Mvc\Controller\Action;
use Gc\Module\Collection as ModuleCollection;
use Gc\Module\Model as ModuleModel;
use Module\Form\Module as ModuleForm;
use Zend\Filter;
use Zend\View\Model\ViewModel;

/**
 * Index controller
 *
 * @category   Gc_Application
 * @package    Module
 * @subpackage Controller
 */
class IndexController extends Action
{
    /**
     * Contains information about acl
     *
     * @var array
     */
    protected $aclPage = array('resource' => 'modules');

    /**
     * List all modules
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        $collection = new ModuleCollection();
        $filter     = new Filter\Word\CamelCaseToSeparator;
        $filter->setSeparator('-');
        $filterChain = new Filter\FilterChain();
        $filterChain->attach($filter)
            ->attach(new Filter\StringToLower());

        foreach ($collection->getModules() as $module) {
            $module->setData('route', $filterChain->filter($module->getName()));
        }

        return array('modules' => $collection->getModules());
    }

    /**
     * Install module
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function installAction()
    {
        $form = new ModuleForm();
        if ($this->getRequest()->isPost()) {
            $form->setData($this->getRequest()->getPost()->toArray());
            if (!$form->isValid()) {
                $this->flashMessenger()->addErrorMessage('Invalid module');
                $this->useFlashMessenger();
            } else {
                $moduleName = $form->getInputFilter()->get('module')->getValue();
                $moduleId   = ModuleModel::install($this->getServiceLocator()->get('CustomModules'), $moduleName);
                if ($moduleId === false) {
                    $this->flashMessenger()->addErrorMessage('Can not install this module');
                    return $this->redirect()->toRoute('module');
                } else {
                    $this->flashMessenger()->addSuccessMessage('Module installed');
                    return $this->redirect()->toRoute('module');
                }
            }

        }

        return array('form' => $form);
    }

    /**
     * Uninstall module
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function uninstallAction()
    {
        $moduleId    = $this->getRouteMatch()->getParam('id');
        $modules     = $this->getServiceLocator()->get('CustomModules');
        $moduleModel = ModuleModel::fromId($moduleId);
        if (!empty($moduleModel)) {
            $module = $modules->getModule($moduleModel->getName());
            if (ModuleModel::uninstall($module, $moduleModel)) {
                return $this->returnJson(array('success' => true, 'message' => 'Module uninstalled'));
            }
        }

        return $this->returnJson(array('success' => false, 'message' => 'Can\'t uninstall module'));
    }
}
