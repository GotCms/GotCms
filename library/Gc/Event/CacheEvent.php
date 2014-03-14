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
 * @subpackage Event
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Gc\Event;

use Zend\EventManager\Event;

/**
 * Cache Event
 *
 * @category   Gc
 * @package    Library
 * @subpackage Event
 */
class CacheEvent extends Event
{
    const EVENT_SAVE        = 'save';
    const EVENT_LOAD        = 'load';
    const EVENT_SHOULDCACHE = 'shouldCache';

    /**
     * Cache key
     *
     * @var string
     */
    protected $cacheKey;

    /**
     * Allow to abort cache
     *
     * @var bool
     */
    protected $abort = false;

    /**
     * Retrieve cache key
     *
     * @return string
     */
    public function getCacheKey()
    {
        return $this->cacheKey;
    }

    /**
     * Set cache key
     *
     * @param string $cacheKey Cache key
     *
     * @return string
     */
    public function setCacheKey($cacheKey)
    {
        $this->cacheKey = $cacheKey;
    }

    /**
     * Retrieve if abort is set
     *
     * @return boolean
     */
    public function getAbort()
    {
        return $this->abort;
    }

    /**
     * Set Abort status
     *
     * @param boolean $abort Boolean to define if the cache is aborted
     *
     * @return CacheEvent
     */
    public function setAbort($abort)
    {
        $this->abort = (boolean) $abort;
        return $this;
    }
}
