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
 * @category   Gc
 * @package    Library
 * @subpackage Mvc\Controller
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Gc\Mvc\Controller;

use Gc\Event\StaticEventManager;
use Gc\Module\Model as ModuleModel;
use Gc\User\Acl;
use Gc\User\Model as UserModel;
use Gc\User\Role\Model as RoleModel;
use Gc\Registry;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\MvcEvent;
use Zend\Session\Container as SessionContainer;
use Zend\View\Model\JsonModel;

/**
 * Extension of AbstractActionController
 *
 * @category   Gc
 * @package    Library
 * @subpackage Mvc\Controller
 */
class Action extends AbstractActionController
{
    /**
     * Route available for installer
     *
     * @var array
     */
    protected $installerRoutes = array(
        'install',
        'install/check-config',
        'install/license',
        'install/database',
        'install/configuration',
        'install/complete'
    );

    /**
     * RouteMatch
     *
     * @var \Zend\Mvc\Router\Http\RouteMatch
     */
    protected $routeMatch = null;

    /**
     * Session storage
     *
     * @var \Zend\Session\Storage\SessionStorage
     */
    protected $session = null;

    /**
     * Execute the request
     *
     * @param MvcEvent $e Mvc Event
     *
     * @return mixed
     */
    public function onDispatch(MvcEvent $e)
    {
        $resultResponse = $this->construct();
        if (!empty($resultResponse)) {
            return $resultResponse;
        }

        $this->init();
        return parent::onDispatch($e);
    }

    /**
     * Initiliaze
     *
     * @return void
     */
    public function init()
    {

    }

    /**
     * Constructor
     *
     * @return void
     */
    protected function construct()
    {
        $module    = $this->getRouteMatch()->getParam('module');
        $routeName = $this->getRouteMatch()->getMatchedRouteName();

        /**
         * Installation check, and check on removal of the install directory.
         */
        if (!file_exists(GC_APPLICATION_PATH . '/config/autoload/global.php')
            and !in_array($routeName, $this->installerRoutes)
        ) {
            return $this->redirect()->toRoute('install');
        } elseif (!in_array($routeName, $this->installerRoutes)) {
            $auth = $this->getServiceLocator()->get('Auth');
            if (!$auth->hasIdentity()) {
                if (!in_array(
                    $routeName,
                    array(
                        'config/user/login',
                        'config/user/forgot-password',
                        'config/user/forgot-password-key',
                        'cms'
                    )
                )
                ) {
                    return $this->redirect()->toRoute(
                        'config/user/login',
                        array('redirect' => base64_encode($this->getRequest()->getRequestUri()))
                    );
                }
            } else {
                if (!in_array($routeName, array('config/user/forbidden', 'config/user/logout'))) {
                    $this->checkAcl($auth->getIdentity());
                }
            }
        }

        $this->layout()->module  = strtolower($module);
        $this->layout()->version = \Gc\Version::VERSION;

        $this->useFlashMessenger(false);
    }

    /**
     * Return matched route
     *
     * @return \Zend\Mvc\Router\Http\RouteMatch
     */
    public function getRouteMatch()
    {
        if (empty($this->routeMatch)) {
            $this->routeMatch = $this->getEvent()->getRouteMatch();
        }

        return $this->routeMatch;
    }

    /**
     * Get session storage
     *
     * @return \Zend\Session\Storage\SessionStorage
     */
    public function getSession()
    {
        if ($this->session === null) {
            $this->session = new SessionContainer();
        }

        return $this->session;
    }

    /**
     * Return json model
     *
     * @param array $data Data
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function returnJson(array $data)
    {
        $jsonModel = new JsonModel();
        $jsonModel->setVariables($data);
        $jsonModel->setTerminal(true);

        return $jsonModel;
    }

    /**
     * Initiliaze flash messenger
     *
     * @param boolean $forceDisplay Force display
     *
     * @return void
     */
    public function useFlashMessenger($forceDisplay = true)
    {
        $flashMessenger = $this->flashMessenger();
        $flashMessages  = array();
        foreach (array('error', 'success', 'info', 'warning') as $namespace) {
            $flashNamespace = $flashMessenger->setNameSpace($namespace);
            if ($forceDisplay) {
                if ($flashNamespace->hasCurrentMessages()) {
                    $flashMessages[$namespace] = $flashNamespace->getCurrentMessages();
                    $flashNamespace->clearCurrentMessages();
                }
            } else {
                if ($flashNamespace->hasMessages()) {
                    $flashMessages[$namespace] = $flashNamespace->getMessages();
                }
            }
        }

        $this->layout()->flashMessages = $flashMessages;
    }

    /**
     * Retrieve event manager
     *
     * @return \Gc\Event\StaticEventManager
     */
    public function events()
    {
        return StaticEventManager::getInstance();
    }

    /**
     * Check user acl
     *
     * @param UserModel $userModel User model
     *
     * @return void|Zend\Http\Response
     */
    protected function checkAcl(UserModel $userModel)
    {
        if (!empty($this->aclPage) and $userModel->getRole()->getName() !== RoleModel::PROTECTED_NAME) {
            $isAllowed  = false;
            $permission = null;
            $acl        = $userModel->getAcl(true);
            if ($this->aclPage['resource'] == 'modules') {
                $moduleId = $this->getRouteMatch()->getParam('m');
                if (empty($moduleId)) {
                    $action     = $this->getRouteMatch()->getParam('action');
                    $permission = ($action === 'index' ? 'list' : $action);
                } else {
                    $moduleModel = ModuleModel::fromId($moduleId);
                    if (!empty($moduleModel)) {
                        $permission = $moduleModel->getName();
                    }
                }
            } else {
                $permission = empty($this->aclPage['permission']) ?
                    null :
                    $this->aclPage['permission'];
                if ($this->aclPage['permission'] != 'index' and
                    !in_array($this->aclPage['resource'], array('content', 'stats'))
                ) {
                    $action      = $this->getRouteMatch()->getParam('action');
                    $permission .= (!empty($permission) ? '/' : '') . ($action === 'index' ? 'list' : $action);
                }
            }

            if (!$acl->isAllowed(
                $userModel->getRole()->getName(),
                $this->aclPage['resource'],
                $permission
            )) {
                return $this->redirect()->toRoute('config/user/forbidden');
            }
        }
    }
}
