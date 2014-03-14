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
 * @subpackage Mvc\Factory
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Gc\Mvc\Factory;

use Gc\Mvc\Service\CacheService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Create cache service via service
 *
 * @category   Gc
 * @package    Library
 * @subpackage Mvc\Factory
 */
class CacheServiceFactory implements FactoryInterface
{
    /**
     * Create the cache service from the configuration
     *
     * @param ServiceLocatorInterface $serviceLocator Service Manager
     *
     * @return CacheService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $cacheStorage = $serviceLocator->get('Cache');
        return new CacheService($cacheStorage);
    }
}
