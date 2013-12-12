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
 * @subpackage Module
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Gc\Module;

use Gc\Core\Object;
use Gc\View\Renderer;
use Gc\Registry;

/**
 * Abstract module bootstrap
 *
 * @category   Gc
 * @package    Library
 * @subpackage Module
 */
abstract class AbstractPlugin
{
    /**
     * Script parameter
     *
     * @var array
     */
    protected $pluginParameters = array();

    /**
     * Renderer
     *
     * @var \Gc\View\Renderer
     */
    protected $renderer;

    /**
     * Initialize
     *
     * @return void
     */
    public function init()
    {

    }

    /**
     * Set params
     *
     * @param array $array List of parameters
     *
     * @return Gc\Module\AbstractPlugin
     */
    public function setParams(array $array)
    {
        $this->pluginParameters = $array;

        return $this;
    }

    /**
     * Set params
     *
     * @param string $key   Key
     * @param string $value Value
     *
     * @return Gc\Module\AbstractPlugin
     */
    public function setParam($key, $value)
    {
        $this->pluginParameters[$key] = $value;

        return $this;
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
        if (isset($this->pluginParameters[$name])) {
            return $this->pluginParameters[$name];
        }

        return null;
    }

    /**
     * Render template
     *
     * @param string $name Name
     * @param array  $data Data
     *
     * @return string
     */
    public function render($name, array $data = array())
    {
        if (empty($this->renderer)) {
            $this->renderer = new Renderer();
        }

        return $this->renderer->render($name, $data);
    }

    /**
     * Add path in Zend\View\Resolver\TemplatePathStack
     *
     * @param string $dir Directory
     *
     * @return \Gc\Module\AbstractPlugin
     */
    public function addPath($dir)
    {
        if (empty($this->renderer)) {
            $this->renderer = new Renderer();
        }

        $this->renderer->addPath($dir);

        return $this;
    }

    /**
     * Get Http Request instance.
     *
     * @return \Zend\Http\PhpEnvironment\Request
     */
    public function getRequest()
    {
        return Registry::get('Application')->getRequest();
    }

    /**
     * Get Http Response instance.
     *
     * @return \Zend\Http\PhpEnvironment\Response
     */
    public function getResponse()
    {
        return Registry::get('Application')->getResponse();
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
        return Registry::get('Application')->getServiceManager()->get('controllerPluginManager')->get($name, $options);
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
