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
 * @category Gc
 * @package  Datatype
 * @author   Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license  GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link     http://www.got-cms.com
 */

namespace Datatypes\CheckboxList;

use Gc\Datatype\AbstractDatatype\AbstractPrevalueEditor;

class PrevalueEditor extends AbstractPrevalueEditor
{

    public function save($request = null) {
        //Save prevalue in column Datatypes\prevalue_value
        $arrayResult = array();
        foreach($request->getParam('values', array()) as $value)
        {
            if($value != '')
            {
                $arrayResult[] = $value;
            }
        }

        $this->setParameters($arrayResult);

        return $this->getParameters();
    }

    public function load()
    {
        $parameters = $this->getParameters();

        $content = '<input type="text" name="addValue" id="addValue" value="" /> <button class="button-add">Add Element</button>'.PHP_EOL;

        $content .= '<ul id="checkboxlist-values">';
        $content .= '<li>List of values</li>';
        if(is_array($parameters) AND count($parameters)>0)
        {
            foreach($parameters as $param => $value)
            {
                $content .= '<li><input type="text" name="values[]" value="'.$value.'" /> <a class="button-delete">Delete Element</a></li>'.PHP_EOL;
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
                                    \'<li><input type="text" name="values[]" value="\'+$("#addValue").val()+\'" /> <a class="button-delete">Delete Element</a></li>\'
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
