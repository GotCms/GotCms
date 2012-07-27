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
 * @category Gc
 * @package  Library
 * @author   Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license  GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link     http://www.got-cms.com
 */

namespace Gc;

use Zend\Json\Json;

final class Version
{
    /**
     * Got Cms version identification - see compareVersion()
     */
    const VERSION = '0.1a';

    /**
     * The latest stable version Got Cms available
     *
     * @var string
     */
    protected static $latestVersion;

    /**
     * Compare the specified Got Cms version string $version
     * with the current Gc\Version::VERSION of Got Cms.
     *
     * @param  string  $version  A version string (e.g. "0.7.1").
     * @return int           -1 if the $version is older,
     *                           0 if they are the same,
     *                           and +1 if $version is newer.
     *
     */
    public static function compareVersion($version)
    {
        $version = strtolower($version);
        $version = preg_replace('/(\d)pr(\d?)/', '$1a$2', $version);
        return version_compare($version, strtolower(self::VERSION));
    }

    /**
     * Fetches the version of the latest stable release.
     * @return string
     */
    public static function getLatest()
    {
        if (null === self::$latestVersion) {
            //@TODO
            self::$latestVersion = self::VERSION;
        }

        return self::$latestVersion;
    }

    /**
     * Returns true if the running version of Got Cms is
     * the latest (or newer??) than the latest tag on GitHub,
     * which is returned by static::getLatest().
     *
     * @return boolean
     */
    public static function isLatest()
    {
        return static::compareVersion(static::getLatest()) < 1;
    }
}
