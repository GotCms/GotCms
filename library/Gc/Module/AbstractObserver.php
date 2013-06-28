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

use Gc\Registry;
use Gc\View\Renderer;
use Gc\Event\StaticEventManager;
use Zend\EventManager\Event;
use Zend\ServiceManager\ServiceManager;

/**
 * Abstract obverser bootstrap
 *
 * @category   Gc
 * @package    Library
 * @subpackage Module
 */
abstract class AbstractObserver
{
    /**
     * Renderer
     *
     * @var \Gc\View\Renderer
     */
    protected $renderer;

    /**
     * Initialize observer
     *
     * @return void
     */
    abstract public function init();

    /**
     * Return database adapter
     *
     * @return \Zend\Db\Adapter\Adapter
     */
    protected function getAdapter()
    {
        return Registry::get('Db');
    }

    /**
     * Return driver name
     *
     * @return string
     */
    protected function getDriverName()
    {
         $configuration = $this->serviceManager->get('Config');
         return $configuration['db']['driver'];
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
     * Retrieve event manager
     *
     * @return \Gc\Event\StaticEventManager
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
        return $this;
    }

    /**
     * Retrieve event manager
     *
     * @return \Gc\Event\StaticEventManager
     */
    public function getServiceManager()
    {
        return $this->serviceManager;
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
     * @return \Gc\Datatype\AbstractDatatype
     */
    public function addPath($dir)
    {
        if (empty($this->renderer)) {
            $this->renderer = new Renderer();
        }

        $this->renderer->addPath($dir);

        return $this;
    }
}
