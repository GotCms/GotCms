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
 * @category    Gc
 * @package     Library
 * @subpackage  View\Resolver
 * @author      Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license     GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link        http://www.got-cms.com
 */

namespace Gc\View\Resolver;

use SplFileInfo,
    Zend\View\Exception,
    Zend\View\Renderer\RendererInterface as Renderer,
    Zend\View\Resolver\TemplatePathStack as PathStack;

/**
 * Resolves view scripts based on a stack of paths
 *
 * @category    Gc
 * @package     Library
 * @subpackage  View\Resolver
 */
class TemplatePathStack extends PathStack
{
    /**
     * Retrieve the filesystem path to a view script
     *
     * @param  string $name
     * @param  null|Renderer $renderer
     * @return string
     * @throws Exception\RuntimeException
     */
    public function resolve($name, Renderer $renderer = null)
    {
        //Force use view stream
        $this->useViewStream = TRUE;
        $this->lastLookupFailure = FALSE;

        if($this->isLfiProtectionOn() && preg_match('#\.\.[\\\/]#', $name))
        {
            throw new Exception\DomainException(
                'Requested scripts may not include parent directory traversal ("../", "..\\" notation)'
            );
        }

        if(strpos($name, '.phtml') === FALSE)
        {
            if($this->useStreamWrapper())
            {
                // If using a stream wrapper, prepend the spec to the path
                $file_path = 'zend.view://' . $name;
                return $file_path;
            }
        }
        else
        {
            if(!count($this->paths))
            {
                $this->lastLookupFailure = static::FAILURE_NO_PATHS;
                return FALSE;
            }

            // Ensure we have the expected file extension
            $default_suffix = $this->getDefaultSuffix();
            if(pathinfo($name, PATHINFO_EXTENSION) != $default_suffix)
            {
                $name .= '.' . $default_suffix;
            }

            foreach($this->paths as $path)
            {
                $file = new SplFileInfo($path . $name);
                if($file->isReadable())
                {
                    // Found! Return it.
                    if(($file_path = $file->getRealPath()) === FALSE && substr($path, 0, 7) === 'phar://')
                    {
                        // Do not try to expand phar paths (realpath + phars == fail)
                        $file_path = $path . $name;
                        if(!file_exists($file_path))
                        {
                            break;
                        }
                    }

                    return $file_path;
                }
            }
        }

        $this->lastLookupFailure = static::FAILURE_NOT_FOUND;
        return FALSE;
    }
}
