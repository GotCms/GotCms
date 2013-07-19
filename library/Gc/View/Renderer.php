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

namespace Gc\View;

use Gc\Registry;
use Gc\Core\Object;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;
use Gc\View\Resolver\TemplatePathStack;

/**
 * Abstract obverser bootstrap
 *
 * @category   Gc
 * @package    Library
 * @subpackage Module
 */
class Renderer extends Object
{
    /**
     * Renderer
     *
     * @var \Zend\View\Renderer\PhpRenderer
     */
    protected $renderer;

    /**
     * Directly initiliaze the renderer
     *
     * @return void
     */
    public function init()
    {
        $this->checkRenderer();
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
        $viewModel = new ViewModel();
        $viewModel->setTemplate($name);
        $viewModel->setVariables($data);

        return $this->renderer->render($viewModel);
    }

    /**
     * Add path in Zend\View\Resolver\TemplatePathStack
     *
     * @param string $dir Directory
     *
     * @return \Gc\View\Renderer
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
     * @return \Gc\View\Renderer
     */
    protected function checkRenderer()
    {
        if (is_null($this->renderer)) {
            $this->renderer = new PhpRenderer();
            $renderer       = Registry::get('Application')->getServiceManager()->get('Zend\View\Renderer\PhpRenderer');
            $this->renderer->setHelperPluginManager(clone $renderer->getHelperPluginManager());
        }

        return $this;
    }

    /**
     * Retrieve php renderer
     *
     * @return PhpRenderer
     */
    public function getRenderer()
    {
        return $this->renderer;
    }

    /**
     * Use view stream
     *
     * @return \Gc\View\Renderer
     */
    public function useStreamWrapper()
    {
        $this->renderer->setResolver(new TemplatePathStack());
        $this->renderer->resolver()->setUseStreamWrapper(true);

        return $this;
    }
}
