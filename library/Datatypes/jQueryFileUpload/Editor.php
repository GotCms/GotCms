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

namespace Datatypes\jQueryFileUpload;

use Gc\Datatype\AbstractDatatype\AbstractEditor,
    Gc\Media\File,
    Gc\Registry,
    Zend\Form\Element;

/**
 * Editor for Upload datatype
 */
class Editor extends AbstractEditor
{
    /**
     * Save upload editor
     * @return void
     */
    public function save()
    {
        $file_class = new File();
        $file_class->init($this->getProperty(), $this->getDatatype()->getDocument());

        $post = $this->getRequest()->getPost();
        $values = $post->get($this->getName(), array());
        $parameters = $this->getConfig();
        $options  = $parameters['options'];
        $array_values = array();
        if(!empty($values) and is_array($values))
        {
            foreach($values as $idx => $value)
            {
                if(empty($value['name']))
                {
                    continue;
                }

                $file = $file_class->getPath() . '/' . $value['name'];
                if(file_exists($file))
                {
                    $const = defined('FILEINFO_MIME_TYPE') ? FILEINFO_MIME_TYPE : FILEINFO_MIME;
                    $finfo = finfo_open($const); // return mimetype extension
                    if(!in_array(finfo_file($finfo, $file), $parameters['mime_list']))
                    {
                        unlink($file);
                    }
                    else
                    {
                        $file_info = @getimagesize($file);
                        $array_values[] = array(
                            'value' => $value['name'],
                            'width' => empty($file_info[0]) ? 0 : $file_info[0],
                            'height' => empty($file_info[1]) ? 0 : $file_info[1],
                            'html' => empty($file_info[2]) ? '' : $file_info[2],
                            'mime' => empty($file_info['mime']) ? '' : $file_info['mime'],
                        );
                    }

                    finfo_close($finfo);
                }
            }

            $return_values = serialize($array_values);
        }

        $this->setValue(empty($return_values) ? NULL : $return_values);
    }

    /**
     * Load upload editor
     * @return mixte
     */
    public function load()
    {
        $parameters = $this->getConfig();
        $options  = $parameters['options'];

        $this->_initScript();
        $file_list = array();
        $files = unserialize($this->getValue());
        if(!empty($files))
        {
            $file_class = new File();
            $file_class->init($this->getProperty(), $this->getDatatype()->getDocument());
            foreach($files as $file_data)
            {
                $file_object = new \StdClass();
                $file_object->name = $file_data['value'];
                $file_object->filename = $file_data['value'];
                $file_object->thumbnail_url = $file_data['value'];

                $router = Registry::get('Application')->getMvcEvent()->getRouter();
                $file_object->delete_url = $router->assemble(array(
                    'document_id' => $this->getDatatype()->getDocument()->getId(),
                    'property_id' => $this->getProperty()->getId(),
                    'file' => base64_encode($file_data['value'])
                ), array('name' => 'mediaRemove'));
                $file_object->delete_type = 'DELETE';
                $file_list[] = $file_object;
            }
        }

        return $this->addPath(__DIR__)->render('upload-editor.phtml', array(
            'property' => $this->getProperty(),
            'uploadUrl' => $this->getUploadUrl(),
            'name' => $this->getName(),
            'files' => json_encode($file_list),
            'options' => $options
        ));
    }

    /**
     * Load resources
     * @return void
     */
    protected function _initScript()
    {
        $headscript = $this->getHelper('HeadScript');
        $headscript
            ->appendFile('/datatypes/jQueryFileUpload/load-image.min.js', 'text/javascript')
            ->appendFile('/datatypes/jQueryFileUpload/canvas-to-blob.min.js', 'text/javascript')
            ->appendFile('/datatypes/jQueryFileUpload/jquery.iframe-transport.js', 'text/javascript')
            ->appendFile('/datatypes/jQueryFileUpload/jquery.fileupload.js', 'text/javascript')
            ->appendFile('/datatypes/jQueryFileUpload/jquery.fileupload-fp.js', 'text/javascript')
            ->appendFile('/datatypes/jQueryFileUpload/jquery.fileupload-ui.js', 'text/javascript')
            ->appendFile('/datatypes/jQueryFileUpload/locale.js', 'text/javascript');

        $headlink = $this->getHelper('HeadLink');
        $headlink->appendStylesheet('/datatypes/jQueryFileUpload/jquery.fileupload-ui.css')
            ->appendStylesheet('/datatypes/jQueryFileUpload/jfileupload-bootstrap.css');
    }
}
