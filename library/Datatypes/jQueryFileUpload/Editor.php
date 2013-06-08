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

use Gc\Datatype\AbstractDatatype\AbstractEditor;
use Gc\Media\File;
use Gc\Registry;
use Zend\Form\Element;
use StdClass;

/**
 * Editor for Upload datatype
 *
 * @category   Gc_Library
 * @package    Datatypes
 * @subpackage JQueryFileUpload
 */
class Editor extends AbstractEditor
{
    /**
     * Save upload editor
     *
     * @return void
     */
    public function save()
    {
        $fileClass = new File();
        $fileClass->load($this->getProperty(), $this->getDatatype()->getDocument());

        $post        = $this->getRequest()->getPost();
        $values      = $post->get($this->getName(), array());
        $parameters  = $this->getConfig();
        $arrayValues = array();
        if (!empty($values) and is_array($values)) {
            foreach ($values as $idx => $value) {
                if (empty($value['name'])) {
                    continue;
                }

                $file = $fileClass->getPath() . '/' . $value['name'];
                if (file_exists($file)) {
                    $const = defined('FILEINFO_MIME_TYPE') ? FILEINFO_MIME_TYPE : FILEINFO_MIME;
                    $finfo = finfo_open($const); // return mimetype extension
                    if (!in_array(finfo_file($finfo, $file), $parameters['mime_list'])) {
                        unlink($file);
                    } else {
                        $fileInfo      = @getimagesize($file);
                        $arrayValues[] = array(
                            'value' => $value['name'],
                            'width' => empty($fileInfo[0]) ? 0 : $fileInfo[0],
                            'height' => empty($fileInfo[1]) ? 0 : $fileInfo[1],
                            'html' => empty($fileInfo[2]) ? '' : $fileInfo[2],
                            'mime' => empty($fileInfo['mime']) ? '' : $fileInfo['mime'],
                        );
                    }

                    finfo_close($finfo);
                }
            }

            $returnValues = serialize($arrayValues);
        }

        $this->setValue(empty($returnValues) ? null : $returnValues);
    }

    /**
     * Load upload editor
     *
     * @return mixed
     */
    public function load()
    {
        $parameters = $this->getConfig();
        $options    = empty($parameters['options']) ? array() : $parameters['options'];

        $this->initScript();
        $fileList = array();
        $files    = unserialize($this->getValue());
        if (!empty($files)) {
            $fileClass = new File();
            $fileClass->load($this->getProperty(), $this->getDatatype()->getDocument());
            foreach ($files as $fileData) {
                $fileObject                = new StdClass();
                $fileObject->name          = $fileData['value'];
                $fileObject->filename      = $fileData['value'];
                $fileObject->thumbnail_url = $fileData['value'];

                $router                  = Registry::get('Application')->getMvcEvent()->getRouter();
                $fileObject->delete_url  = $router->assemble(
                    array(
                        'document_id' => $this->getDatatype()->getDocument()->getId(),
                        'property_id' => $this->getProperty()->getId(),
                        'file' => base64_encode($fileData['value'])
                    ),
                    array('name' => 'content/media/remove')
                );
                $fileObject->delete_type = 'DELETE';
                $fileList[]              = $fileObject;
            }
        }

        return $this->addPath(__DIR__)->render(
            'upload-editor.phtml',
            array(
                'property' => $this->getProperty(),
                'uploadUrl' => $this->getUploadUrl(),
                'name' => $this->getName(),
                'files' => json_encode($fileList),
                'options' => $options
            )
        );
    }

    /**
     * Load resources
     *
     * @return void
     */
    protected function initScript()
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
