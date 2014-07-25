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

namespace GcContent\Controller;

use Gc\Test\PHPUnit\Controller\AbstractRestControllerTestCase;
use Gc\Core\Translator;

/**
 * Test layout rest api
 *
 * @group    ZfModules
 * @category Gc_Tests
 * @package  ZfModules
 */
class TranslationRestControllerTest extends AbstractRestControllerTestCase
{
    public function setUp()
    {
        $this->controller = new TranslationRestController;
        $this->translator = new Translator;
        parent::setUp();
    }

    public function tearDown()
    {
        $this->translator->delete('1=1');
    }

    /**
     * Test get layouts
     *
     * @return void
     */
    public function testGetList()
    {
        $this->translator->setValue(
            'word',
            array(
                array(
                    'locale' => 'fr_FR',
                    'value' => 'mot'
                )
            )
        );
        $this->setUpRoute('admin/content/translation');
        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertEquals('word', $result->translations[0]['source']);
        $this->assertEquals('mot', $result->translations[0]['destination']);
    }
}
