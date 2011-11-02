<?php
class Datatypes_CheckboxList_PrevalueEditor extends Es_Model_DbTable_Datatype_Abstract_PrevalueEditor  {

    public function save($request = null) {
        //Save prevalue in column datatypes_prevalue_value
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
