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

namespace Gc\Core\Updater;

use Gc\Registry;
use Gc\View\Helper;

/**
 * Retrieve script from identifier
 *
 * @category   Gc
 * @package    Library
 * @subpackage View\Helper
 * @example In view: $this->script('identifier');
 */
class Script extends Helper\Script
{
    /**
     * Script parameter
     *
     * @var array
     */
    protected $__params = array();

    /**
     * Returns script from identifier.
     *
     * @param string $content
     * @param array $params
     * @return mixed
     */
    public function __invoke($content, $params = array())
    {
        $existed = in_array('gc.script', stream_get_wrappers());
        if (!$existed) {
            stream_wrapper_register('gc.script', 'Gc\View\Stream');
        }

        $this->__params = $params;
        $name = mt_rand() . '-script.gc-stream';

        file_put_contents('gc.script://' . $name, $content);

        ob_start();
        include('gc.script://' . $name);

        return ob_get_clean();
    }

    /**
     * Returns param from name.
     *
     * @param string $name
     * @return mixed
     */
    public function getParam($name)
    {
        if (!empty($this->__params[$name])) {
            return $this->__params[$name];
        }

        return null;
    }
}
