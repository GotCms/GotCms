<?php
/**
 * This source file is part of Got CMS.
 *
 * Got CMS is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Got CMS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License along
 * with Got CMS. If not, see <http://www.gnu.org/licenses/lgpl-3.0.html>.
 *
 * PHP Version >=5.3
 *
 * @category    Gc
 * @package     Library
 * @subpackage  View\Helper
 * @author      Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license     GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link        http://www.got-cms.com
 */

namespace Gc\View\Helper;

use Zend\View\Helper\AbstractHelper,
    Gc\Script\Model as ScriptModel,
    Gc\View\Stream;

class Script extends AbstractHelper
{
    /**
     * Returns script from identifier.
     *
     * @param string $identifier
     * @return mixte
     */
    public function __invoke($identifier)
    {
        $existed = in_array('gc.script', stream_get_wrappers());
        if(!$existed)
        {
            stream_wrapper_register('gc.script', 'Gc\View\Stream');
        }

        $script =  ScriptModel::fromIdentifier($identifier);
        file_put_contents('gc.script://' . $identifier, $script->getContent());

        return include('gc.script://' . $identifier);

    }
}
