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
 * Helper for making easy links and getting urls that depend on the routes and router.
 *
 * @category   Gc_Library
 * @package    Library
 * @subpackage View\Helper
 */
class ModuleUrl extends AbstractHelper
{
    /**
     * Generates an url given the name of a route.
     *
     * @param  string  $action_name             Action name
     * @param  array   $controller_name         Controller name
     * @param  array   $query_params                  Parameters for the link
     * @param  array   $options                 Options for the route
     * @param  boolean $reuse_matched_params    Whether to reuse matched parameters
     * @return string  Url                      For the link href attribute
     * @throws Exception\RuntimeException       If no RouteStackInterface was provided
     * @throws Exception\RuntimeException       If no RouteMatch was provided
     * @throws Exception\RuntimeException       If RouteMatch didn't contain a matched route name
     */
    public function __invoke($action_name = NULL, $controller_name = NULL, $query_params = array(), $options = array(), $reuse_matched_params = TRUE)
    {
        $params = array();
        if(!empty($action_name))
        {
            $params['ma'] = $action_name;
        }

        if(!empty($controller_name))
        {
            $params['mc'] = $controller_name;
        }

        $url = $this->getView()->url('moduleEdit', $params, $options, $reuse_matched_params);

        if(!empty($query_params))
        {
            $url_params = array();
            foreach($query_params as $key => $value)
            {
                $url_params[] = $key . '=' . rawurlencode($value);
            }

            $url .= '?' . implode('&', $url_params);
        }

        return $url;
    }
}
