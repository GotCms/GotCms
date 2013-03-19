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
 * @subpackage Upload
 * @author     Pierre Rambaud (GoT) http://rambaudpierre.fr
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
 * @package    Datatype
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
            $_OLD_FILES = $_FILES;
            $file       = $_FILES[$this->getName()];
            //Ignore others data
            $_FILES                   = array();
            $_FILES[$this->getName()] = $file;

            $file_class = new File();
            $file_class->load($this->getProperty(), $this->getDatatype()->getDocument(), $this->getName());
            $file_class->upload();
            $files = $file_class->getFiles();

            if (!empty($files)) {
                foreach ($files as $file) {
                    $name = $file->filename;
                    $file = $file_class->getPath() . '/' . $name;
                    if (file_exists($file)) {
                        $const = defined('FILEINFO_MIME_TYPE') ? FILEINFO_MIME_TYPE : FILEINFO_MIME;
                        $finfo = finfo_open($const); // return mimetype extension
                        if (!in_array(finfo_file($finfo, $file), $parameters['mime_list'])) {
                            unlink($file);
                        } else {
                            $file_info = @getimagesize($file);
                            $data[]    = array(
                                'value' => $name,
                                'width' => empty($file_info[0]) ? 0 : $file_info[0],
                                'height' => empty($file_info[1]) ? 0 : $file_info[1],
                                'html' => empty($file_info[2]) ? '' : $file_info[2],
                                'mime' => empty($file_info['mime']) ? '' : $file_info['mime'],
                            );
                        }

                        finfo_close($finfo);
                    }
                }

                $data = serialize($data);
            }

            //Restore file data
            $_FILES = $_OLD_FILES;
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
        $upload->setAttribute('label', $property->getName());
        if (!empty($parameters['is_multiple'])) {
            $upload->setAttribute('multiple', 'multiple');
            $upload->setName($upload->getName() . '[]');
        }

        $hidden_upload = new Element\Hidden($this->getName() . '-hidden');
        $value         = $this->getValue();
        if (!empty($value)) {
            $hidden_upload->setValue($value);
        }

        return array(
            $upload,
            $hidden_upload,
            $this->addPath(__DIR__)->render(
                'upload-editor.phtml',
                array(
                    'files' => $value,
                    'id' => $this->getName()
                )
            )
        );
    }
}
