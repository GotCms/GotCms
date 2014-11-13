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

use Gc\Core\Object;
use Gc\Registry;
use Gc\Property\Model as PropertyModel;
use Gc\Document\Model as DocumentModel;
use Zend\File\Transfer\Adapter\Http as FileTransfer;

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
     * @param \Gc\Property\Model $property Property
     * @param \Gc\Document\Model $document Document
     * @param string             $filename Filename
     *
     * @return void
     */
    public function load(PropertyModel $property, DocumentModel $document, $filename = null)
    {
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
        return realpath(GC_MEDIA_PATH . '/..');
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
     * Return File Transfer adapter
     *
     * @return \Zend\File\Transfer\Adapter
     */
    public function getFileTransfer()
    {
        if (empty($this->data['fileTransfer'])) {
            $this->data['fileTransfer'] = new FileTransfer();
        }

        return $this->data['fileTransfer'];
    }

    /**
     * Upload file to the server
     *
     * @return boolean
     */
    public function upload()
    {
        $dir = $this->getPath() . $this->getDirectory();
        if (!is_dir($dir)) {
            mkdir($dir, self::FILE_PERMISSION, true);
            $tmpDir = $dir;
            while ($tmpDir != GC_MEDIA_PATH . '/files') {
                chmod($tmpDir, self::FILE_PERMISSION);
                $tmpDir = realpath(dirname($tmpDir));
            }
        }

        $this->getFileTransfer()->setDestination($dir);

        $filename  = $this->getFileName();
        $filenames = empty($filename) ? null : $filename;
        $filenames = $this->getFileTransfer()->getFileName($filenames, false);
        if (!is_array($filenames)) {
            $filenames = array();
            $files     = $this->getFileTransfer()->getFileInfo($filename);
            foreach ($files as $key => $file) {
                if (!empty($file['name'])) {
                    $filenames[$key] = $file['name'];
                    break;
                }
            }
        }

        $data = array();
        foreach ($filenames as $key => $fileName) {
            $info = pathinfo($fileName);
            $this->getFileTransfer()->addFilter(
                'Rename',
                array(
                    'target' => $this->getFileTransfer()->getDestination($fileName)
                        . '/' . uniqid()
                        . (empty($info['extension']) ? '' : '.' . $info['extension'])
                        , 'overwrite' => true
                )
            );

            if ($this->getFileTransfer()->receive($fileName)) {
                $files = $this->getFileTransfer()->getFileInfo($key);
                foreach ($files as $fileData) {
                    $fileObject                  = array();
                    $fileObject['name']          = 'New Image Upload Complete:   ' . $fileData['name'];
                    $fileObject['filename']      = $this->getDirectory() . '/' . $fileData['name'];
                    $fileObject['size']          = $fileData['size'];
                    $fileObject['type']          = $fileData['type'];
                    $fileObject['thumbnail_url'] = $this->getDirectory() . '/' . $fileData['name'];
                    $router                      = Registry::get('Application')->getMvcEvent()->getRouter();
                    $fileObject['delete_url']    = $router->assemble(
                        array(
                            'document_id' => $this->getDocument()->getId(),
                            'property_id' => $this->getProperty()->getId(),
                            'file' => base64_encode($fileData['name'])
                        ),
                        array('name' => 'content/media/remove')
                    );
                    $fileObject['delete_type']   = 'DELETE';
                    $data[]                      = $fileObject;
                }
            }
        }

        if (!empty($data)) {
            $this->setFiles($data);
            return true;
        }

        return false;
    }

    /**
     * Remove image
     *
     * @param string $filename Filename
     *
     * @return boolean
     */
    public function remove($filename)
    {
        $file = $this->getPath() . $filename;
        if (file_exists($file)) {
            @unlink($file);
        }

        return true;
    }

    /**
     * Copy directory from source to destination
     *
     * @param string $source      Source
     * @param string $destination Destination
     *
     * @return boolean
     */
    public static function copyDirectory($source, $destination)
    {
        if (is_dir($source)) {
            if (!file_exists($destination)) {
                @mkdir($destination, 0777);
            }

            $directory = dir($source);
            while (false !== ($readDirectory = $directory->read())) {
                if ($readDirectory == '.' || $readDirectory == '..') {
                    continue;
                }

                $pathDir = $source . '/' . $readDirectory;
                self::copyDirectory($pathDir, $destination . '/' . $readDirectory);
            }

            $directory->close();
        } else {
            $result = copy($source, $destination);
            @chmod($destination, self::FILE_PERMISSION);

            return $result;
        }

        return true;
    }

    /**
     * Test is_writable recursively
     *
     * @param string   $directory        Directory start
     * @param string[] $excludeDirectory Exclude directory
     *
     * @return boolean
     */
    public static function isWritable($directory, $excludeDirectory = array())
    {
        $folder = opendir($directory);
        if (!is_resource($folder)) {
            return false;
        }

        while (false !== ($file = readdir($folder))) {
            $path = $directory . '/' . $file;
            if (!in_array($file, array('.', '..')) and !in_array($path, $excludeDirectory)) {
                $isWritable = true;
                if (is_dir($path)) {
                    $isWritable = self::isWritable($path, $excludeDirectory);
                }

                if (empty($isWritable) or !is_writable($path)) {
                    closedir($folder);
                    return false;
                }
            }
        }

        closedir($folder);
        return true;
    }

    /**
     * Use rmdir recursively
     *
     * @param string $directory Directory start
     *
     * @return boolean
     */
    public static function removeDirectory($directory)
    {
        $files = array_diff(scandir($directory), array('.','..'));
        foreach ($files as $file) {
            $filename = $directory . '/' . $file;
            is_dir($filename) ? self::removeDirectory($filename) : unlink($filename);
        }

        return rmdir($directory);
    }
}
