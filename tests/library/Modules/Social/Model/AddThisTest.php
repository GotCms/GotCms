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
 * @package  Modules
 * @author   Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license  GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link     http://www.got-cms.com
 */

namespace Social\Model;

use Mockery;

/**
 *
 * @group Modules
 * @category Gc_Tests
 * @package  Modules
 */
class AddThisTest extends \PHPUnit_Framework_TestCase
{
    protected $object;
    protected $configTable;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->configTable = Mockery::mock('Gc\Core\Config');
        $this->configTable->shouldReceive('getValue')->with('module_addthis')->andReturn(
            serialize(array())
        );
        $this->object = new AddThis($this->configTable);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    protected function tearDown()
    {
    }

    /**
     * Test
     *
     * @return void
     */
    public function testConstructwithInvalidParameter()
    {
        $this->setExpectedException('Gc\Exception');
        $result = new AddThis();
        $this->assertFalse($result);
    }

    /**
     * Test
     *
     * @return void
     */
    public function testGetDefaultStyles()
    {
        $result = $this->object->getDefaultStyles();
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('large_toolbox', $result);
        $this->assertArrayHasKey('small_toolbox', $result);
        $this->assertArrayHasKey('fb_tw_p1_sc', $result);
        $this->assertArrayHasKey('button', $result);
        $this->assertArrayHasKey('custom_string', $result);
        foreach ($result as $key => $value) {
            if ($key != 'custom_string') {
                $this->assertArrayHasKey('src', $value);
                $this->assertArrayHasKey('img', $value);
                $this->assertArrayHasKey('name', $value);
            } else {
                $this->assertArrayHasKey('name', $value);
            }
        }
    }

    /**
     * Test
     *
     * @return void
     */
    public function testGetLanguages()
    {
        $result = $this->object->getLanguages();
        $this->assertInternalType('array', $result);
    }

    /**
     * Test
     *
     * @return void
     */
    public function testGetConfig()
    {
        $result = $this->object->getConfig();
        $this->assertInternalType('array', $result);
    }

    /**
     * Test
     *
     * @return void
     */
    public function testAddWidgets()
    {
        $this->configTable->shouldReceive('setValue')->andReturn(true);

        $widgets = array (
            'widget-0' => array (
                'name' => 'blog',
                'identifier' => 'blog',
                'settings' => 'small_toolbox',
                'custom_string' => '',
                'chosen_list' => '',
            ),
              'widget-1' => array (
                'name' => 'home',
                'identifier' => 'home',
                'settings' => 'large_toolbox',
                'custom_string' => '',
                'chosen_list' => '',
            ),
        );
        $result = $this->object->addwidgets($widgets);
        $this->assertTrue($result);
        $result = $this->object->addwidgets($widgets);
        $this->assertTrue($result);
        $result = $this->object->addwidgets($widgets, true);
        $this->assertTrue($result);
    }

    /**
     * Test
     *
     * @return void
     */
    public function testGetWidgets()
    {
        $result = $this->object->getWidgets();
        $this->assertInternalType('array', $result);
    }

    /**
     * Test
     *
     * @return void
     */
    public function testSaveConfig()
    {
        $config = 'a:10:{s:10:"profile_id";s:0:"";s:8:"username";s:0:"";s:8:"password";s:0:"";s:10:"show_stats";b:1;s:8:"language";s:2:"en";s:19:"data_ga_property_id";s:0:"";s:20:"data_track_clickback";s:0:"";s:21:"data_track_addressbar";s:0:"";s:11:"config_json";s:0:"";s:7:"widgets";a:0:{}}';
        $this->configTable->shouldReceive('setValue')->once()->with('module_addthis', $config)->andReturn(true);

        $this->assertTrue($this->object->saveConfig());
    }

    /**
     * Test
     *
     * @return void
     */
    public function testSetConfig()
    {
        $config = 'a:10:{s:10:"profile_id";s:2:"10";s:8:"username";s:0:"";s:8:"password";s:0:"";s:10:"show_stats";b:1;s:8:"language";s:2:"en";s:19:"data_ga_property_id";s:0:"";s:20:"data_track_clickback";s:0:"";s:21:"data_track_addressbar";s:0:"";s:11:"config_json";s:0:"";s:7:"widgets";a:0:{}}';
        $this->configTable->shouldReceive('setValue')->once()->with('module_addthis', $config)->andReturn(true);

        $this->assertTrue($this->object->setConfig(array('config' => array('profile_id' => '10'))));
    }
}
