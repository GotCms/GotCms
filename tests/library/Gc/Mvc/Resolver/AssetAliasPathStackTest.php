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
 * @package  Library
 * @author   Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license  GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link     http://www.got-cms.com
 */

namespace Gc\Mvc\Resolver;

use Gc\Mvc\Resolver\AssetAliasPathStack;
use Gc\Registry;
use Assetic\Asset;
use AssetManager\Service\MimeResolver;

/**
 * Unit Tests for the Alias Path Stack Resolver
 * @group    Gc
 * @category Gc_Tests
 * @package  Library
 */
class AliasPathStackTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->serviceManager = Registry::get('Application')->getServiceManager();
    }

    /**
     * Test add alias method.
     *
     * @return void
     */
    public function testAddAlias()
    {
        $resolver = new AssetAliasPathStack($this->serviceManager);

        $reflectionClass = new \ReflectionClass('Gc\Mvc\resolver\AssetAliasPathStack');

        $addAlias = $reflectionClass->getMethod('addAlias');
        $addAlias->setAccessible(true);

        $property = $reflectionClass->getProperty('aliases');
        $property->setAccessible(true);

        $addAlias->invoke($resolver, 'alias', 'path');

        $result = $property->getValue($resolver);
        $this->assertArrayHasKey('alias', $result);
        $this->assertContains('path' . DIRECTORY_SEPARATOR, $result);
    }

    /**
     * Test addAlias fails with bad key
     *
     * @return void
     */
    public function testAddAliasFailsWithBadKey()
    {
        $this->setExpectedException('AssetManager\Exception\InvalidArgumentException');

        $resolver = new AssetAliasPathStack($this->serviceManager);

        $reflectionClass = new \ReflectionClass('Gc\Mvc\resolver\AssetAliasPathStack');

        $addAlias = $reflectionClass->getMethod('addAlias');
        $addAlias->setAccessible(true);

        $property = $reflectionClass->getProperty('aliases');
        $property->setAccessible(true);

        $addAlias->invoke($resolver, null, 'path');
    }

    /**
     * Test addAlias fails with bad Path
     *
     * @return void
     */
    public function testAddAliasFailsWithBadPath()
    {
        $this->setExpectedException('AssetManager\Exception\InvalidArgumentException');
        $resolver = new AssetAliasPathStack($this->serviceManager);

        $reflectionClass = new \ReflectionClass('Gc\Mvc\resolver\AssetAliasPathStack');

        $addAlias = $reflectionClass->getMethod('addAlias');
        $addAlias->setAccessible(true);

        $property = $reflectionClass->getProperty('aliases');
        $property->setAccessible(true);

        $addAlias->invoke($resolver, 'alias', null);
    }

    /**
     * Test normalize path
     *
     * @return void
     */
    public function testNormalizePath()
    {
        $resolver = new AssetAliasPathStack($this->serviceManager);

        $reflectionClass = new \ReflectionClass('Gc\Mvc\resolver\AssetAliasPathStack');

        $addAlias = $reflectionClass->getMethod('normalizePath');
        $addAlias->setAccessible(true);

        $result = $addAlias->invoke($resolver, 'somePath\/\/\/');

        $this->assertEquals(
            'somePath'.DIRECTORY_SEPARATOR,
            $result
        );
    }

    /**
     * Test Set Mime Resolver Only Accepts a mime Resolver
     *
     * @return void
     */
    public function testGetAndSetMimeResolver()
    {
        $mimeReolver = $this->getMockBuilder('AssetManager\Service\MimeResolver')
            ->disableOriginalConstructor()
            ->getMock();

        $resolver = new AssetAliasPathStack($this->serviceManager);
        $resolver->addAlias('my/alias/', __DIR__);

        $resolver->setMimeResolver($mimeReolver);

        $returned = $resolver->getMimeResolver();

        $this->assertEquals($mimeReolver, $returned);
    }

    /**
     * Test Set Mime Resolver Only Accepts a mime Resolver
     *
     * @return void
     */
    public function testSetMimeResolverFailObject()
    {
        $this->setExpectedException('PHPUnit_Framework_Error');
        $resolver = new AssetAliasPathStack($this->serviceManager);
        $resolver->addAlias('my/alias/', __DIR__);
        $resolver->setMimeResolver(new \stdClass());
    }

    /**
     * Test Lfi Protection Flag Defaults to true
     *
     * @return void
     */
    public function testLfiProtectionFlagDefaultsTrue()
    {
        $resolver = new AssetAliasPathStack($this->serviceManager);
        $resolver->addAlias('my/alias/', __DIR__);
        $returned = $resolver->isLfiProtectionOn();

        $this->assertTrue($returned);
    }

    /**
     * Test Get and Set of Lfi Protection Flag
     *
     * @return void
     */
    public function testGetAndSetOfLfiProtectionFlag()
    {
        $resolver = new AssetAliasPathStack($this->serviceManager);
        $resolver->addAlias('my/alias/', __DIR__);
        $resolver->setLfiProtection(true);
        $returned = $resolver->isLfiProtectionOn();

        $this->assertTrue($returned);

        $resolver->setLfiProtection(false);
        $returned = $resolver->isLfiProtectionOn();

        $this->assertFalse($returned);
    }

    /**
     * Test Resolve returns valid asset
     *
     * @return void
     */
    public function testResolve()
    {
        $resolver = new AssetAliasPathStack($this->serviceManager);
        $resolver->addAlias('my/alias/', __DIR__);
        $this->assertTrue($resolver instanceof AssetAliasPathStack);

        $mimeResolver = new MimeResolver;
        $resolver->setMimeResolver($mimeResolver);

        $fileAsset = new Asset\FileAsset(__FILE__);
        $fileAsset->mimetype = $mimeResolver->getMimeType(__FILE__);

        $this->assertInstanceOf('Assetic\Asset\FileAsset', $resolver->resolve('my/alias/'.basename(__FILE__)));
        $this->assertNull($resolver->resolve('i-do-not-exist.php'));
    }

    /**
     * Test that resolver will not resolve directories
     *
     * @return void
     */
    public function testWillNotResolveDirectories()
    {
        $resolver = new AssetAliasPathStack($this->serviceManager);
        $resolver->addAlias('my/alias/', __DIR__);
        $this->assertNull($resolver->resolve('my/alias/' . basename(__DIR__)));
    }

    /**
     * Test Lfi Protection
     *
     * @return void
     */
    public function testLfiProtection()
    {
        $mimeResolver = new MimeResolver;
        $resolver     = new AssetAliasPathStack($this->serviceManager);
        $resolver->addAlias('my/alias/', __DIR__);
        $resolver->setMimeResolver($mimeResolver);

        // should be on by default
        $this->assertTrue($resolver->isLfiProtectionOn());

        $this->assertNull(
            $resolver->resolve(
                '..' . DIRECTORY_SEPARATOR . basename(__DIR__) . DIRECTORY_SEPARATOR . basename(__FILE__)
            )
        );

        $resolver->setLfiProtection(false);

        $this->assertEquals(
            file_get_contents(__FILE__),
            $resolver->resolve(
                'my/alias/..' . DIRECTORY_SEPARATOR . basename(__DIR__) . DIRECTORY_SEPARATOR . basename(__FILE__)
            )->dump()
        );
    }
}
