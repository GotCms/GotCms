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
 * @subpackage View\Resolver
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Gc\View\Resolver;

use SplFileInfo;
use Zend\View\Exception;
use Zend\View\Renderer\RendererInterface as Renderer;
use Zend\View\Resolver\TemplatePathStack as PathStack;

/**
 * Resolves view scripts based on a stack of paths
 *
 * @category   Gc
 * @package    Library
 * @subpackage View\Resolver
 */
class TemplatePathStack extends PathStack
{
    /**
     * Retrieve the filesystem path to a view script
     *
     * @param string        $name     Template name
     * @param null|Renderer $renderer Renderer
     *
     * @return string
     * @throws Exception\RuntimeException
     */
    public function resolve($name, Renderer $renderer = null)
    {
        //Force use view stream
        $this->useViewStream     = true;
        $this->lastLookupFailure = false;

        if ($this->isLfiProtectionOn() && preg_match('#\.\.[\\\/]#', $name)) {
            throw new Exception\DomainException(
                'Requested scripts may not include parent directory traversal ("../", "..\\" notation)'
            );
        }

        if (strpos($name, '.phtml') === false) {
            if ($this->useStreamWrapper()) {
                // If using a stream wrapper, prepend the spec to the path
                $filePath = 'zend.view://' . $name;
                return $filePath;
            }
        } else {
            if (!count($this->paths)) {
                $this->lastLookupFailure = static::FAILURE_NO_PATHS;
                return false;
            }

            // Ensure we have the expected file extension
            $defaultSuffix = $this->getDefaultSuffix();
            if (pathinfo($name, PATHINFO_EXTENSION) != $defaultSuffix) {
                $name .= '.' . $defaultSuffix;
            }

            foreach ($this->paths as $path) {
                $file = new SplFileInfo($path . $name);
                if ($file->isReadable()) {
                    // Found! Return it.
                    if (($filePath = $file->getRealPath()) === false && substr($path, 0, 7) === 'phar://') {
                        // Do not try to expand phar paths (realpath + phars == fail)
                        $filePath = $path . $name;
                        if (!file_exists($filePath)) {
                            break;
                        }
                    }

                    return $filePath;
                }
            }
        }

        $this->lastLookupFailure = static::FAILURE_NOT_FOUND;
        return false;
    }
}
