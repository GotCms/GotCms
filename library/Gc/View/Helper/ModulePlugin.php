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

use Zend\View\Helper\AbstractHelper;
use Gc\Exception;
use Gc\Module\Model as ModuleModel;
use Gc\Module\AbstractPlugin;

/**
 * Execute plugin module
 *
 * @category   Gc
 * @package    Library
 * @subpackage View\Helper
 * @example In view: $this->modulePlugin('blog', 'comment-list', $parameters);
 */
class ModulePlugin extends AbstractHelper
{
    /**
     * Lookup for camel case names.
     *
     * @var array
     */
    protected $camelCaseNames = array();

    /**
     * Registered services and cached values
     *
     * @var array
     */
    protected $instances = array();

    /**
     * Script parameter
     *
     * @var array
     */
    protected $modulePluginParameters = array();

    /**
     * Execute plugin module.
     *
     * @param string $moduleName Module name
     * @param string $pluginName Plugin name
     * @param array  $params     Parameters
     *
     * @return mixed
     */
    public function __invoke($moduleName, $pluginName, $params = array())
    {
        if (!$this->has($moduleName, $pluginName)) {
            return false;
        }

        $this->modulePluginParameters = $params;

        $instance = $this->get($moduleName, $pluginName);
        if (is_callable($instance)) {
            return call_user_func_array($instance, $params);
        }

        return $instance;
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
        if (isset($this->modulePluginParameters[$name])) {
            return $this->modulePluginParameters[$name];
        }

        return null;
    }

    /**
     * Validate the plugin
     *
     * Any plugin is considered valid in this context.
     *
     * @param mixed $plugin Plugin
     *
     * @return void
     * @throws Exception
     */
    public function validatePlugin($plugin)
    {
        if ($plugin instanceof AbstractPlugin) {
            return true;
        }

        throw new Exception(
            sprintf(
                'Plugin of type %s is invalid; must implement Gc\Module\AbstractPlugin',
                (is_object($plugin) ? get_class($plugin) : gettype($plugin))
            )
        );
    }

    /**
     * Retrieve a registered instance
     *
     * @param string $moduleName Module name
     * @param string $pluginName Plugin name
     *
     * @return object|array
     */
    public function get($moduleName, $pluginName)
    {
        $pluginName = $this->toCamelCase($pluginName);
        $instance   = null;

        if (isset($this->instances[$moduleName][$pluginName])) {
            return $this->instances[$moduleName][$pluginName];
        }

        if (!$instance) {
            if ($this->canCreate($moduleName, $pluginName)) {
                $instance = $this->create($moduleName, $pluginName);
            }
        }

        if ($instance === null) {
            return false;
        }

        return $instance;
    }

    /**
     * Canonicalize name
     *
     * @param string $name Name
     *
     * @return string
     */
    protected function toCamelCase($name)
    {
        if (isset($this->camelCaseNames[$name])) {
            return $this->camelCaseNames[$name];
        }

        $result = str_replace(array('.', '-', '_'), ' ', $name);
        $result = ucwords($result);
        $result = str_replace(' ', '', $result);

        // this is just for performance instead of using str_replace
        return $this->camelCaseNames[$name] = $result;
    }


    /**
     * Determine if we can create an instance.
     *
     * @param string|array $moduleName Module name
     * @param string       $pluginName Plugin name
     *
     * @return bool
     */
    public function canCreate($moduleName, $pluginName = null)
    {
        if (is_array($moduleName)) {
            list($moduleName, $pluginName) = $moduleName;
        } else {
            $pluginName = $this->toCamelCase($pluginName);
        }

        if (isset($this->instances[$moduleName][$pluginName])
        ) {
            return true;
        }

        if (ModuleModel::fromName($moduleName)) {
            $className = 'Modules\\' . $moduleName . '\\Plugin\\' . $pluginName;
            if (class_exists($className)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Create plugin
     *
     * @param string|array $moduleName Module name
     * @param string       $pluginName Plugin name
     *
     * @return bool|\Gc\Module\AbstractPlugin
     */
    public function create($moduleName, $pluginName = null)
    {
        if (is_array($moduleName)) {
            list($moduleName, $pluginName) = $moduleName;
        } else {
            $pluginName = $this->toCamelCase($pluginName);
        }

        if ($this->canCreate($moduleName, $pluginName)) {
            if (!isset($this->instances[$moduleName])) {
                $this->instances[$moduleName] = array();
            }

            $className = 'Modules\\' . $moduleName . '\\Plugin\\' . $pluginName;
            $plugin    = new $className();
            if ($this->validatePlugin($plugin)) {
                $this->instances[$moduleName][$pluginName] = $plugin;
                return $plugin;
            }
        }

        return false;
    }

    /**
     * Check if plugin exists
     *
     * @param string|array $moduleName Module name
     * @param string       $pluginName Plugin name
     *
     * @return bool
     */
    public function has($moduleName, $pluginName = null)
    {
        if (is_array($moduleName)) {
            list($moduleName, $pluginName) = $moduleName;
        } else {
            $pluginName = $this->toCamelCase($pluginName);
        }

        if ($this->canCreate($moduleName, $pluginName)) {
            return true;
        }

        return false;
    }
}
