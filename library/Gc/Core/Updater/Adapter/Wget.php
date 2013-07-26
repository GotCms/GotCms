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
 * @subpackage Core
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Gc\Core\Updater\Adapter;

use ZipArchive;
use Gc\Media\File;

/**
 * Get and set config data
 *
 * @category   Gc
 * @package    Library
 * @subpackage Core
 */
class Wget extends AbstractAdapter
{
    /**
     * Initialize adapter
     *
     * @return void
     */
    public function init()
    {
        $this->setTmpPath(GC_APPLICATION_PATH . '/data/tmp');
    }
    /**
     * Update
     *
     * @return string
     */
    public function update()
    {
        $filename = $this->getTmpPath() . '/v' . $this->getLatestVersion() . '.zip';
        if (file_exists($filename)) {
            unlink($filename);
        }

        exec(
            'wget -P '
            . $this->getTmpPath()
            . ' --no-check-certificate https://api.github.com/repos/GotCms/GotCms/zipball/'
            . $this->getLatestVersion()
            . ' 2>&1',
            $output
        );
        rename($this->getTmpPath() . '/' . $this->getLatestVersion(), $filename);
        $this->addMessage(implode(PHP_EOL, $output));

        $zip = new ZipArchive;
        if ($zip->open($filename)) {
            $directoryName = $zip->getNameIndex(0);
            $zip->extractTo($this->getTmpPath());
            $zip->close();
            rename($this->getTmpPath() . '/' . $directoryName, $this->getTmpPath() . '/v' . $this->getLatestVersion());

            unlink($filename);

            return true;
        }

        return false;
    }

    /**
     * Upgrade
     *
     * @return string
     */
    public function upgrade()
    {
        $backupFilename = $this->getTmpPath() . '/backup.zip';
        //Create backup
        if (file_exists($backupFilename)) {
            unlink($backupFilename);
        }

        if (File::isWritable(
            GC_APPLICATION_PATH,
            array(GC_APPLICATION_PATH . '/data/cache', GC_APPLICATION_PATH . '/.git')
        )
        ) {
            $zip = new ZipArchive();
            if ($zip->open($backupFilename, ZipArchive::CREATE)) {
                $this->addDirectoryToZip(
                    $zip,
                    GC_APPLICATION_PATH,
                    array(
                        GC_APPLICATION_PATH . '/.git',
                        GC_APPLICATION_PATH . '/data/tmp',
                        GC_APPLICATION_PATH . '/data/cache',
                        GC_PUBLIC_PATH . '/frontend',
                        GC_MEDIA_PATH . '/files',
                    )
                );

                $directory = $this->getTmpPath() . '/v' . $this->getLatestVersion();
                exec('cp -v -R ' . $directory . ' ' . GC_APPLICATION_PATH . ' 2>&1', $output);
                $this->addMessage(implode(PHP_EOL, empty($output) ? array() : $output));

                return true;
            }
        }

        $this->addMessage('Some files are not writable!');
        $this->addMessage(sprintf('Please execute: chmod -R ug+rw %s', GC_APPLICATION_PATH));
        return false;
    }

    /**
     * Rollback
     *
     * @param string $version Version
     *
     * @return string
     */
    public function rollback($version)
    {
        $zip = new ZipArchive();
        if ($zip->open($this->getTmpPath() . '/backup.zip')) {
            $zip->extractTo(GC_APPLICATION_PATH);
            $zip->close();
        }

        return true;
    }

    /**
     * Add directory and children to zip
     *
     * @param ZipArchive $zip              Zip
     * @param string     $directory        Directory
     * @param array      $excludeDirectory Exclude directory
     *
     * @return ZipArchive
     */
    protected function addDirectoryToZip(ZipArchive $zip, $directory, $excludeDirectory = array())
    {
        $newFolder = str_replace(GC_APPLICATION_PATH, '', $directory);
        $zip->addEmptyDir($newFolder);
        $files = glob($directory . '/*');
        foreach ($files as $file) {
            if (in_array($file, $excludeDirectory)) {
                continue;
            }

            if (is_dir($file)) {
                $zip = $this->addDirectoryToZip($zip, $file, $excludeDirectory);
            } else {
                $newFile = str_replace(GC_APPLICATION_PATH, '', $file);
                $zip->addFile($file, $newFile);
            }
        }

        return $zip;
    }
}
