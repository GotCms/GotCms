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
 * @subpackage Test\PHPUnit\Framework
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Gc\Test\PHPUnit\Framework;

use PHPUnit_Framework_TestCase;

/**
 * Override Phpunit framework testcase
 *
 * @category   Gc
 * @package    Library
 * @subpackage Test\PHPUnit\Framework
 */
class TestCase extends PHPUnit_Framework_TestCase
{
    /**
     * Tear down to  clean database
     *
     * @return void
     */
    protected function tearDown()
    {
        $classes = array(
            '\\Gc\\Document\\Collection',
            '\\Gc\\DocumentType\\Collection',
            '\\Gc\\User\\Collection',
            '\\Gc\\Datatype\\Collection',
            '\\Gc\\Module\\Collection',
            '\\Gc\\View\\Collection',
            '\\Gc\\Layout\\Collection',
            '\\Gc\\Tab\\Collection',
            '\\Gc\\Property\\Collection',
            '\\Gc\\Script\Collection'
        );

        foreach ($classes as $class) {
            $this->cleanClass(new $class);
        }
    }

    /**
     * Clean collection
     *
     * @param mixed $class Collection class
     *
     * @return void
     */
    protected function cleanClass($class)
    {
        foreach ($class->getAll(true) as $element) {
            $element->delete();
        }
    }
}
