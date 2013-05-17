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
 * @subpackage JQueryFileUpload
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Datatypes\jQueryFileUpload;

use Gc\Datatype\AbstractDatatype\AbstractPrevalueEditor;
use Zend\Form\Element;
use Zend\Form\Fieldset;

/**
 * Prevalue Editor for Upload datatype
 *
 * @category   Gc_Library
 * @package    Datatypes
 * @subpackage JQueryFileUpload
 */
class PrevalueEditor extends AbstractPrevalueEditor
{
    /**
     * Save upload prevalue editor
     *
     * @return void
     */
    public function save()
    {
        $post                        = $this->getRequest()->getPost();
        $mimeList                    = $post->get('mime_list');
        $optionsPost                 = $post->get('options', array());
        $options                     = array();
        $options['maxNumberOfFiles'] = in_array('maxNumberOfFiles', $optionsPost) ? true : false;

        $this->setConfig(array('mime_list' => $mimeList, 'options' => $options));
    }

    /**
     * Load upload prevalue editor
     *
     * @return mixed
     */
    public function load()
    {
        $parameters = $this->getConfig();
        $elements   = array();

        $optionsValues = !empty($parameters['options']) ? $parameters['options'] : array();
        $fieldset      = new Fieldset('Available options');
        $element       = new Element\MultiCheckbox('options');
        $element->setAttribute('selected', $optionsValues);
        $element->setAttribute('class', 'input-checkbox');

        $element->setValueOptions(
            array(
                array(
                    'value' => 'maxNumberOfFiles',
                    'label' => 'Is multiple',
                    'selected' => empty($optionsValues['maxNumberOfFiles']) ? false : true,
                ),
            )
        );
        $fieldset->add($element);

        $elements[] = $fieldset;

        $element  = new Element\MultiCheckbox('mime_list');
        $mimeList = array(
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
        $options  = array();
        foreach ($mimeList as $mime) {
            $options[] = array(
                'value' => $mime,
                'label' => $mime,
                'selected' =>
                    !in_array(
                        $mime,
                        empty($parameters['mime_list']) ? array() : $parameters['mime_list']
                    ) ? false : true,
            );
        }
        $element->setValueOptions($options);
        $element->setAttribute('class', 'input-checkbox');

        $fieldset = new Fieldset('Mime list');
        $fieldset->add($element);
        $elements[] = $fieldset;

        return $this->addPath(__DIR__)->render('upload-prevalue.phtml', array('elements' => $elements));
    }
}
