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
 * @category Gc_Tests
 * @package  ZfModules
 * @author   Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license  GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link     http://www.got-cms.com
 */

namespace GcStatistics\Controller;

use Gc\Test\PHPUnit\Controller\AbstractRestControllerTestCase;

/**
 * Test layout rest api
 *
 * @group    ZfModules
 * @category Gc_Tests
 * @package  ZfModules
 */
class StatRestControllerTest extends AbstractRestControllerTestCase
{
    public function setUp()
    {
        $this->controller = new StatRestController;
        parent::setUp();
    }

    /**
     * Test get layouts
     *
     * @return void
     */
    public function testGetListWithoutLayouts()
    {
        $this->setUpRoute('admin/development/stat');
        $result = $this->controller->dispatch($this->request, $this->response);
        $vars = $result->getVariables();
        $this->assertArrayHasKey('days', $vars);
        $this->assertArrayHasKey('months', $vars);
        $this->assertArrayHasKey('years', $vars);
        $this->assertArrayHasKey('hours', $vars);
    }
}
