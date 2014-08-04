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

use Gc\Document;
use Gc\Layout;

/**
 * Config filter
 *
 * @category   Gc_Application
 * @package    GcConfig
 * @subpackage Filter
 */
class GeneralConfig extends AbstractConfigFilter
{
    /**
     * Initialize General filter
     *
     * @return GeneralConfig
     */
    public function __construct()
    {
        $this->add(
            array(
                'name' => 'site_name',
                'required' => true,
                'validators' => array(
                    array('name' => 'not_empty'),
                ),
            )
        );

        $this->add(
            array(
                'name' => 'site_is_offline',
                'required' => false,
            )
        );

        $documentCollection = new Document\Collection();
        $documentCollection->load(0);
        $this->add(
            array(
                'name' => 'site_offline_document',
                'required' => true,
                'validators' => array(
                    array(
                        'name' => 'in_array',
                        'haystack' => array_keys($documentCollection->getSelect())
                    )
                )
            )
        );

        $layoutCollection = new Layout\Collection();
        $this->add(
            array(
                'name' => 'site_404_layout',
                'required' => true,
                'validators' => array(
                    array(
                        'name' => 'in_array',
                        'haystack' => array_keys($layoutCollection->getSelect())
                    )
                )
            )
        );

        $this->add(
            array(
                'name' => 'site_exception_layout',
                'required' => true,
                'validators' => array(
                    array(
                        'name' => 'in_array',
                        'haystack' => array_keys($layoutCollection->getSelect())
                    )
                )
            )
        );

        return $this;
    }
}
