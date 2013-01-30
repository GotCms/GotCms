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

use Gc\Core\Updater\Adapter,
    Gc\Registry,
    Gc\Version;

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
    protected $_adapter;

    /**
     * Initialize update directory
     */
    public function init()
    {
        $this->setUpdateDirectory(GC_APPLICATION_PATH . '/data/update/' . Version::getLatest());
    }

    /**
     * Load adapter
     *
     * @param strin $type Adapter type
     * @return boolean
     */
    public function load($type)
    {
        switch($type)
        {
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

        if(empty($adapter))
        {
            return false;
        }

        $this->_adapter = $adapter;
        return TRUE;
    }

    /**
     * Update
     *
     * @return void
     */
    public function update()
    {
        if(empty($this->_adapter))
        {
            return FALSE;
        }

        return $this->_adapter->update();
    }

    /**
     * Upgrade
     *
     * @return void
     */
    public function upgrade()
    {
        if(empty($this->_adapter))
        {
            return FALSE;
        }

        return $this->_adapter->upgrade();
    }

    /**
     * Rollback if problem with database
     *
     * @param string $current_version
     * @return void
     */
    public function rollback($current_version)
    {
        if(empty($this->_adapter))
        {
            return FALSE;
        }

        return $this->_adapter->rollback($current_version);
    }

    /**
     * Update database
     *
     * @return boolean
     */
    public function updateDatabase()
    {
        if(empty($this->_adapter))
        {
            return FALSE;
        }

        $configuration = Registry::get('Configuration');
        $files = array();
        $update_path = GC_APPLICATION_PATH . '/data/update';
        $path = glob($update_path . '/*');
        foreach($path as $file)
        {
            $version = str_replace($update_path . '/v', '', $file);
            if(version_compare($version, Version::VERSION, '>'))
            {
                $file_list = glob(sprintf($file . '/%s/*.sql', $configuration['db']['driver']));
                if(!empty($file_list))
                {
                    $files[] = $file_list;
                }
            }
        }

        if(empty($files))
        {
            return TRUE;
        }

        $sql = '';
        foreach($files as $file_list)
        {
            foreach($file_list as $filename)
            {
                $sql .= file_get_contents($filename) . PHP_EOL;
            }
        }

        $resource = Registry::get('Db')->getDriver()->getConnection()->getResource();
        try
        {
            $resource->beginTransaction();
            $resource->exec($sql);
            $resource->commit();
        }
        catch(\Exception $e)
        {
            $resource->rollback();
            $this->setError($e->getMessage());

            return FALSE;
        }

        return TRUE;
    }

    /**
     * Retrieve messages from adapter
     *
     * @return array
     */
    public function getMessages()
    {
        return $this->_adapter->getMessages();
    }
}
