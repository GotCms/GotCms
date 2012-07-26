<?php
/**
 * This source file is part of Got CMS.
 *
 * Got CMS is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Got CMS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License along
 * with Got CMS. If not, see <http://www.gnu.org/licenses/lgpl-3.0.html>.
 *
 * PHP Version >=5.3
 *
 * @category    Gc
 * @package     Library
 * @subpackage  Media
 * @author      Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license     GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link        http://www.got-cms.com
 */

namespace Gc\Media;

use Gc\Core\Object,
    Gc\Datatype,
    Zend\EventManager\StaticEventManager,
    Zend\File\Transfer\Adapter\Http as FileTransfer;

class File extends Object
{
    const FILE_PERMISSION = 0774;

    /**
     * @param integer $property_id
     * @param $_FILES $file
     * @param string $name
     */
    public function init($property = NULL, $document = NULL)
    {
        if(empty($property) or empty($document))
        {
            return FALSE;
        }

        $this->setProperty($property);
        $this->setDocument($document);
    }

    public function getDirectory()
    {
        return GC_APPLICATION_PATH . '/public/media/files/' . $this->getDocument()->getId() . '/' . $this->getProperty()->getId();
    }
    /**
     * @param unknown_type $file
     * @return boolean
     */
    public function upload()
    {
        $file = new FileTransfer();
        $dir = $this->getDirectory();
        if(!is_dir($dir))
        {
            mkdir($dir, self::FILE_PERMISSION, TRUE);
            $tmp_dir = $dir;
            while($tmp_dir != GC_APPLICATION_PATH . '/media/files')
            {
                chmod($tmp_dir, self::FILE_PERMISSION);
                $tmp_dir = dirname($tmp_dir);
            }
        }

        $file->setDestination($dir);

        $file_name = $file->getFileName(NULL, FALSE);
        $info = pathinfo($file_name);
        $file->addFilter('Rename', array(
            'target' => $file->getDestination() . '/' . uniqid() . '.' . $info['extension'], 'overwrite' => TRUE));

        if($file->receive())
        {
            $data = array();
            $files = $file->getFileInfo();
            foreach($files as $file_data)
            {
                $file_object = new \StdClass();
                $file_object->name = 'New Image Upload Complete:   ' .$file_data['name'];
                $file_object->filename = $file_data['name'];
                $file_object->size = $file_data['size'];
                $file_object->type = $file_data['type'];
                //$fileclass->error = 'null';
                $file_object->thumbnail_url = str_replace(GC_APPLICATION_PATH . '/public', '', $file->getDestination()) . '/' . $file_data['name'];

                $router = \Gc\Registry::get('Application')->getMvcEvent()->getRouter();
                $file_object->delete_url = $router->assemble(array(
                    'document_id' => $this->getDocument()->getId(),
                    'property_id' => $this->getProperty()->getId(),
                    'file' => $file_data['name'])
                , array('name' => 'documentRemoveMedia'));
                $file_object->delete_type = 'DELETE';
                $data[] = $file_object;
            }

            $this->setFiles($data);
            return TRUE;
        }

        return FALSE;
    }

    public function remove($filename)
    {
         $file = $this->getDirectory() . '/' . $filename;
         if(file_exists($file))
         {
             @unlink($file);
             return TRUE;
         }

         return FALSE;
    }

    /**
     *
     * Resizes an image
     * Set parameters to the wanted (or maximum/minimum) width for the processed image, in pixels
     */
    public function resize($file_path, $width = null, $height = null)
    {
        $this->uploader->image_resize = true;

        $this->uploader->image_ratio_x = ( $width == null ) ? true : false;
        $this->uploader->image_ratio_y = ( $height == null ) ? true : false;

        $this->uploader->image_x = $width;
        $this->uploader->image_y = $height;
    }
}
