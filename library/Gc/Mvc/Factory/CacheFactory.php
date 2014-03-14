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

use Zend\Cache\StorageFactory as CacheStorage;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Create cache storage adapter via service
 *
 * @category   Gc
 * @package    Library
 * @subpackage Mvc\Factory
 */
class CacheFactory implements FactoryInterface
{
    /**
     * Create the cache storage apdater from the configuration.
     *
     * @param ServiceLocatorInterface $serviceLocator Service Manager
     *
     * @return CacheStorage
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $coreConfig   = $serviceLocator->get('CoreConfig');
        $cacheTtl     = (int) $coreConfig->getValue('cache_lifetime');
        $cacheHandler = $coreConfig->getValue('cache_handler');

        if (!in_array($cacheHandler, array('apc', 'memcached', 'filesystem'))) {
            $cacheHandler = 'filesystem';
        }

        switch($cacheHandler) {
            case 'memcached':
                $namespace = preg_replace(
                    '/[^a-z0-9_\+\-]+/Di',
                    '_',
                    str_replace('/', '-', strtolower($coreConfig->getValue('site_name')))
                );

                $cacheOptions = array(
                    'ttl'       => $cacheTtl,
                    'namespace' => $namespace,
                    'servers'   => array(array(
                        'localhost', 11211
                    )),
                );
                break;
            case 'apc':
                $cacheOptions = array(
                    'ttl'       => $cacheTtl,
                );
                break;
            default:
                $cacheOptions = array(
                    'ttl'       => $cacheTtl,
                    'cache_dir' => GC_APPLICATION_PATH . '/data/cache',
                );
                break;
        }

        return CacheStorage::factory(
            array(
                'adapter' => array(
                    'name'    => $cacheHandler,
                    'options' => $cacheOptions,
                ),
                'plugins' => array(
                    // Don't throw exceptions on cache errors
                    'exception_handler' => array(
                        'throw_exceptions' => false
                    ),
                    'Serializer'
                ),
            )
        );
    }
}
