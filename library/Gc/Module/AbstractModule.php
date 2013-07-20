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
 * @subpackage Module
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Gc\Module;

use Zend\Db\TableGateway\Feature\GlobalAdapterFeature;
use Zend\EventManager\Event;
use Gc\Registry;

/**
 * Abstract module bootstrap
 *
 * @category   Gc
 * @package    Library
 * @subpackage Module
 */
abstract class AbstractModule
{
    /**
     * Execute on bootstrap
     *
     * @param Event $e Event
     *
     * @return void
     */
    abstract public function init(Event $e);

    /**
     * Install module
     *
     * @return boolean
     */
    abstract public function install();
    /**
     * Uninstall module
     *
     * @return boolean
     */
    abstract public function uninstall();

    /**
     * Return database adapter
     *
     * @return \Zend\Db\Adapter\Adapter
     */
    protected function getAdapter()
    {
        return GlobalAdapterFeature::getStaticAdapter();
    }

    /**
     * Return driver name
     *
     * @return string
     */
    protected function getDriverName()
    {
        $parameters = $this->getAdapter()->getDriver()->getConnection()->getConnectionParameters();
        return $parameters['driver'];
    }
}
