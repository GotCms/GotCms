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
 * @category    Gc
 * @package     Library
 * @subpackage  Module\AbstractModule
 * @author      Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license     GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link        http://www.got-cms.com
 */

namespace Gc\Module;

use Zend\EventManager\Event;
/**
 * Abstract module
 */
abstract class AbstractModule
{
    /**
     * Execute on bootstrap
     * @param Event $e
     */
    abstract function onBootstrap(Event $e);

    /**
     * Install module
     * @return boolean
     */
    abstract function install();
    /**
     * Uninstall module
     * @return boolean
     */
    abstract function uninstall();

    /**
     * Return database adapter
     * @return Zend\Db\Adapter\Adapter
     */
    protected function getAdapter()
    {
        return \Gc\Registry::get('Db');
    }

    protected function getDriverName()
    {
        return $this->getAdapter()->getDriver()->getConnection()->getDriverName();
    }
}
