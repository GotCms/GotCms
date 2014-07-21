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
use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\Session\Container as SessionContainer;
use Zend\View\Model\JsonModel;

/**
 * Extension of AbstractActionController
 *
 * @category   Gc
 * @package    Library
 * @subpackage Mvc\Controller
 */
class RestAction extends AbstractRestfulController
{
    /**
     * RouteMatch
     *
     * @var \Zend\Mvc\Router\RouteMatch
     */
    protected $routeMatch = null;

    /**
     * Session storage
     *
     * @var \Zend\Session\Container
     */
    protected $session = null;

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
     * @return SessionContainer
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

        $this->layout()->setVariable('flashMessages', $flashMessages);
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
}
