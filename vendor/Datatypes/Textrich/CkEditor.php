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

namespace Datatypes\Textrich;

use Gc\Core\Object,
    Zend\Form\Element;

class CkEditor extends Object
{
    public function setParameters($parameters = NULL)
    {
        if(!empty($parameters['toolbar-items']))
        {
            $this->setToolbarItems($parameters['toolbar-items']);
        }
    }

    public function getToolbarAsJs(array $toolbar){
        $js = '';
        foreach($toolbar as $group)
        {
            if(is_array($group))
            {
                if(count($group) > 0)
                {
                    $js .= '[\''.implode('\', \'', $group).'\'],';
                }
            }
            else
            {
                if(strlen($group) > 0)
                {
                    $js .= '[\'/\'],';
                }
            }
        }
        return '[' . substr($js, 0, -1) . ']';
    }

    public function getAllToolbarItems()
    {
        return array(
            array('Source','-','Save','NewPage','Preview','-','Templates'),
            array('Cut','Copy','Paste','PasteText','PasteFromWord','-','Print', 'SpellChecker', 'Scayt'),
            array('Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'),
            array('Form', 'Checkbox', 'Radio', 'TextField', 'Textarea', 'Select', 'Button', 'ImageButton', 'HiddenField'),
            '/',
            array('Bold','Italic','Underline','Strike','-','Subscript','Superscript'),
            array('NumberedList','BulletedList','-','Outdent','Indent','Blockquote','CreateDiv'),
            array('JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'),
            array('BidiLtr', 'BidiRtl'),
            array('Link','Unlink','Anchor'),
            array('Image','Flash','Table','HorizontalRule','Smiley','SpecialChar','PageBreak','Iframe'),
            '/',
            array('Styles','Format','Font','FontSize'),
            array('TextColor','BGColor'),
            array('Maximize', 'ShowBlocks','-','About')
        );
    }

    public function getAllItems()
    {
        $elements = array();
        $items = $this->getAllToolbarItems();
        foreach($items as $idx_group => $group_items)
        {
            if(is_array($group_items))
            {
                foreach($group_items as $idx_item => $item)
                {
                    if($item == '-')
                    {
                        continue;
                    }

                    $element = new Element('toolbar-items['.$item.']');
                    $element->setAttribute('id', 'i' . $idx_group . $idx_item)
                        ->setAttribute('value', $item)
                        ->setAttribute('type', 'checkbox')
                        ->setAttribute('label', $item);

                    if(in_array($item, $this->getToolbarItems()))
                    {
                        $element->setAttribute('checked', 'checked');
                    }

                    $elements[] = $element;
                }
            }
        }

        return $elements;
    }

    public function hasItem($item)
    {
        foreach($this->toolbar as $group)
        {
            if(is_array($group) && in_array($item, $group))
            {
                return TRUE;
            }
        }

        return FALSE;
    }
}
