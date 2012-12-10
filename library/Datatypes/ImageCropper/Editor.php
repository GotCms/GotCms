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

use Gc\Datatype\AbstractDatatype\AbstractEditor,
    Gc\Media\File,
    Gc\Media\Image,
    Zend\Form\Element;

/**
 * Editor for Image cropper datatype
 *
 * @category   Gc_Library
 * @package    Datatype
 * @subpackage ImageCropper
 */
class Editor extends AbstractEditor
{
    /**
     * Save Image cropper editor
     * @return void
     */
    public function save()
    {
        $post = $this->getRequest()->getPost();
        $parameters = $this->getConfig();
        $data = array();
        $image_model = new Image();
        $file_class = new File();
        $file_class->init($this->getDatatype()->getDocument(), $this->getProperty(), $this->getName());
        $background_color = empty($parameters['background']) ? '#000000' : $parameters['background'];

        if(!empty($_FILES[$this->getName()]['name']))
        {
            $_OLD_FILES = $_FILES;
            $file = $_FILES[$this->getName()];
            //Ignore others data
            $_FILES = array();
            $_FILES[$this->getName()] = $file;

            $file_class->upload();
            $files = $file_class->getFiles();

            if(!empty($files) and is_array($files))
            {
                foreach($files as $file)
                {
                    $name = $file->filename;
                    $file = $file_class->getPath() . $name;
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
                            $data['original'] = array(
                                'value' => $name,
                                'width' => empty($file_info[0]) ? 0 : $file_info[0],
                                'height' => empty($file_info[1]) ? 0 : $file_info[1],
                                'html' => empty($file_info[2]) ? '' : $file_info[2],
                                'mime' => empty($file_info['mime']) ? '' : $file_info['mime'],
                            );

                            $image_model->open($file);

                            foreach($parameters['size'] as $size)
                            {
                                $image_model->open($file);
                                $image_model->resize($size['width'], $size['height'], empty($parameters['resize_option']) ? 'auto' : $parameters['resize_option'], $background_color);
                                $size_filename = preg_replace('~\.([a-zA-Z]+)$~', '-' . $size['name'] . '.$1', $name);
                                $image_model->save($file_class->getPath() . $size_filename);

                                $file_info = @getimagesize($file_class->getPath() . $size_filename);
                                $data[$size['name']] = array(
                                    'value' => $size_filename,
                                    'width' => empty($file_info[0]) ? 0 : $file_info[0],
                                    'height' => empty($file_info[1]) ? 0 : $file_info[1],
                                    'html' => empty($file_info[2]) ? '' : $file_info[2],
                                    'mime' => empty($file_info['mime']) ? '' : $file_info['mime'],
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
            $_FILES = $_OLD_FILES;
        }
        else
        {
            $data = $post->get($this->getName() . '-hidden');
            $data = unserialize($data);

            if(!empty($data))
            {
                if(!empty($data['original']['value']))
                {
                    foreach($parameters['size'] as $size)
                    {
                        $x = (int)$post->get($this->getName() . $size['name'] . '-x');
                        $y = (int)$post->get($this->getName() . $size['name'] . '-y');

                        $filename = !empty($data[$size['name']]['value']) ? $data[$size['name']]['value'] :  preg_replace('~\.([a-zA-Z]+)$~', '-' . $size['name'] . '.$1', $data['original']['value']);
                        $image_model->open($file_class->getPath() . $data['original']['value']);
                        $image_model->resize($size['width'], $size['height'], empty($parameters['resize_option']) ? 'auto' : $parameters['resize_option'], $background_color, $x, $y);
                        $image_model->save($file_class->getPath() . $filename);
                        if(!empty($data[$size['name']]['value']))
                        {
                            $data[$size['name']]['x'] = $x;
                            $data[$size['name']]['y'] = $y;
                        }
                        else
                        {
                            $file_info = @getimagesize($file_class->getPath() . $filename);
                            $data[$size['name']] = array(
                                'value' => $filename,
                                'width' => empty($file_info[0]) ? 0 : $file_info[0],
                                'height' => empty($file_info[1]) ? 0 : $file_info[1],
                                'html' => empty($file_info[2]) ? '' : $file_info[2],
                                'mime' => empty($file_info['mime']) ? '' : $file_info['mime'],
                                'x' => 0,
                                'y' => 0,
                            );
                        }
                    }

                    foreach($data as $name => $value)
                    {
                        if($name == 'original')
                        {
                            continue;
                        }

                        $found = FALSE;
                        foreach($parameters['size'] as $size)
                        {
                            if($size['name'] == $name)
                            {
                                $found = TRUE;
                                $file['options'] = $size;

                                break;
                            }
                        }

                        if(empty($found))
                        {
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
     * @return mixed
     */
    public function load()
    {
        $parameters = $this->getConfig();
        $property = $this->getProperty();
        $upload = new Element\File($this->getName());
        $upload->setAttribute('label', $property->getName());

        $hidden_upload = new Element\Hidden($this->getName() . '-hidden');
        $value = $this->getValue();
        if(!empty($value))
        {
            $hidden_upload->setValue($value);
            $value = unserialize($value);
            if(is_array($value))
            {
                foreach($value as $name => $file)
                {
                    if($name == 'original')
                    {
                        continue;
                    }

                    $found = FALSE;
                    foreach($parameters['size'] as $size)
                    {
                        if($size['name'] == $name)
                        {
                            $found = TRUE;
                            $file['options'] = $size;

                            break;
                        }
                    }

                    if(empty($found))
                    {
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
            $hidden_upload,
            $this->addPath(__DIR__)->render('upload-editor.phtml', array(
                'files' => $value,
                'id' => $this->getName(),
                'options' => $parameters,
                'name' => $this->getName(),
            ))
        );
    }
}
