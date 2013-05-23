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

use Gc\Datatype\AbstractDatatype\AbstractEditor;
use Gc\Media\File;
use Zend\Form\Element;

/**
 * Editor for Upload datatype
 *
 * @category   Gc_Library
 * @package    Datatypes
 * @subpackage Upload
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
        $value      = $this->getRequest()->getPost()->get($this->getName());
        $parameters = $this->getConfig();
        $data       = array();
        if (!empty($_FILES[$this->getName()]['name'])) {
            $oldFiles = $_FILES;
            $file     = $_FILES[$this->getName()];
            //Ignore others data
            $_FILES                   = array();
            $_FILES[$this->getName()] = $file;

            $fileClass = new File();
            $fileClass->load($this->getProperty(), $this->getDatatype()->getDocument(), $this->getName());
            $fileClass->upload();
            $files = $fileClass->getFiles();

            if (!empty($files)) {
                foreach ($files as $file) {
                    $name = $file->filename;
                    $file = $fileClass->getPath() . '/' . $name;
                    if (file_exists($file)) {
                        $const = defined('FILEINFO_MIME_TYPE') ? FILEINFO_MIME_TYPE : FILEINFO_MIME;
                        $finfo = finfo_open($const); // return mimetype extension
                        if (!in_array(finfo_file($finfo, $file), $parameters['mime_list'])) {
                            unlink($file);
                        } else {
                            $fileInfo = @getimagesize($file);
                            $data[]   = array(
                                'value' => $name,
                                'width' => empty($fileInfo[0]) ? 0 : $fileInfo[0],
                                'height' => empty($fileInfo[1]) ? 0 : $fileInfo[1],
                                'html' => empty($fileInfo[2]) ? '' : $fileInfo[2],
                                'mime' => empty($fileInfo['mime']) ? '' : $fileInfo['mime'],
                            );
                        }

                        finfo_close($finfo);
                    }
                }

                if (empty($parameters['is_multiple']) and !empty($data[0])) {
                    $data = $data[0];
                }

                $data = serialize($data);
            }

            //Restore file data
            $_FILES = $oldFiles;
        } else {
            $data = $this->getRequest()->getPost()->get($this->getName() . '-hidden');
        }

        $this->setValue(empty($data) ? '' : $data);
    }

    /**
     * Load upload editor
     *
     * @return mixed
     */
    public function load()
    {
        $parameters = $this->getConfig();
        $property   = $this->getProperty();
        $upload     = new Element\File($this->getName());
        $value      = $this->getValue();
        $upload->setAttribute('label', $property->getName());
        if (!empty($parameters['is_multiple'])) {
            $upload->setAttribute('multiple', 'multiple');
            $upload->setName($upload->getName() . '[]');
        }

        $hiddenUpload = new Element\Hidden($this->getName() . '-hidden');
        if (!empty($value)) {
            $hiddenUpload->setValue($value);
        }

        return array(
            $upload,
            $hiddenUpload,
            $this->addPath(__DIR__)->render(
                'upload-editor.phtml',
                array(
                    'files' => $value,
                    'id' => $this->getName(),
                    'isMultiple' => $parameters['is_multiple']
                )
            )
        );
    }
}
