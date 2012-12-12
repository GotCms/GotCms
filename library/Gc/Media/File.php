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
 * @category   Gc
 * @package    Library
 * @subpackage Media
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Gc\Media;

use Gc\Core\Object,
    Gc\Datatype,
    Gc\Registry,
    StdClass,
    Zend\EventManager\StaticEventManager,
    Zend\File\Transfer\Adapter\Http as FileTransfer;

/**
 * Manage File, actually only works for Datatypes
 * Need document and property to work
 *
 * @category   Gc
 * @package    Library
 * @subpackage Media
 */
class File extends Object
{
    const FILE_PERMISSION = 0774;

    /**
     * Initiliaze File Object
     *
     * @param object $property
     * @param object $document
     * @param string $filename
     *
     * @return void
     */
    public function init($property = NULL, $document = NULL, $filename = NULL)
    {
        if(empty($property) or empty($document))
        {
            return FALSE;
        }

        $this->setProperty($property);
        $this->setDocument($document);
        $this->setFileName($filename);
    }

    /**
     * Return path
     *
     * @return string
     */
    public function getPath()
    {
        return GC_APPLICATION_PATH . '/public';
    }

    /**
     * Return directory
     *
     * @return string
     */
    public function getDirectory()
    {
        return '/media/files/' . $this->getDocument()->getId() . '/' . $this->getProperty()->getId();
    }

    /**
     * Upload file to the server
     *
     * @return boolean
     */
    public function upload()
    {
        $file_transfer = new FileTransfer();
        $dir = $this->getPath() . $this->getDirectory();
        if(!is_dir($dir))
        {
            mkdir($dir, self::FILE_PERMISSION, TRUE);
            $tmp_dir = $dir;
            while($tmp_dir != GC_APPLICATION_PATH . '/public/media/files')
            {
                chmod($tmp_dir, self::FILE_PERMISSION);
                $tmp_dir = dirname($tmp_dir);
            }
        }

        $file_transfer->setDestination($dir);

        $filename = $this->getFileName();
        $filenames = empty($filename) ? NULL : $filename;
        $filenames = $file_transfer->getFileName($filenames, FALSE);
        if(!is_array($filenames))
        {
            $filenames = array();
            $files = $file_transfer->getFileInfo($filename);
            foreach($files as $key => $file)
            {
                if(!empty($file['name']))
                {
                    $filenames[$key] = $file['name'];
                    break;
                }
            }
        }

        $data = array();
        foreach($filenames as $key => $file_name)
        {
            $info = pathinfo($file_name);
            $file_transfer->addFilter('Rename', array(
                'target' => $file_transfer->getDestination($file_name) . '/' . uniqid() . (empty($info['extension']) ? '' : '.' . $info['extension']), 'overwrite' => TRUE));

            if($file_transfer->receive($file_name))
            {
                $files = $file_transfer->getFileInfo($key);
                foreach($files as $file_data)
                {
                    $file_object = new StdClass();
                    $file_object->name = 'New Image Upload Complete:   ' .$file_data['name'];
                    $file_object->filename = $this->getDirectory() . '/' . $file_data['name'];
                    $file_object->size = $file_data['size'];
                    $file_object->type = $file_data['type'];
                    $file_object->thumbnail_url = $this->getDirectory() . '/' . $file_data['name'];

                    $router = Registry::get('Application')->getMvcEvent()->getRouter();
                    $file_object->delete_url = $router->assemble(array(
                        'document_id' => $this->getDocument()->getId(),
                        'property_id' => $this->getProperty()->getId(),
                        'file' => base64_encode($file_data['name'])
                    ), array('name' => 'mediaRemove'));
                    $file_object->delete_type = 'DELETE';
                    $data[] = $file_object;
                }
            }
        }

        if(!empty($data))
        {
            $this->setFiles($data);
            return TRUE;
        }

        return FALSE;
    }

    /**
     * Remove image
     *
     * @param string $filename
     * @return boolean
     */
    public function remove($filename)
    {
         $file = $this->getPath() . $filename;
         if(file_exists($file))
         {
             @unlink($file);
         }

         return TRUE;
    }
}
