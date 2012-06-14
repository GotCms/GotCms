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
 * @category    Gc
 * @package     Library
 * @subpackage  Mvc
 * @author      Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license     GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link        http://www.got-cms.com
 */

namespace Gc\Mvc;

use Zend,
    Zend\Config\Reader\Ini,
    Zend\ModuleManager\ModuleManager,
    Zend\Db\Adapter\Adapter as DbAdapter;

class Module
{
    /**
     * @var array
     */
    static protected $_globalConfig;

    /**
     * @var array
     */
    protected $_config;

    /**
     * get autoloader config
     * @return array
     */
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                $this->_getDir() . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    $this->_getNamespace() => $this->_getDir() . '/src/' . $this->_getNamespace(),
                ),
            ),
        );
    }

    /**
     * Get module configuration
     * @return array
     */
    public function getConfig()
    {
        if(empty($this->_config))
        {
            $config = include $this->_getDir() . '/config/module.config.php';
            $ini = new Ini();
            $routes = $ini->fromFile($this->_getDir() . '/config/routes.ini');
            $routes = $routes['production'];
            if(empty($config['router']['routes']))
            {
                $config['router']['routes'] = array();
            }

            if(!empty($routes['routes']))
            {
                $config['router']['routes'] += $routes['routes'];
            }

            $this->_config = $config;
        }

        return $this->_config;
    }

    /**
     * Get module dir
     * @return string
     */
    protected function _getDir()
    {
        return $this->_directory;
    }

    /**
     * get module namespace
     * @return string
     */
    protected function _getNamespace()
    {
        return $this->_namespace;
    }

    /**
     * initiliaze database connexion for every modules
     * @param ModuleManager $module_manager
     * @return void
     */
    public function init(ModuleManager $module_manager)
    {
        if(self::$_globalConfig === NULL)
        {
            $config_paths = $module_manager->getEvent()->getConfigListener()->getOptions()->getConfigGlobPaths();
            if(!empty($config_paths))
            {
                $config = array();
                foreach($config_paths as $path)
                {
                    foreach(glob(realpath(__DIR__.'/../../../').'/'.$path, GLOB_BRACE) as $filename)
                    {
                        $config = include_once($filename);
                    }
                }

                if(!empty($config))
                {
                    $db_adapter = new DbAdapter($config['db']);
                    self::$_globalConfig = $config;
                    \Zend\Db\TableGateway\Feature\GlobalAdapterFeature::setStaticAdapter($db_adapter);
                }
            }
        }
    }
}
