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
 * @category   Gc_Library
 * @package    Library
 * @subpackage View\Helper
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Gc\View\Helper;

use Zend\View\Helper\AbstractHelper;

/**
 * Generate module url.
 *
 * @category   Gc_Library
 * @package    Library
 * @subpackage View\Helper
 * @example In view: $this->moduleUrl($controller, $action, $queryParams, $options);
 */
class ModuleUrl extends AbstractHelper
{
    /**
     * Generates an url given the name of a route.
     *
     * @param string  $actionName         Action name
     * @param array   $controllerName     Controller name
     * @param array   $queryParams        Parameters for the link
     * @param array   $options            Options for the route
     * @param boolean $reuseMatchedParams Whether to reuse matched parameters
     *
     * @return string  Url                For the link href attribute
     * @throws Exception\RuntimeException If no RouteStackInterface was provided
     * @throws Exception\RuntimeException If no RouteMatch was provided
     * @throws Exception\RuntimeException If RouteMatch didn't contain a matched route name
     */
    public function __invoke(
        $actionName = null,
        $controllerName = null,
        $queryParams = array(),
        $options = array(),
        $reuseMatchedParams = true
    ) {
        $params = array();
        if (!empty($actionName)) {
            $params['ma'] = $actionName;
        }

        if (!empty($controllerName)) {
            $params['mc'] = $controllerName;
        }

        $url = $this->getView()->url('moduleEdit', $params, $options, $reuseMatchedParams);

        if (!empty($queryParams)) {
            $urlParams = array();
            foreach ($queryParams as $key => $value) {
                $urlParams[] = $key . '=' . rawurlencode($value);
            }

            $url .= '?' . implode('&', $urlParams);
        }

        return $url;
    }
}
