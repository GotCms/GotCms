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

use Zend\EventManager\Event;
use Gc\Registry;
use Gc\Event\StaticEventManager;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;

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
     * @var \Zend\View\Renderer\PhpRenderer
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
         $configuration = Registry::get('Configuration');
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
     * Render template
     *
     * @param string $name
     * @param array $data
     * @return string
     */
    public function render($name, array $data = array())
    {
        $this->checkRenderer();
        $view_model = new ViewModel();
        $view_model->setTemplate($name);
        $view_model->setVariables($data);

        return $this->renderer->render($view_model);
    }

    /**
     * Add path in Zend\View\Resolver\TemplatePathStack
     *
     * @param string $dir
     * @return \Gc\Module\AbstractObserver
     */
    public function addPath($dir)
    {
        $this->checkRenderer();
        $this->renderer->resolver()->addPath($dir);

        return $this;
    }

    /**
     * Check renderer, create if not exists
     * Copy helper plugin manager from application service manager
     *
     * @return \Gc\Module\AbstractObserver
     */
    protected function checkRenderer()
    {
        if (is_null($this->renderer)) {
            $this->renderer = new PhpRenderer();
            $renderer = Registry::get('Application')->getServiceManager()->get('Zend\View\Renderer\PhpRenderer');
            $this->renderer->setHelperPluginManager(clone $renderer->getHelperPluginManager());
        }

        return $this;
    }
}
