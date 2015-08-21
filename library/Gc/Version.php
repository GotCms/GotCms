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
use Zend\Http\Client;

/**
 * Class to store and retrieve version
 *
 * @category Gc
 * @package  Library
 */
final class Version
{
    /**
     * GotCms version identification - see compareVersion()
     */
    const VERSION = '1.6.1';

    /**
     * The latest stable version GotCms available
     *
     * @var string
     */
    protected static $latestVersion;

    /**
     * Compare the specified GotCms version string $version
     * with the current Gc\Version::VERSION of GotCms.
     *
     * @param string $version A version string (e.g. "0.7.1").
     *
     * @return int -1 if the $version is older,
     *             0 if they are the same,
     *             and +1 if $version is newer.
     *
     */
    public static function compareVersion($version)
    {
        return version_compare($version, strtolower(self::VERSION));
    }

    /**
     * Fetches the version of the latest stable release.
     *
     * @return string
     */
    public static function getLatest()
    {
        if (null === self::$latestVersion) {
            self::$latestVersion = 'not available';
            $url                 = 'https://api.github.com/repos/GotCms/GotCms/git/refs/tags';
            try {
                $client = new Client(
                    $url,
                    array(
                        'adatper' => 'Zend\Http\Client\Adapter\Curl',
                        'timeout'      => 2,
                        'ssltransport' => STREAM_CRYPTO_METHOD_TLS_CLIENT,
                        'sslverifypeer' => false
                    )
                );

                $response = $client->send();
                if ($response->isSuccess()) {
                    $content = $response->getBody();
                }
            } catch (\Exception $e) {
                //Don't care
            }

            //Try to retrieve with file_get_contents
            if (empty($content)) {
                $content = @file_get_contents($url);
            }

            if (!empty($content)) {
                $apiResponse = Json::decode($content, Json::TYPE_ARRAY);

                // Simplify the API response into a simple array of version numbers
                $tags = array_map(
                    function ($tag) {
                        return substr($tag['ref'], 10); // Reliable because we're filtering on 'refs/tags/'
                    },
                    $apiResponse
                );

                // Fetch the latest version number from the array
                self::$latestVersion = array_reduce(
                    $tags,
                    function ($a, $b) {
                        return version_compare($a, $b, '>') ? $a : $b;
                    }
                );
            }
        }

        return self::$latestVersion;
    }

    /**
     * Returns true if the running version of GotCms is
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
