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

namespace GcBackend\Controller;

use Gc\Test\PHPUnit\Controller\AbstractRestControllerTestCase;
use Gc\User\Model as UserModel;

/**
 * Test dashboard rest api
 *
 * @group    ZfModules
 * @category Gc_Tests
 * @package  ZfModules
 */
class DashboardRestControllerTest extends AbstractRestControllerTestCase
{
    public function setUp()
    {
        $this->controller = new DashboardRestController;
        parent::setUp();
    }

    /**
     * Test Login without credentials
     *
     * @return void
     */
    public function testGetList()
    {
        $this->setUpRoute('backend/dashboard');
        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertArrayHasKey('version', $result->getVariables());
        $this->assertArrayHasKey('versionIsLatest', $result->getVariables());
        $this->assertArrayHasKey('versionLatest', $result->getVariables());
        $this->assertArrayHasKey('contentStats', $result->getVariables());
        $this->assertArrayHasKey('userStats', $result->getVariables());
        $this->assertArrayHasKey('dashboardSortable', $result->getVariables());
        $this->assertArrayHasKey('dashboardWelcome', $result->getVariables());
        $this->assertArrayHasKey('customWidgets', $result->getVariables());
    }

    /**
     * Test update dashboard
     *
     * @return void
     */
    public function testCreateToRemoveWelcomeMessage()
    {
        $this->setUpRoute('backend/dashboard');
        $this->request->setMethod('POST');
        $post = $this->request->getPost();
        $post->fromArray(
            array(
                'dashboard' => true,
            )
        );
        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertEquals(true, $result->success);
    }

    /**
     * Test update dashboard
     *
     * @return void
     */
    public function testCreateToUpdateDashboard()
    {
        $this->setUpRoute('backend/dashboard');
        $this->request->setMethod('POST');
        $post = $this->request->getPost();
        $post->fromArray(
            array(
                'sortable' => true,
            )
        );
        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertEquals(true, $result->success);
    }
}
