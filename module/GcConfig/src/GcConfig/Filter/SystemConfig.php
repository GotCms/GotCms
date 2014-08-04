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
 * @category   Gc_Application
 * @package    GcConfig
 * @subpackage Filter
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace GcConfig\Filter;

/**
 * Config filter
 *
 * @category   Gc_Application
 * @package    GcConfig
 * @subpackage Filter
 */
class SystemConfig extends AbstractConfigFilter
{
    /**
     * Initialize System filter
     *
     * @return SystemConfig
     */
    public function __construct()
    {
        $this->add(
            array(
                'name' => 'cookie_domain',
                'required' => true,
                'validators' => array(
                    array('name' => 'not_empty'),
                ),
            )
        );

        $this->add(
            array(
                'name' => 'cookie_path',
                'required' => true,
                'validators' => array(
                    array('name' => 'not_empty'),
                ),
            )
        );

        $this->add(
            array(
                'name' => 'session_handler',
                'required' => true,
                'validators' => array(
                    array('name' => 'not_empty'),
                ),
            )
        );

        $this->add(
            array(
                'name' => 'session_path',
                'required' => false,
            )
        );

        $this->add(
            array(
                'name' => 'session_lifetime',
                'required' => true,
                'validators' => array(
                    array('name' => 'not_empty'),
                    array('name' => 'digits'),
                ),
            )
        );

        $this->add(
            array(
                'name' => 'debug_is_active',
                'required' => false,
                'validators' => array(
                    array('name' => 'not_empty'),
                ),
            )
        );

        $this->add(
            array(
                'name' => 'cache_is_active',
                'required' => false,
                'validators' => array(
                    array('name' => 'not_empty'),
                ),
            )
        );

        $handlerWhitelist = array('filesystem');
        if (extension_loaded('apc')) {
            $handlerWhitelist[] = 'apc';
        }

        if (extension_loaded('memcached')) {
            $handlerWhitelist[] = 'memcached';
        }

        $this->add(
            array(
                'name' => 'cache_handler',
                'required' => true,
                'validators' => array(
                    array('name' => 'not_empty'),
                    array(
                        'name' => 'in_array',
                        'options' => array(
                            'haystack' => $handlerWhitelist
                        )
                    )
                ),
            )
        );

        $this->add(
            array(
                'name' => 'cache_lifetime',
                'required' => true,
                'validators' => array(
                    array('name' => 'not_empty'),
                    array('name' => 'digits'),
                ),
            )
        );

        $this->add(
            array(
                'name' => 'stream_wrapper_is_active',
                'required' => false,
            )
        );

        return $this;
    }
}
