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

namespace Datatypes\Upload;

use Gc\Datatype\AbstractDatatype\AbstractEditor,
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
        $value = $this->getRequest()->getPost()->get($this->getName());
        if(!empty($_FILES[$this->getName()]['name']))
        {
            $file = new \Gc\Media\File();
            $file->init($this->getDatatype()->getDocument(), $this->getProperty(), $this->getName());
            $file->upload();
            $files = $file->getFiles();
            if(!empty($files))
            {
                $data = array();
                foreach($files as $file)
                {
                    $data[] = $file->filename;
                }
            }
        }

        $this->setValue(empty($data) ? NULL : $data);
    }

    /**
     * Load upload editor
     * @return mixte
     */
    public function load()
    {
        $parameters = $this->getConfig();
        $property = $this->getProperty();
        $upload = new Element\File($this->getName());
        $upload->setAttribute('label', $property->getName());
        $value = $this->getValue();
        var_dump($value);
        if(!empty($value))
        {
            $upload->setValue($value[0]);
        }

        return $upload;
    }
}
