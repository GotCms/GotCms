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
 * @package    Datatype
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
 * @package    Datatype
 * @subpackage CheckboxList
 */
class PrevalueEditor extends AbstractPrevalueEditor
{
    /**
     * Save Checkbox List prevalue editor
     * @return void
     */
    public function save()
    {
        //Save prevalue in column Datatypes\prevalue_value
        $array_result = array();
        $request = $this->getRequest()->getPost();
        foreach($request->get('values', array()) as $value)
        {
            if(!empty($value))
            {
                $array_result[] = $value;
            }
        }

        $this->setConfig($array_result);
    }

    /**
     * Load Checkbox List prevalue editor
     * @return void
     */
    public function load()
    {
        $parameters = $this->getConfig();

        $content = '<input type="text" name="addValue" id="addValue" value=""> <button class="button-add">Add Element</button>'.PHP_EOL;

        $content .= '<ul id="checkboxlist-values">';
        $content .= '<li>List of values</li>';
        if(is_array($parameters) AND count($parameters)>0)
        {
            foreach($parameters as $param => $value)
            {
                $content .= '<li><input type="text" name="values[]" value="'.$value.'"> <a class="button-delete">Delete Element</a></li>'.PHP_EOL;
            }
        }

        $content .= '</ul>';
        $content .= '<script type="text/javascript">
                        buttonDelete();
                        $(\'.button-add\').button({
                            icons: {
                                primary: \'ui-icon-circle-plus\'
                            },
                            text: false
                        }).click(function() {
                            if($(\'#addValue\').val() != "") {
                                $(\'#checkboxlist-values\').children(\'li:last\').after(
                                    \'<li><input type="text" name="values[]" value="\'+$("#addValue").val()+\'"> <a class="button-delete">Delete Element</a></li>\'
                                );
                                buttonDelete();
                                $(this).removeClass(\'ui-state-focus\');
                                $(\'#addValue\').val(\'\');
                            }
                            return false;
                        });
                        function buttonDelete() {
                            $(\'.button-delete\').button({
                                icons: {
                                    primary: \'ui-icon-circle-minus\'
                                },
                                text: false
                            }).click(function() {
                                $(this).parent().remove();
                                return false;
                            });
                        }
                    </script>';

        return $content;
    }
}
