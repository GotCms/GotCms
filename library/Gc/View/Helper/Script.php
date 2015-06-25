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
 * @subpackage View\Helper
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Gc\View\Helper;

use Gc\Script\Model as ScriptModel;
use Zend\Http\PhpEnvironment\Request;
use Zend\Http\PhpEnvironment\Response;
use Zend\Mvc\Controller\PluginManager;
use Zend\ServiceManager\ServiceManager;
use Zend\View\Helper\AbstractHelper;

/**
 * Retrieve script from identifier
 *
 * @category   Gc
 * @package    Library
 * @subpackage View\Helper
 * @example In view: $this->script('identifier');
 */
class Script extends AbstractHelper
{
    /**
     * Script parameter
     *
     * @var array
     */
    protected $helperScriptParameters = array();

    /**
     * Http Request
     *
     * @var Request
     */
    protected $request;

    /**
     * Http Response
     *
     * @var Response
     */
    protected $response;

    /**
     * Controller plugin manager
     *
     * @var PluginManager
     */
    protected $pluginManager;

    /**
     * Service manager
     *
     * @var ServiceManager
     */
    protected $serviceManager;

    /**
     * Constructor
     *
     * @param ServiceManager $serviceManager Service manager
     */
    public function __construct(ServiceManager $serviceManager)
    {
        $this->request        = $serviceManager->get('Request');
        $this->response       = $serviceManager->get('Response');
        $this->pluginManager  = $serviceManager->get('ControllerPluginManager');
        $this->serviceManager = $serviceManager;
    }

    /**
     * Returns script from identifier.
     *
     * @param string $identifier Identifier
     * @param array  $params     Parameters
     *
     * @return mixed
     */
    public function __invoke($identifier, $params = array())
    {
        $script = ScriptModel::fromIdentifier($identifier);
        if (empty($script)) {
            return false;
        }

        $this->helperScriptParameters = $params;
        return include GC_TEMPLATE_PATH . '/script/' . $identifier . '.phtml';
    }

    /**
     * Returns param from name.
     *
     * @param string $name Parameter name
     *
     * @return mixed
     */
    public function getParam($name)
    {
        if (isset($this->helperScriptParameters[$name])) {
            return $this->helperScriptParameters[$name];
        }

        return null;
    }

    /**
     * Returns param from name.
     *
     * @return \Gc\Document\Model
     */
    public function getDocument()
    {
        return $this->getView()->currentDocument();
    }

    /**
     * Get Http Request instance.
     *
     * @return \Zend\Http\PhpEnvironment\Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Get Http Response instance.
     *
     * @return \Zend\Http\PhpEnvironment\Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Retrieve serviceManager instance
     *
     * @return ServiceManager
     */
    public function getServiceLocator()
    {
        return $this->serviceManager;
    }

    /**
     * Get plugin instance
     *
     * @param string     $name    Name of plugin to return
     * @param null|array $options Options to pass to plugin constructor (if not already instantiated)
     *
     * @return mixed
     */
    public function plugin($name, array $options = null)
    {
        return $this->pluginManager->get($name, $options);
    }

    /**
     * Method overloading: return/call plugins
     *
     * If the plugin is a functor, call it, passing the parameters provided.
     * Otherwise, return the plugin instance.
     *
     * @param string $method Method
     * @param array  $params Parameters
     *
     * @return mixed
     */
    public function __call($method, $params)
    {
        $plugin = $this->plugin($method);
        if (is_callable($plugin)) {
            return call_user_func_array($plugin, $params);
        }

        return $plugin;
    }
}
