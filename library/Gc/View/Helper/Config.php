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
 * @package    Library
 * @subpackage View\Helper
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Gc\View\Helper;

use Gc\Core\Config as CoreConfig;
use Zend\View\Helper\AbstractHelper;

/**
 * Generate url with specific base path for cdn backend stored in database.
 *
 * @category   Gc_Library
 * @package    Library
 * @subpackage View\Helper
 * @example In view: $this->config()->get('key)
 */
class Config extends AbstractHelper
{
    /**
     * @var Gc\Core\Config
     */
    protected $config;

    /**
     * Constructor
     *
     * @param CoreConfig $config Configuration
     */
    public function __construct(CoreConfig $config)
    {
        $this->config = $config;
    }

    /**
     * Invoke method
     *
     * @return Config
     */
    public function __invoke()
    {
        return $this;
    }

    /**
     * Set data
     *
     * @param string $identifier Identifier
     * @param string $value      Value
     *
     * @return boolean|string
     */
    public function set($identifier, $value)
    {
        return $this->config->setValue($identifier, $value);
    }

    /**
     * Get data
     *
     * @param string $identifier Identifier
     *
     * @return boolean|string
     */
    public function get($identifier)
    {
        return $this->config->getValue($identifier);
    }
}
