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
 * @subpackage JQueryFileUpload
 * @author     Pierre Rambaud (GoT) http://rambaudpierre.fr
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Datatypes\jQueryFileUpload;

use Gc\Datatype\AbstractDatatype\AbstractPrevalueEditor;
use Zend\Form\Element;

/**
 * Prevalue Editor for Upload datatype
 *
 * @category   Gc_Library
 * @package    Datatype
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
        $mime_list                   = $post->get('mime_list');
        $options_post                = $post->get('options', array());
        $options                     = array();
        $options['maxNumberOfFiles'] = in_array('maxNumberOfFiles', $options_post) ? true : false;

        $this->setConfig(array('mime_list' => $mime_list, 'options' => $options));
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

        $options_values = !empty($parameters['options']) ? $parameters['options'] : array();
        $fieldset       = new \Zend\Form\Fieldset('Available options');
        $element        = new Element\MultiCheckbox('options');
        $element->setAttribute('selected', $options_values);

        $element->setValueOptions(
            array(
                array(
                    'value' => 'maxNumberOfFiles',
                    'label' => 'Is multiple',
                    'selected' => empty($options_values['maxNumberOfFiles']) ? false : true,
                ),
            )
        );
        $fieldset->add($element);

        $elements[] = $fieldset;

        $element   = new Element\MultiCheckbox('mime_list');
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
        $options   = array();
        foreach ($mime_list as $mime) {
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

        $fieldset = new \Zend\Form\Fieldset('Mime list');
        $fieldset->add($element);
        $elements[] = $fieldset;

        return $this->addPath(__DIR__)->render('upload-prevalue.phtml', array('elements' => $elements));
    }
}
