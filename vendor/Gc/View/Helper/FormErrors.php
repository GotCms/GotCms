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
 * @category Gc
 * @package  Library
 * @subpackage View\Helper
 * @author   Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license  GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link     http://www.got-cms.com
 */

namespace Gc\View\Helper;

use Zend\View\Renderer\RendererInterface as Renderer
    ,Zend\View\Helper\AbstractHelper;
/**
 * Display form errors
 */
class FormErrors extends AbstractHelper
{
    /**
     * View object
     *
     * @var Renderer
     */
    protected $view = null;

    /**
     * Element block end tags
     * @var string
     */
    protected $_htmlElementEnd       = '</li></ul>';

    /**
     * Element block start tags
     * @var string
     */
    protected $_htmlElementStart     = '<ul%s><li>';

    /**
     * Element block separator
     * @var string
     */
    protected $_htmlElementSeparator = '</li><li>';

    /**
     * Render form errors
     *
     * @param  string|array $errors Error(s) to render
     * @param  array $options
     * @return string
     */
    public function __invoke($errors, array $options = null)
    {
        $escape = true;
        if (isset($options['escape'])) {
            $escape = (bool) $options['escape'];
            unset($options['escape']);
        }

        if (empty($options['class'])) {
            $options['class'] = 'errors';
        }

        $start = $this->getElementStart();
        if (strstr($start, '%s')) {
            $attribs = $this->_htmlAttribs($options);
            $start   = sprintf($start, $attribs);
        }

        if ($escape) {
            $escaper = $this->view->plugin('escapeHtml');
            foreach ($errors as $key => $error) {
                $errors[$key] = $escaper($error);
            }
        }

        $html  = $start
               . implode($this->getElementSeparator(), (array) $errors)
               . $this->getElementEnd();

        return $html;
    }

    /**
     * Set end string for displaying errors
     *
     * @param  string $string
     * @return \Zend\View\Helper\FormErrors
     */
    public function setElementEnd($string)
    {
        $this->_htmlElementEnd = (string) $string;
        return $this;
    }

    /**
     * Retrieve end string for displaying errors
     *
     * @return string
     */
    public function getElementEnd()
    {
        return $this->_htmlElementEnd;
    }

    /**
     * Set separator string for displaying errors
     *
     * @param  string $string
     * @return \Zend\View\Helper\FormErrors
     */
    public function setElementSeparator($string)
    {
        $this->_htmlElementSeparator = (string) $string;
        return $this;
    }

    /**
     * Retrieve separator string for displaying errors
     *
     * @return string
     */
    public function getElementSeparator()
    {
        return $this->_htmlElementSeparator;
    }

    /**
     * Set start string for displaying errors
     *
     * @param  string $string
     * @return \Zend\View\Helper\FormErrors
     */
    public function setElementStart($string)
    {
        $this->_htmlElementStart = (string) $string;
        return $this;
    }

    /**
     * Retrieve start string for displaying errors
     *
     * @return string
     */
    public function getElementStart()
    {
        return $this->_htmlElementStart;
    }

    /**
     * Converts an associative array to a string of tag attributes.
     *
     * @access public
     *
     * @param array $attribs From this array, each key-value pair is
     * converted to an attribute name and value.
     *
     * @return string The XHTML for the attributes.
     */
    protected function _htmlAttribs($attribs)
    {
        $xhtml   = '';
        $escaper = $this->view->plugin('escapeHtml');
        foreach ((array) $attribs as $key => $val) {
            $key = $escaper($key);

            if (('on' == substr($key, 0, 2)) || ('constraints' == $key)) {
                // Don't escape event attributes; _do_ substitute double quotes with singles
                if (!is_scalar($val)) {
                    // non-scalar data should be cast to JSON first
                    $val = \Zend\Json\Json::encode($val);
                }
                // Escape single quotes inside event attribute values.
                // This will create html, where the attribute value has
                // single quotes around it, and escaped single quotes or
                // non-escaped double quotes inside of it
                $val = str_replace('\'', '&#39;', $val);
            } else {
                if (is_array($val)) {
                    $val = implode(' ', $val);
                }
                $val = $escaper($val);
            }

            if ('id' == $key) {
                $val = $this->_normalizeId($val);
            }

            if (strpos($val, '"') !== false) {
                $xhtml .= " $key='$val'";
            } else {
                $xhtml .= " $key=\"$val\"";
            }

        }
        return $xhtml;
    }

    /**
     * Normalize an ID
     *
     * @param  string $value
     * @return string
     */
    protected function _normalizeId($value)
    {
        if (strstr($value, '[')) {
            if ('[]' == substr($value, -2)) {
                $value = substr($value, 0, strlen($value) - 2);
            }
            $value = trim($value, ']');
            $value = str_replace('][', '-', $value);
            $value = str_replace('[', '-', $value);
        }
        return $value;
    }

    /**
     * Set the View object
     *
     * @param  Renderer $view
     * @return AbstractHelper
     */
    public function setView(Renderer $view)
    {
        $this->view = $view;
        return $this;
    }

    /**
     * Get the view object
     *
     * @return null|Renderer
     */
    public function getView()
    {
        return $this->view;
    }
}
