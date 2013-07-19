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
 * @subpackage ActivityLog
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Modules\ActivityLog;

use Gc\Module\AbstractModule;
use Zend\EventManager\Event;

/**
 * Activity log module bootstrap
 *
 * @category   Gc_Library
 * @package    Modules
 * @subpackage ActivityLog
 */
class Bootstrap extends AbstractModule
{
    /**
     * Boostrapazdazd
     *
     * @param Event $e Event
     *
     * @return void
     */
    public function init(Event $e)
    {

    }

    /**
     * Install module
     *
     * @return boolean
     */
    public function install()
    {
        $pdoResource = $this->getAdapter()->getDriver()->getConnection()->getResource();
        $pdoResource->exec(
            file_get_contents(
                __DIR__ . sprintf('/sql/install-%s.sql', str_replace('pdo_', '', $this->getDriverName()))
            )
        );

        return true;
    }

    /**
     * Uninstall module
     *
     * @return boolean
     */
    public function uninstall()
    {
        $pdoResource = $this->getAdapter()->getDriver()->getConnection()->getResource();
        $pdoResource->exec(
            file_get_contents(
                __DIR__ . sprintf('/sql/uninstall-%s.sql', str_replace('pdo_', '', $this->getDriverName()))
            )
        );

        return true;
    }
}
