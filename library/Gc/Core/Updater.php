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

namespace Gc\Core;

use Gc\Core\Updater\Adapter;
use Gc\Core\Updater\Script;
use Gc\Version;
use Zend\Db\Adapter\Adapter as DbAdapter;

/**
 * Update cms
 *
 * @category   Gc
 * @package    Library
 * @subpackage Core
 */
class Updater extends Object
{
    /**
     * Adapter
     *
     * @var AbstractAdapter
     */
    protected $adapter;

    /**
     * Initialize update directory
     *
     * @return void
     */
    public function init()
    {
        $this->setUpdateDirectory(GC_APPLICATION_PATH . '/data/update/' . Version::getLatest());
    }

    /**
     * Load adapter
     *
     * @param string $type Adapter type
     *
     * @return boolean
     */
    public function load($type)
    {
        switch($type) {
            case 'git':
                $adapter = new Adapter\Git();
                break;
            /**
             * @TODO Wget
             *
            case 'wget':
                $adapter = new Adapter\Wget();
                break;
             */
            /**
             * @TODO ftp
             *
            case 'ftp':
                $adapter = new Adapter\Ftp();
                break;
             */
        }

        if (empty($adapter)) {
            return false;
        }

        $this->adapter = $adapter;
        return true;
    }

    /**
     * Update
     *
     * @return void
     */
    public function update()
    {
        if (empty($this->adapter)) {
            return false;
        }

        return $this->adapter->update();
    }

    /**
     * Upgrade
     *
     * @return void
     */
    public function upgrade()
    {
        if (empty($this->adapter)) {
            return false;
        }

        return $this->adapter->upgrade();
    }

    /**
     * Rollback if problem with database
     *
     * @param string $currentVersion Current version
     *
     * @return void
     */
    public function rollback($currentVersion)
    {
        if (empty($this->adapter)) {
            return false;
        }

        return $this->adapter->rollback($currentVersion);
    }

    /**
     * Update database
     *
     * @param array     $configuration Configuration
     * @param DbAdapter $dbAdapter     Database adapter
     *
     * @return boolean
     */
    public function updateDatabase($configuration, $dbAdapter)
    {
        if (empty($this->adapter)) {
            return false;
        }

        $files      = array();
        $updatePath = GC_APPLICATION_PATH . '/data/update';
        $path       = glob($updatePath . '/*');
        foreach ($path as $file) {
            $version = str_replace($updatePath . '/', '', $file);
            if (version_compare($version, Version::VERSION, '>')) {
                $fileList = glob(sprintf($file . '/%s/*.sql', $configuration['db']['driver']));
                if (!empty($fileList)) {
                    $files[] = $fileList;
                }
            }
        }

        if (empty($files)) {
            return true;
        }

        $sql = '';
        foreach ($files as $fileList) {
            foreach ($fileList as $filename) {
                $sql .= file_get_contents($filename) . PHP_EOL;
            }
        }

        $resource = $dbAdapter->getDriver()->getConnection()->getResource();
        try {
            $resource->beginTransaction();
            $stmt = $resource->exec($sql);
            $resource->commit();
        } catch (\Exception $e) {
            $resource->rollBack();
            $this->setError($e->getMessage());

            return false;
        }

        return true;
    }

    /**
     * Update database
     *
     * @return boolean
     */
    public function executeScripts()
    {
        if (empty($this->adapter)) {
            return false;
        }

        $files      = array();
        $updatePath = GC_APPLICATION_PATH . '/data/update';
        $path       = glob($updatePath . '/*');
        foreach ($path as $file) {
            $version = str_replace($updatePath . '/', '', $file);
            if (version_compare($version, Version::VERSION, '>')) {
                $fileList = glob($file . '/*.php');
                if (!empty($fileList)) {
                    $files[] = $fileList;
                }
            }
        }

        if (empty($files)) {
            return true;
        }

        $script = new Script();
        foreach ($files as $fileList) {
            foreach ($fileList as $filename) {
                try {
                    $this->adapter->addMessage($script->execute($filename));
                } catch (\Exception $e) {
                    ob_get_clean();
                    $this->setError($e->getMessage());
                }
            }
        }

        return true;
    }

    /**
     * Retrieve messages from adapter
     *
     * @return array
     */
    public function getMessages()
    {
        return $this->adapter->getMessages();
    }
}
