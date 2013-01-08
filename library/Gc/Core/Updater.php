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
    Gc\Registry;

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
         $configuration = Registry::get('Configuration');
        $this->setUpdateDirectory(GC_APPLICATION_PATH . '/data/update/' . $configuration['db']['driver']);
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
     * Update database
     *
     * @return boolean
     */
    public function updateDatabase()
    {
        $configuration = Registry::get('Configuration');
        $files = glob(sprintf(GC_APPLICATION_PATH . '/data/update/%s/%s/*.sql', $this->getLatest(), $configuration['db']['driver']));
        if(empty($files))
        {
            return TRUE;
        }

        $sql = '';
        foreach($files as $filename)
        {
            $sql .= file_get_contents($filename).PHP_EOL;
        }

        $resource = Registry::get('Db')->getDriver()->getConnection()->getResource();
        try
        {
            $resource->beginTransaction();
            $resource->exec($sql);
            $resource->commit();
        }
        catch(Exception $e)
        {
            $this->setError($e->getMessage());
            $resource->rollback();

            return FALSE;
        }

        return TRUE;
    }
}
