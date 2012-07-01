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

namespace Datatypes\Upload;

use Gc\Datatype\AbstractDatatype\AbstractPrevalueEditor,
    Zend\Form\Element;

class PrevalueEditor extends AbstractPrevalueEditor
{
    /**
     * Save textstring prevalue editor
     * @return void
     */
    public function save()
    {
        $post = $this->getRequest()->getPost();
        $mime_list = $post->get('mime_list');
        $options_post = $post->get('options', array());
        $options = array();
        $options['maxNumberOfFiles'] = array_key_exists('maxNumberOfFiles', $options_post) ? TRUE : FALSE;
        $options['title'] = array_key_exists('title', $options_post)  ? TRUE : FALSE;
        $options['content'] = array_key_exists('content', $options_post)  ? TRUE : FALSE;

        $this->setConfig(array('mime_list' => $mime_list, 'options' => $options));
    }

    /**
     * Load textstring prevalue editor
     * @return mixte
     */
    public function load()
    {
        $parameters = $this->getConfig();
        $elements = array();

        $options_values = !empty($parameters['options']) ? $parameters['options'] : array();
        $fieldset = new \Zend\Form\Fieldset('Available options');
        foreach(array('maxNumberOfFiles' => 'Max number of files', 'title' => 'has title', 'content' => 'has content text') as $option_value => $option_label)
        {
            $element = new Element('options['.$option_value.']');
            $element->setAttribute('type', 'checkbox')
                ->setAttribute('value', 1)
                ->setAttribute('label', $option_label)
                ->setAttribute('id', 'upload-options-' . $option_value);

            if(!empty($options_values[$option_value]))
            {
                $element->setAttribute('checkedValue', TRUE);
            }

            $fieldset->add($element);
        }

        $elements[] = $fieldset;
        $mime_list = array(
            'image/gif',
            'image/jpeg',
            'image/png',
            'image/tiff',
            'image/svg+xml',
            'text/css',
            'text/csv',
            'text/html',
            'text/javascript',
            'text/plain',
            'text/xml',
            'video/mpeg',
            'video/mp4',
            'video/quicktime',
            'video/x-ms-wmv',
            'video/x-msvideo',
            'video/x-flv',
            'audio/mpeg',
            'audio/x-ms-wma',
            'audio/vnd.rn-realaudio',
            'audio/x-wav'
        );

        $mime_list_values = !empty($parameters['mime_list']) ? $parameters['mime_list'] : array();

        $fieldset = new \Zend\Form\Fieldset('Mime list');
        foreach($mime_list as $mime_idx => $mime_value)
        {
            $element = new Element('mime_list['.$mime_value.']');
            $element->setAttribute('type', 'checkbox')
                ->setAttribute('value', 1)
                ->setAttribute('id', 'upload' . $mime_idx)
                ->setAttribute('label', $mime_value);

            if(!empty($mime_list_values[$mime_value]))
            {
                $element->setAttribute('checkedValue', TRUE);
            }

            $fieldset->add($element);
        }

        $elements[] = $fieldset;

        return $this->addPath(__DIR__)->render('upload-prevalue.phtml', array('elements' => $elements));
    }
}
