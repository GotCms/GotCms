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
 * @subpackage ImageCropper
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Datatypes\ImageCropper;

use Gc\Datatype\AbstractDatatype\AbstractPrevalueEditor,
    Zend\Form\Element;

/**
 * Prevalue Editor for Image cropper datatype
 *
 * @category   Gc_Library
 * @package    Datatype
 * @subpackage ImageCropper
 */
class PrevalueEditor extends AbstractPrevalueEditor
{
    /**
     * Save Image cropper prevalue editor
     * @return void
     */
    public function save()
    {
        $post = $this->getRequest()->getPost();
        $mime_list = $post->get('mime_list');
        $sizes_data = $post->get('size');
        $resize_option = $post->get('resize_option');
        $background = $post->get('background');
        $sizes = array();
        if(!empty($sizes_data) and is_array($sizes_data))
        {
            foreach($sizes_data as $idx => $size)
            {
                if(empty($size['name']) or empty($size['height']) or empty($size['width']))
                {
                    continue;
                }

                $size['name'] = str_replace(' ', '', $size['name']); // delete useless space
                $sizes[] = $size;
            }
        }

        $this->setConfig(array('background' => $background, 'resize_option' => $resize_option, 'mime_list' => empty($mime_list) ? array() : $mime_list, 'size' => $sizes));
    }

    /**
     * Load Image cropper prevalue editor
     * @return mixed
     */
    public function load()
    {
        $config = $this->getConfig();

        $resize_option = new Element\Select('resize_option');
        $resize_option->setValue(empty($config['resize_option']) ? 'auto' : $config['resize_option']);
        $resize_option->setAttribute('id', 'resize-option');
        $resize_option->setLabel('Resize option');
        $resize_option->setValueOptions(array(
            'auto' => 'auto',
            'crop' => 'crop',
        ));

        $background_option = new Element\Text('background');
        $background_option->setValue(empty($config['background']) ? '' : $config['background']);
        $background_option->setAttribute('id', 'background');
        $background_option->setLabel('Background color');

        $mime_list = new Element\MultiCheckbox('mime_list');
        $array = array(
            'image/gif',
            'image/jpeg',
            'image/png',
        );

        $options = array();
        foreach($array as $mime)
        {
            $options[] = array(
                'value' => $mime,
                'label' => $mime,
                'selected' => !in_array($mime, empty($config['mime_list']) ? array() : $config['mime_list']) ? FALSE : TRUE,
            );
        }

        $mime_list->setValueOptions($options);
        $size_elements = array();
        $idx = 0;
        if(!empty($config['size']))
        {
            foreach($config['size'] as $idx => $size)
            {
                $element_size_name = new Element\Text('size['. $idx . '][name]');
                $element_size_name->setValue($size['name']);
                $element_size_name->setAttribute('id', 'name' . $idx);
                $element_size_name->setLabel('Name');

                $element_width = new Element\Text('size['. $idx . '][width]');
                $element_width->setValue($size['width']);
                $element_width->setAttribute('id', 'width' . $idx);
                $element_width->setLabel('Width');

                $element_height = new Element\Text('size['. $idx . '][height]');
                $element_height->setValue($size['height']);
                $element_height->setAttribute('id', 'height' . $idx);
                $element_height->setLabel('Height');
                $size_elements[] = array($element_size_name, $element_width, $element_height);
            }

            $idx++;
        }

        $element_size_name = new Element\Text('size[#{idx}][name]');
        $element_size_name->setAttribute('id', 'name#{idx}');
        $element_size_name->setLabel('Name');

        $element_width = new Element\Text('size[#{idx}][width]');
        $element_width->setLabel('Width');
        $element_width->setAttribute('id', 'width#{idx}');
        $element_height = new Element\Text('size[#{idx}][height]');
        $element_height->setLabel('Height');
        $element_height->setAttribute('id', 'height#{idx}');
        $template = array($element_size_name, $element_width, $element_height);

        return $this->addPath(__DIR__)->render('upload-prevalue.phtml', array(
            'elements' => array(
                'resize-option' => $resize_option,
                'background' => $background_option,
                'mime' => $mime_list,
                'size' => $size_elements,
                'size-template' => $template
            )
        ));
    }
}
