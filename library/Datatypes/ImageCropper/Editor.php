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
 * @subpackage ImageCropper
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Datatypes\ImageCropper;

use Gc\Datatype\AbstractDatatype\AbstractEditor;
use Gc\Media\File;
use Gc\Media\Image;
use Zend\Form\Element;

/**
 * Editor for Image cropper datatype
 *
 * @category   Gc_Library
 * @package    Datatypes
 * @subpackage ImageCropper
 */
class Editor extends AbstractEditor
{
    /**
     * Save Image cropper editor
     *
     * @return void
     */
    public function save()
    {
        $post       = $this->getRequest()->getPost();
        $parameters = $this->getConfig();
        $data       = array();
        $imageModel = new Image();
        $fileClass  = new File();
        $fileClass->load($this->getProperty(), $this->getDatatype()->getDocument(), $this->getName());
        $backgroundColor = empty($parameters['background']) ? '#000000' : $parameters['background'];

        if (!empty($_FILES[$this->getName()]['name'])) {
            $oldFiles = $_FILES;
            $file     = $_FILES[$this->getName()];
            //Ignore others data
            $_FILES                   = array();
            $_FILES[$this->getName()] = $file;

            $fileClass->upload();
            $files = $fileClass->getFiles();

            if (!empty($files) and is_array($files)) {
                foreach ($files as $file) {
                    $name = $file['filename'];
                    $file = $fileClass->getPath() . $name;
                    if (file_exists($file)) {
                        $const = defined('FILEINFO_MIME_TYPE') ? FILEINFO_MIME_TYPE : FILEINFO_MIME;
                        $finfo = finfo_open($const); // return mimetype extension
                        if (!in_array(finfo_file($finfo, $file), $parameters['mime_list'])) {
                            unlink($file);
                        } else {
                            $fileInfo         = @getimagesize($file);
                            $data['original'] = array(
                                'value' => $name,
                                'width' => empty($fileInfo[0]) ? 0 : $fileInfo[0],
                                'height' => empty($fileInfo[1]) ? 0 : $fileInfo[1],
                                'html' => empty($fileInfo[2]) ? '' : $fileInfo[2],
                                'mime' => empty($fileInfo['mime']) ? '' : $fileInfo['mime'],
                            );

                            $imageModel->open($file);

                            foreach ($parameters['size'] as $size) {
                                $imageModel->open($file);
                                $imageModel->resize(
                                    $size['width'],
                                    $size['height'],
                                    empty($parameters['resize_option']) ? 'auto' : $parameters['resize_option'],
                                    $backgroundColor
                                );
                                $sizeFilename = preg_replace('~\.([a-zA-Z]+)$~', '-' . $size['name'] . '.$1', $name);
                                $imageModel->save($fileClass->getPath() . $sizeFilename);

                                $fileInfo            = @getimagesize($fileClass->getPath() . $sizeFilename);
                                $data[$size['name']] = array(
                                    'value' => $sizeFilename,
                                    'width' => empty($fileInfo[0]) ? 0 : $fileInfo[0],
                                    'height' => empty($fileInfo[1]) ? 0 : $fileInfo[1],
                                    'html' => empty($fileInfo[2]) ? '' : $fileInfo[2],
                                    'mime' => empty($fileInfo['mime']) ? '' : $fileInfo['mime'],
                                    'x' => 0,
                                    'y' => 0,
                                );
                            }
                        }

                        finfo_close($finfo);
                    }
                }
            }

            //Restore file data
            $_FILES = $oldFiles;
        } else {
            $data = $post->get($this->getName() . '-hidden');
            $data = unserialize($data);

            if (!empty($data)) {
                if (!empty($data['original']['value'])) {
                    foreach ($parameters['size'] as $size) {
                        $x = (int) $post->get($this->getName() . $size['name'] . '-x');
                        $y = (int) $post->get($this->getName() . $size['name'] . '-y');

                        $filename = !empty($data[$size['name']]['value']) ?
                            $data[$size['name']]['value'] :
                            preg_replace('~\.([a-zA-Z]+)$~', '-' . $size['name'] . '.$1', $data['original']['value']);
                        $imageModel->open($fileClass->getPath() . $data['original']['value']);
                        $imageModel->resize(
                            $size['width'],
                            $size['height'],
                            empty($parameters['resize_option']) ? 'auto' : $parameters['resize_option'],
                            $backgroundColor,
                            $x,
                            $y
                        );
                        $imageModel->save($fileClass->getPath() . $filename);
                        if (!empty($data[$size['name']]['value'])) {
                            $data[$size['name']]['x'] = $x;
                            $data[$size['name']]['y'] = $y;
                        } else {
                            $fileInfo            = @getimagesize($fileClass->getPath() . $filename);
                            $data[$size['name']] = array(
                                'value' => $filename,
                                'width' => empty($fileInfo[0]) ? 0 : $fileInfo[0],
                                'height' => empty($fileInfo[1]) ? 0 : $fileInfo[1],
                                'html' => empty($fileInfo[2]) ? '' : $fileInfo[2],
                                'mime' => empty($fileInfo['mime']) ? '' : $fileInfo['mime'],
                                'x' => 0,
                                'y' => 0,
                            );
                        }
                    }

                    foreach ($data as $name => $file) {
                        if ($name == 'original') {
                            continue;
                        }

                        $found = false;
                        foreach ($parameters['size'] as $size) {
                            if ($size['name'] == $name) {
                                $found           = true;
                                $file['options'] = $size;

                                break;
                            }
                        }

                        if (empty($found)) {
                            unset($data[$name]);
                        }
                    }
                }
            }
        }

        $data = serialize($data);

        $this->setValue(empty($data) ? '' : $data);
    }

    /**
     * Load Image cropper editor
     *
     * @return mixed
     */
    public function load()
    {
        $parameters = $this->getConfig();
        $property   = $this->getProperty();
        $upload     = new Element\File($this->getName());
        $upload->setAttribute('class', 'form-control');
        $upload->setAttribute('description', $property->getDescription());
        $upload->setAttribute('required', $property->isRequired());
        $upload->setLabel($property->getName());

        $hiddenUpload = new Element\Hidden($this->getName() . '-hidden');
        $value        = $this->getValue();
        if (!empty($value)) {
            $hiddenUpload->setValue($value);
            $value = unserialize($value);
            if (is_array($value)) {
                foreach ($value as $name => $file) {
                    if ($name == 'original') {
                        continue;
                    }

                    $found = false;
                    foreach ($parameters['size'] as $size) {
                        if ($size['name'] == $name) {
                            $found           = true;
                            $file['options'] = $size;

                            break;
                        }
                    }

                    if (empty($found)) {
                        unset($value[$name]);
                    }
                }
            }
        }

        $this->getHelper('HeadLink')->appendStylesheet('/datatypes/ImageCropper/jquery.jcrop.min.css');
        $this->getHelper('HeadLink')->appendStylesheet('/datatypes/ImageCropper/image-cropper.css');
        $this->getHelper('HeadScript')->appendFile('/datatypes/ImageCropper/jquery.jcrop.min.js');

        return array(
            $upload,
            $hiddenUpload,
            $this->addPath(__DIR__)->render(
                'upload-editor.phtml',
                array(
                    'files' => $value,
                    'id' => $this->getName(),
                    'options' => $parameters,
                    'name' => $this->getName(),
                )
            )
        );
    }
}
