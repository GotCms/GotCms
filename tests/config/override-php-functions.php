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
 * Override Updater adapters
 */
namespace Gc\Core\Updater\Adapter;

function exec($command, &$output = array(), &$returnVar = null)
{
    $output = array();
    return '';
}


/**
 * Override updater
 */
namespace Gc\Core;

use Gc\View\Stream;

function glob($pattern, $flags = 0)
{
    Stream::register();

    if (preg_match('~\.sql$~', $pattern)) {
        $content = trim(file_get_contents('zend.view://test-updater'));
        if (empty($content)) {
            return false;
        }

        return array('zend.view://test-updater');
    }

    $scriptPath = GC_APPLICATION_PATH . '/tests/library/Gc/Core/_files/test.php';
    if (file_exists($scriptPath) and in_array($pattern, array(GC_APPLICATION_PATH . '/data/update/*', '999/*.php'))) {
        $content = file_get_contents($scriptPath);
        if ($pattern == GC_APPLICATION_PATH . '/data/update/*') {
            if (empty($content)) {
                return array('0');
            }

            return array(999);
        } else {
            if (empty($content)) {
                return array();
            }

            return array($scriptPath);
        }
    }

    return array('9999.999.999');
}

function file_put_contents($filename, $data, $flags = 0, $context = null)
{
    if (strpos($filename, GC_APPLICATION_PATH . '/data/translation') !== false) {
        return true;
    }

    return \file_put_contents($filename, $data, $flags, $context);
}

/**
 * Override Git adapter
 */
namespace Backup\Model;

function exec($command, &$output = array(), &$returnVar = null)
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

/**
 * Override Installer
 */
namespace GcFrontend\Controller;

function file_exists($string)
{
    return false;
}

function ini_set($string, $value)
{
    return true;
}

function file_get_contents($filename)
{
    return 'DELETE FROM core_config_data WHERE id = "test";';
}

function file_put_contents($filename, $content)
{
    return true;
}

function copy($source, $destination)
{
    return true;
}

function glob($path)
{
    return array(GC_MEDIA_PATH . '/fr_FR.php');
}


/**
 * Override Zend\Mail\Transport
 */
namespace Zend\Mail\Transport;

function mail($to, $subject, $message, $additional_headers = null, $additional_parameters = null)
{
    return true;
}

/**
 * Override Zend\Mail\Transport
 */
namespace Gc;

function file_get_contents($filename)
{
    return \file_get_contents(GC_MEDIA_PATH . '/github.tags');
}
