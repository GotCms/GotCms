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
     * Test get translations
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

    /**
     * Test create translation with empty data
     *
     * @return void
     */
    public function testCreateTranslationWithEmptyData()
    {
        $this->setUpRoute('admin/content/translation');
        $this->request->setMethod('POST');
        $post = $this->request->getPost();
        $post->fromArray(
            array(
                'source' => ''
            )
        );
        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertEquals('Invalid data', $result->content);
        $this->assertEquals(
            array(
                'source' => array(
                    'isEmpty' => "Value is required and can't be empty",
                ),
                'destination' =>  array(
                    'isEmpty' => "Value is required and can't be empty",
                ),
                'locale' =>  array(
                    'isEmpty' => "Value is required and can't be empty",
                )
            ),
            $result->errors
        );
    }

    /**
     * Test create translation with valid data
     *
     * @return void
     */
    public function testCreateTranslationValidData()
    {
        $this->setUpRoute('admin/content/translation');
        $this->request->setMethod('POST');
        $post = $this->request->getPost();
        $post->fromArray(
            array(
                'source' => 'word',
                'destination' => array(
                    'mot',
                    'fake',
                    ''
                ),
                'locale' => array(
                    'fr_FR',
                    '',
                    25 => 'fr_FR'

                )
            )
        );
        $result = $this->controller->dispatch($this->request, $this->response);
        $this->assertInternalType('array', $result->word);
        $this->assertEquals('fr_FR', $result->word[0]['locale']);
        $this->assertEquals('mot', $result->word[0]['value']);
        $this->assertArrayNotHasKey('1', $result->word);
    }
}
