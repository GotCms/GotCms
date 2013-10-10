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

use Zend\View\Helper\Partial as ZendPartial;
use Gc\View\Model as ViewModel;
use Gc\View\Stream;
use Gc\View\Resolver\TemplatePathStack;

/**
 * Retrieve view from identifier
 *
 * @category   Gc
 * @package    Library
 * @subpackage View\Helper
 * @example In view: $this->partial('identifier');
 */
class Partial extends ZendPartial
{
    /**
     * Check if stream is registered
     *
     * @var TemplatePathStack
     */
    static protected $streamIsRegistered = false;

    /**
     * Returns script from identifier.
     *
     * @param string $name   Name of view script
     * @param array  $values Variables to populate in the view
     *
     * @return mixed
     */
    public function __invoke($name = null, $values = null)
    {
        if (empty($name)) {
            return $this;
        }

        $view = $this->cloneView();

        if (!empty($values)) {
            if (is_array($values)) {
                $view->vars()->assign($values);
            } elseif (is_object($values)) {
                if (null !== ($objectKey = $this->getObjectKey())) {
                    $view->vars()->offsetSet($objectKey, $values);
                } elseif (method_exists($values, 'toArray')) {
                    $view->vars()->assign($values->toArray());
                } else {
                    $view->vars()->assign(get_object_vars($values));
                }
            }
        }

        if (self::$streamIsRegistered === false) {
            Stream::register();
        }

        try {
            $viewModel = ViewModel::fromIdentifier($name);
        } catch (\Exception $e) {
            //don't care
        }

        if (empty($viewModel)) {
            try {
                $return = $view->render($name);
                return $return;
            } catch (\Exception $e) {
                return false;
            }
        }

        $name .= '-view.gc-stream';
        file_put_contents('zend.view://' . $name, $viewModel->getContent());

        return $view->render($name);
    }

    /**
     * Clone the current View
     *
     * @return \Zend\View\Renderer\RendererInterface
     */
    public function cloneView()
    {
        $view = clone $this->getView();
        $view->setVars(array());

        return $view;
    }
}
