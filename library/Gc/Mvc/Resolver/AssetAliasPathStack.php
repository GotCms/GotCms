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
 * @subpackage Mvc\Resolver
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Gc\Mvc\Resolver;

use Gc\Datatype\Collection as DatatypeCollection;
use Zend\ServiceManager\ServiceManager;
use SplFileInfo;
use Assetic\Asset\FileAsset;
use AssetManager\Exception;
use AssetManager\Service\MimeResolver;
use AssetManager\Resolver;

/**
 * This resolver allows you to resolve from a stack of paths.
 *
 * @category   Gc
 * @package    Library
 * @subpackage Mvc\Resolver
 */
class AssetAliasPathStack implements Resolver\ResolverInterface, Resolver\MimeResolverAwareInterface
{
    /**
     * Aliases
     *
     * @var Array
     */
    protected $aliases = array();

    /**
     * Flag indicating whether or not LFI protection for rendering view scripts is enabled
     *
     * @var bool
     */
    protected $lfiProtectionOn = true;

    /**
     * The mime resolver.
     *
     * @var MimeResolver
     */
    protected $mimeResolver;

    /**
     * The service manager.
     *
     * @var ServiceManager
     */
    protected $serviceLocator;

    /**
     * Constructor
     *
     * Populate the array stack with a list of aliases and their corresponding paths
     *
     * @param ServiceManager $serviceManager Service Manager
     *
     * @throws Exception\InvalidArgumentException
     */
    public function __construct(ServiceManager $serviceManager = null)
    {
        $this->serviceLocator = $serviceManager;
        $this->loadDatatypesAliases();
        $this->loadModulesAliases();
    }

    /**
     * Add a single alias to the stack
     *
     * @param string $alias Alias
     * @param string $path  Path
     *
     * @throws Exception\InvalidArgumentException
     *
     * @return void
     */
    public function addAlias($alias, $path)
    {
        if (!is_string($path)) {
            throw new Exception\InvalidArgumentException(
                sprintf(
                    'Invalid path provided; must be a string, received %s',
                    gettype($path)
                )
            );
        }

        if (!is_string($alias)) {
            throw new Exception\InvalidArgumentException(
                sprintf(
                    'Invalid alias provided; must be a string, received %s',
                    gettype($alias)
                )
            );
        }

        $this->aliases[$alias] = $this->normalizePath($path);
    }

    /**
     * Normalize a path for insertion in the stack
     *
     * @param string $path Path to normalize
     *
     * @return string
     */
    protected function normalizePath($path)
    {
        return rtrim($path, '/\\') . DIRECTORY_SEPARATOR;
    }

    /**
     * Set the mime resolver
     *
     * @param MimeResolver $resolver Resolver
     *
     * @return void
     */
    public function setMimeResolver(MimeResolver $resolver)
    {
        $this->mimeResolver = $resolver;
    }

    /**
     * Get the mime resolver
     *
     * @return MimeResolver
     */
    public function getMimeResolver()
    {
        return $this->mimeResolver;
    }

    /**
     * Set LFI protection flag
     *
     * @param boolean $flag Define if lfi protection is active
     *
     * @return self
     */
    public function setLfiProtection($flag)
    {
        $this->lfiProtectionOn = (bool) $flag;
    }

    /**
     * Return status of LFI protection flag
     *
     * @return bool
     */
    public function isLfiProtectionOn()
    {
        return $this->lfiProtectionOn;
    }

    /**
     * Resolve an Asset
     *
     * @param string $name The path to resolve.
     *
     * @return  \Assetic\Asset\AssetInterface|null Asset instance when found, null when not.
     */
    public function resolve($name)
    {
        if ($this->isLfiProtectionOn() && preg_match('#\.\.[\\\/]#', $name)) {
            return null;
        }

        foreach ($this->aliases as $alias => $path) {
            if (strpos($name, $alias) === false) {
                continue;
            }

            $name = str_replace($alias, '', $name);

            $file = new SplFileInfo($path . $name);

            if ($file->isReadable() && !$file->isDir()) {
                $filePath = $file->getRealPath();
                $mimeType = $this->getMimeResolver()->getMimeType($filePath);
                $asset    = new FileAsset($filePath);

                $asset->mimetype = $mimeType;

                return $asset;
            }
        }

        return null;
    }

    /**
     * Load Datatypes aliases
     *
     * @return void
     */
    protected function loadDatatypesAliases()
    {
        $collection   = new DatatypeCollection();
        $datatypeList = $this->serviceLocator->get('DatatypesList');
        foreach ($datatypeList as $path => $model) {
            $this->addAlias(
                'backend/assets/datatypes/' . strtolower($model),
                $path . '/assets'
            );
        }
    }

    /**
     * Load module aliases
     *
     * @return void
     */
    protected function loadModulesAliases()
    {

    }
}
