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
 * @category Gc_Test
 * @package  Bootstrap
 * @author   Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license  GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link     http://www.got-cms.com
 */

/**
 * Override upload functions
 */
namespace Gc\Media\File;

namespace Zend\File\Transfer\Adapter;

function is_uploaded_file($filename)
{
    return true;
}

function move_uploaded_file($filename, $destination)
{
    return copy($filename, $destination);
}

namespace Zend\Validator\File;

function is_uploaded_file($filename)
{
    return true;
}

function move_uploaded_file($filename, $destination)
{
    return copy($filename, $destination);
}

/**
 * Override Git adapter
 */
namespace Gc\Core\Updater\Adapter;

function exec($command, &$output = array(), &$return_var = null)
{
    $output = array();
    return '';
}


/**
 * Override updater
 */
namespace Gc\Core;

function glob($pattern, $flags = 0)
{
    $existed = in_array('zend.view', stream_get_wrappers());
    if ($existed) {
        stream_wrapper_unregister('zend.view');
    }

    stream_wrapper_register('zend.view', '\Gc\View\Stream');

    if (preg_match('~\.sql$~', $pattern)) {
        $content = trim(file_get_contents('zend.view://test-updater'));
        if (empty($content)) {
            return false;
        }

        return array('zend.view://test-updater');
    }

    return array('9999.999.999');
}

/**
 * Override Git adapter
 */
namespace Modules\Backup\Model;

function exec($command, &$output = array(), &$return_var = null)
{
    $output = array();
    return '';
}

/**
 * Override Elfinder connector
 */
namespace elFinder;

class elFinderConnector
{
    public function run() {
        return true;
    }
}
