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
 * @package    GcDevelopment
 * @subpackage Filter
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace GcDevelopment\Filter;

use Zend\Db\Adapter\Adapter;
use Zend\InputFilter\InputFilter;

/**
 * Script form
 *
 * @category   Gc_Application
 * @package    GcDevelopment
 * @subpackage Filter
 */
class AbstractFilterContent extends InputFilter
{
    /**
     * Identifier pattern constante
     *
     * @const IDENTIFIER_PATTERN
     */
    const IDENTIFIER_PATTERN = '~^[a-zA-Z0-9._-]+$~';

    /**
     * Table name
     *
     * @return void
     */
    protected $tableName;

    /**
     * Initialize form
     *
     * @param Adapter $dbAdapter Database Adapter
     *
     * @return void
     */
    public function __construct(Adapter $dbAdapter)
    {
        $this->add(
            array(
                'name' => 'name',
                'required' => true,
                'validators' => array(
                    array('name' => 'not_empty'),
                    array(
                        'name' => 'db\\no_record_exists',
                        'options' => array(
                            'table' => $this->tableName,
                            'field' => 'name',
                            'adapter' => $dbAdapter,
                        ),
                    ),
                )
            )
        );

        $this->add(
            array(
                'name' => 'identifier',
                'required' => true,
                'validators' => array(
                    array('name' => 'not_empty'),
                    array('name' => 'regex', 'options' => array('pattern' => self::IDENTIFIER_PATTERN)),
                    array(
                        'name' => 'db\\no_record_exists',
                        'options' => array(
                            'table' => $this->tableName,
                            'field' => 'identifier',
                            'adapter' => $dbAdapter,
                        ),
                    ),
                ),
            )
        );

        $this->add(
            array(
                'name' => 'content',
                'required' => false
            )
        );

        $this->add(
            array(
                'name' => 'description',
                'required' => false
            )
        );
    }
}
