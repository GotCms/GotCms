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

use Zend\View\Helper\Partial as ZendPartial,
    Gc\View\Model as ViewModel,
    Gc\View\Stream,
    Gc\View\Resolver\TemplatePathStack;

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
     * Script parameter
     *
     * @var array
     */
    protected $_params = array();

    /**
     * Template path stack
     *
     * @var TemplatePathStack
     */
    protected $_resolver;

    /**
     * Check if stream is registered
     *
     * @var TemplatePathStack
     */
    static protected $_streamIsRegistered = FALSE;

    /**
     * Returns script from identifier.
     *
     * @param  string $name Name of view script
     * @param  array $values Variables to populate in the view
     * @return mixed
     */
    public function __invoke($name = null, $values = null)
    {
        if(empty($name))
        {
            return $this;
        }

        $view = $this->cloneView();


        if(!empty($values))
        {
            if(is_array($values))
            {
                $view->vars()->assign($values);
            }
            elseif(is_object($values))
            {
                if(NULL !== ($object_key = $this->getObjectKey()))
                {
                    $view->vars()->offsetSet($object_key, $values);
                }
                elseif(method_exists($values, 'toArray'))
                {
                    $view->vars()->assign($values->toArray());
                }
                else
                {
                    $view->vars()->assign(get_object_vars($values));
                }
            }
        }

        if(strpos($name, '.phtml') !== FALSE)
        {
            return $view->render($name);
        }
        else
        {
            if(empty($this->_resolver))
            {
                $this->_resolver = new TemplatePathStack();
                $this->_resolver->setUseStreamWrapper(TRUE);
            }

            $view->setResolver($this->_resolver);

            if(self::$_streamIsRegistered === FALSE)
            {
                $existed = in_array('zend.view', stream_get_wrappers());
                if($existed)
                {
                    stream_wrapper_unregister('zend.view');
                    stream_wrapper_register('zend.view', 'Gc\View\Stream');
                }
            }

            $view_model =  ViewModel::fromIdentifier($name);
            if(empty($view_model))
            {
                return FALSE;
            }

            $name .= '-view.gc-stream';

            file_put_contents('zend.view://' . $name, $view_model->getContent());

            return $view->render($name);
        }
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
