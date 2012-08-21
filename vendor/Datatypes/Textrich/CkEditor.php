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
 * @category Gc
 * @package  Datatype
 * @author   Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license  GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link     http://www.got-cms.com
 */

namespace Datatypes\Textrich;

use Gc\Core\Object,
    Zend\Form\Element;

/**
 * Ckeditor class to generate html for Ckeditor plugin
 */
class CkEditor extends Object
{
    /**
     * Set parameters
     * @param array $parameters
     * @return \Datatypes\Textrich\Ckeditor
     */
    public function setParameters(array $parameters = NULL)
    {
        if(!empty($parameters['toolbar-items']))
        {
            $this->setToolbarItems($parameters['toolbar-items']);
        }

        return $this;
    }

    /**
     * Get toolbar as js to initialize ckeditor
     * @return string
     */
    public function getToolbarAsJs()
    {
        $js = '';
        $all_toolbar_items = $this->getAllToolbarItems();
        $toolbar_items = $this->getToolbarItems();
        foreach($all_toolbar_items as $group)
        {
            if(!empty($group['items']) and is_array($group['items']))
            {
                $content = array();
                foreach($group['items'] as $item)
                {
                    if(in_array($item, $toolbar_items))
                    {
                        $content[] = $item;
                    }
                }

                if(!empty($content))
                {
                    $js .= '[\''.implode('\', \'', $content).'\'], ';
                }
            }
            else
            {
                if(strlen($group) > 0)
                {
                    $js .= '[\'/\'], ';
                }
            }
        }

        return '[' . substr($js, 0, -1) . ']';
    }

    /**
     * List all toolbar items
     * @return array
     */
    public function getAllToolbarItems()
    {
        return array(
            array('name' => 'document', 'items' => array('Source', '-', 'Save', 'NewPage', 'DocProps', 'Preview', 'Print', '-', 'Templates')),
            array('name' => 'clipboard', 'items' => array('Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo')),
            array('name' => 'editing', 'items' => array('Find', 'Replace', '-', 'SelectAll', '-', 'SpellChecker', 'Scayt')),
            array('name' => 'forms', 'items' => array('Form', 'Checkbox', 'Radio', 'TextField', 'Textarea', 'Select', 'Button', 'ImageButton', 'HiddenField')),
            '/',
            array('name' => 'basicstyles', 'items' => array('Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat')),
            array('name' => 'paragraph', 'items' => array('NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv',
            '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock', '-', 'BidiLtr', 'BidiRtl')),
            array('name' => 'links', 'items' => array('Link', 'Unlink', 'Anchor')),
            array('name' => 'insert', 'items' => array('Image', 'Flash', 'Table', 'HorizontalRule', 'Smiley', 'SpecialChar', 'PageBreak', 'Iframe')),
            '/',
            array('name' => 'styles', 'items' => array('Styles', 'Format', 'Font', 'FontSize')),
            array('name' => 'colors', 'items' => array('TextColor', 'BGColor')),
            array('name' => 'tools', 'items' => array('Maximize', 'ShowBlocks', '-', 'About')),
        );
    }

    /**
     * Get all items as form
     * @return array
     */
    public function getAllItems()
    {
        $elements = array();
        $items = $this->getAllToolbarItems();
        $toolbar_items =  $this->getToolbarItems();
        foreach($items as $group)
        {
            if(!empty($group['items']) and is_array($group['items']))
            {
                $fieldset = new \Zend\Form\Fieldset($group['name']);
                foreach($group['items'] as $idx_item => $item)
                {
                    if($item == '-')
                    {
                        continue;
                    }

                    $element = new Element\Checkbox('toolbar-items['.$item.']');
                    $element->setAttribute('id', 'i' . $group['name'] . $idx_item)
                        ->setCheckedValue(1)
                        ->setAttribute('label', $item);

                    if(!empty($toolbar_items[$item]))
                    {
                        $element->setValue(1);
                    }

                    $fieldset->add($element);
                }

                $elements[] = $fieldset;
            }
        }

        return $elements;
    }
}
