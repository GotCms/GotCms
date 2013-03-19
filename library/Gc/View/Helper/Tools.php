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
 * @author     Pierre Rambaud (GoT) http://rambaudpierre.fr
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Gc\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Gc\Script\Model as ScriptModel;
use Gc\View\Stream;

/**
 * Tools helper
 *
 * @category   Gc
 * @package    Library
 * @subpackage View\Helper
 */
class Tools extends AbstractHelper
{
    /**
     * Tools helper.
     *
     * @param string $function_name Function name
     * @param mixed  $value         Value
     *
     * @return mixed
     */
    public function __invoke($function_name, $value)
    {
        $data = false;
        switch($function_name) {
            case 'unserialize':
                $data = @unserialize($value);
                break;
            case 'serialize':
                $data = @serialize($value);
                break;
            case 'debug':
                $data = sprintf('<pre>%s</pre>', print_r($value, true));
                break;
            case 'is_serialized':
            case 'isSerialized':
                if (trim($value) != '' and preg_match('/^(i|s|a|o|d)(.*);/si', $value)) {
                    $data = true;
                }
                break;
            case 'camel_case':
            case 'camelCase':
                $data = str_replace(' ', '', ucwords($value));
                break;
        }

        return $data;
    }
}
