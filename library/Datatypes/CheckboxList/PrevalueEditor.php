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
 * @package    Datatypes
 * @subpackage CheckboxList
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Datatypes\CheckboxList;

use Gc\Datatype\AbstractDatatype\AbstractPrevalueEditor;

/**
 * Prevalue Editor for Checkbox List datatype
 *
 * @category   Gc_Library
 * @package    Datatypes
 * @subpackage CheckboxList
 */
class PrevalueEditor extends AbstractPrevalueEditor
{
    /**
     * Save Checkbox List prevalue editor
     *
     * @return void
     */
    public function save()
    {
        //Save prevalue in column Datatypes\prevalue_value
        $arrayResult = array();
        $request     = $this->getRequest()->getPost();
        foreach ($request->get('list', array()) as $idx => $data) {
            $key               = empty($data['key']) ? $idx : $data['key'];
            $arrayResult[$key] = $data['value'];
        }

        $this->setConfig($arrayResult);
    }

    /**
     * Load Checkbox List prevalue editor
     *
     * @return string
     */
    public function load()
    {
        return $this->addPath(__DIR__)->render(
            'checkboxlist-prevalue.phtml',
            array(
                'parameters' => $this->getConfig()
            )
        );
    }
}
