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
 * @subpackage Test\PHPUnit\Controller\TestAsset
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Gc\Test\PHPUnit\Controller\TestAsset;

use Zend\Http\Request as HttpRequest;

/**
 * Override the method setter
 *
 * @category   Gc
 * @package    Library
 * @subpackage Test\PHPUnit\Controller\TestAsset
 */
class Request extends HttpRequest
{
    /**
     * Override the method setter, to allow arbitrary HTTP methods
     *
     * @param string $method Method to use
     *
     * @return Request
     */
    public function setMethod($method)
    {
        $this->method = strtoupper($method);
        return $this;
    }
}
