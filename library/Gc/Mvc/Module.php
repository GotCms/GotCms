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
 * @subpackage Mvc
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Gc\Mvc;

/**
 * Generic Module
 *
 * @category   Gc
 * @package    Library
 * @subpackage Mvc
 */
abstract class Module
{
    /**
     * Module directory path
     *
     * @var string
     */
    protected $directory = null;

    /**
     * Module namespace
     *
     * @var string
     */
    protected $namespace = null;

    /**
     * Module configuration
     *
     * @var array
     */
    protected $config;

    /**
     * Get autoloader config
     *
     * @return array
     */
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                $this->getDir() . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    $this->getNamespace() => $this->getDir() . '/src/' . $this->getNamespace(),
                ),
            ),
        );
    }

    /**
     * Get module configuration
     *
     * @return array
     */
    public function getConfig()
    {
        if (empty($this->config)) {
            $config       = include $this->getDir() . '/config/module.config.php';
            $this->config = $config;
        }

        return $this->config;
    }

    /**
     * Get module dir
     *
     * @return string
     */
    protected function getDir()
    {
        return $this->directory;
    }

    /**
     * get module namespace
     *
     * @return string
     */
    protected function getNamespace()
    {
        return $this->namespace;
    }
}
