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
use Gc\Registry;

/**
 * Retrieve script from identifier
 *
 * @category   Gc
 * @package    Library
 * @subpackage View\Helper
 * @example In view: $this->script('identifier');
 */
class ModulePlugin extends AbstractHelper
{
    /**
     * Lookup for canonicalized names.
     *
     * @var array
     */
    protected $canonicalNames = array();

    /**
     * Registered services and cached values
     *
     * @var array
     */
    protected $instances = array();

    /**
     * @var array map of characters to be replaced through strtr
     */
    protected $canonicalNamesReplacements = array('-' => '', '_' => '', ' ' => '', '\\' => '', '/' => '');

    /**
     * Script parameter
     *
     * @var array
     */
    protected $__params = array();

    /**
     * Execute plugin module.
     *
     * @param string $module_name Module name
     * @param string $plugin_name Plugin name
     * @param array  $params      Parameters
     *
     * @return mixed
     */
    public function __invoke($module_name, $plugin_name, $params = array())
    {
        if (!$this->has($module_name, $plugin_name)) {
            return false;
        }

        $this->__params = $params;

        $instance = $this->get($module_name, $plugin_name);
        if (is_callable($instance)) {
            return call_user_func_array($instance, $params);
        }

        return $instance;
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
     * @param string $module_name Module name
     * @param string $plugin_name Plugin name
     *
     * @return object|array
     */
    public function get($module_name, $plugin_name)
    {
        $plugin_name = $this->canonicalizeName($plugin_name);
        $instance    = null;

        if (isset($this->instances[$module_name][$plugin_name])) {
            return $this->instances[$module_name][$plugin_name];
        }

        if (!$instance) {
            if ($this->canCreate($module_name, $plugin_name)) {
                $instance = $this->create($module_name, $plugin_name);
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
    protected function canonicalizeName($name)
    {
        if (isset($this->canonicalNames[$name])) {
            return $this->canonicalNames[$name];
        }

        // this is just for performance instead of using str_replace
        return $this->canonicalNames[$name] = ucfirst(strtolower(strtr($name, $this->canonicalNamesReplacements)));
    }


    /**
     * Determine if we can create an instance.
     *
     * @param string|array $module_name Module name
     * @param string       $plugin_name Plugin name
     *
     * @return bool
     */
    public function canCreate($module_name, $plugin_name = null)
    {
        if (is_array($module_name)) {
            list($module_name, $plugin_name) = $module_name;
        } else {
            $plugin_name = $this->canonicalizeName($plugin_name);
        }

        if (isset($this->instances[$module_name][$plugin_name])
        ) {
            return true;
        }

        if (!isset($this->instances[$module_name])) {
            $this->instances[$module_name] = array();
        }

        if (ModuleModel::fromName($module_name)) {
            $class_name = 'Modules\\' . $module_name . '\\Plugin\\' . $plugin_name;
            if (class_exists($class_name)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Create plugin
     *
     * @param string|array $module_name Module name
     * @param string       $plugin_name Plugin name
     *
     * @return bool|\Gc\Module\AbstractPlugin
     */
    public function create($module_name, $plugin_name = null)
    {
        if (is_array($module_name)) {
            list($module_name, $plugin_name) = $module_name;
        } else {
            $plugin_name = $this->canonicalizeName($plugin_name);
        }

        if ($this->canCreate($module_name, $plugin_name)) {
            $class_name = 'Modules\\' . $module_name . '\\Plugin\\' . $plugin_name;
            $plugin     = new $class_name();
            if ($this->validatePlugin($plugin)) {
                $this->instances[$module_name][$plugin_name] = $plugin;
                return $plugin;
            }
        }

        return false;
    }

    /**
     * Check if plugin exists
     *
     * @param string|array $module_name Module name
     * @param string       $plugin_name Plugin name
     *
     * @return bool
     */
    public function has($module_name, $plugin_name = null)
    {
        if (is_array($module_name)) {
            list($module_name, $plugin_name) = $module_name;
        } else {
            $plugin_name = $this->canonicalizeName($plugin_name);
        }

        if ($this->canCreate($module_name, $plugin_name)) {
            return true;
        }

        return false;
    }
}
