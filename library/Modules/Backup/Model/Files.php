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
 * @package    Modules
 * @subpackage Blog\Model
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Backup\Model;

use Gc\Core\Object;
use ZipArchive;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

/**
 * Blog comment table
 *
 * @category   Gc_Library
 * @package    Modules
 * @subpackage Blog\Model
 */
class Files extends Object
{
    /**
     * Export function
     *
     * @return string
     */
    public function export()
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'Gc');
        $this->zip(
            array(
                GC_PUBLIC_PATH . '/frontend',
                GC_MEDIA_PATH,
                GC_TEMPLATE_PATH . '/layout',
                GC_TEMPLATE_PATH . '/view',
                GC_TEMPLATE_PATH . '/script',
            ),
            $tmpFile
        );

        $zipContent = file_get_contents($tmpFile);
        unlink($tmpFile);

        return $zipContent;
    }

    /**
     * Generate zip archive
     *
     * @param string[] $sources     Array of folders
     * @param string   $destination Zip file name
     *
     * @return boolean
     */
    protected function zip(array $sources, $destination)
    {
        $zip = new ZipArchive();
        if (!$zip->open($destination, ZIPARCHIVE::CREATE)) {
            return false;
        }

        foreach ($sources as $source) {
            $source = str_replace('\\', '/', realpath($source));
            if (is_dir($source) === true) {
                $files = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($source),
                    RecursiveIteratorIterator::SELF_FIRST
                );

                $directoryName = dirname($source);
                foreach ($files as $file) {
                    $file = str_replace('\\', '/', $file);

                    // Ignore "." and ".." folders
                    if (in_array(substr($file, strrpos($file, '/') + 1), array('.', '..'))) {
                        continue;
                    }

                    $file = realpath($file);

                    if (is_dir($file) === true) {
                        $zip->addEmptyDir(str_replace($directoryName . '/', '', $file . '/'));
                    } elseif (is_file($file) === true) {
                        $zip->addFromString(str_replace($directoryName . '/', '', $file), file_get_contents($file));
                    }
                }
            }
        }

        return $zip->close();
    }
}
