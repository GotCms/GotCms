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
 * @subpackage Upload
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Datatypes\Upload;

use Gc\Datatype\AbstractDatatype\AbstractPrevalueEditor;
use Zend\Form\Element;

/**
 * Prevalue Editor for Upload datatype
 *
 * @category   Gc_Library
 * @package    Datatypes
 * @subpackage Upload
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
        $post       = $this->getRequest()->getPost();
        $mimeList   = $post->get('mime_list');
        $isMultiple = $post->get('is_multiple');

        $this->setConfig(
            array(
                'mime_list'   => empty($mimeList) ? array() : $mimeList,
                'is_multiple' => empty($isMultiple) ? false : true
            )
        );
    }

    /**
     * Load upload prevalue editor
     *
     * @return mixed
     */
    public function load()
    {
        $config = $this->getConfig();

        $isMultiple = new Element\Checkbox('is_multiple');
        $isMultiple->setAttributes(
            array(
                'value' => isset($config['is_multiple']) ? $config['is_multiple'] : '',
                'class' => 'input-checkbox',
                'id' => 'is_multiple',
            )
        );
        $isMultiple->setOptions(
            array(
                'label' => 'Is Multiple',
                'label_attributes' => array(
                    'class' => 'required control-label col-lg-2'
                )
            )
        );

        $mimeList = new Element\MultiCheckbox('mime_list');
        $array    = array(
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

        $options = array();
        foreach ($array as $mime) {
            $options[] = array(
                'value' => $mime,
                'label' => $mime,
                'selected' =>
                    !in_array(
                        $mime,
                        empty($config['mime_list']) ? array() : $config['mime_list']
                    ) ? false : true,
            );
        }

        $mimeList->setAttribute('class', 'input-checkbox');
        $mimeList->setValueOptions($options);

        return array($isMultiple, $mimeList);
    }
}
