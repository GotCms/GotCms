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
 * @subpackage View
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Gc\View;

/**
 * Stream wrapper to convert markup of mostly-PHP templates into PHP prior to
 * include().
 *
 * Based in large part on the example at
 * http://www.php.net/manual/en/function.stream-wrapper-register.php
 *
 * @category   Gc
 * @package    Library
 * @subpackage View
 */
class Stream
{
    /**
     * Current stream position.
     *
     * @var int
     */
    protected $position = 0;

    /**
     * Current stream path.
     *
     * @var string
     */
    protected $path = null;

    /**
     * Data for streaming.
     *
     * @var string
     */
    protected static $data = array();

    /**
     * Stream stats.
     *
     * @var array
     */
    protected $stat = array();

    /**
     * Stream stats.
     *
     * @var array
     */
    protected $mode;

    /**
     * Opens the script file and converts markup.
     *
     * @param string  $path        Path
     * @param string  $mode        Mode
     * @param integer $options     Options
     * @param string  &$openedpath Opened path
     *
     * @return boolean
     */
    public function stream_open($path, $mode, $options, &$openedpath)
    {
        $this->mode     = $mode;
        $path           = str_replace('zend.view://', '', $path);
        $this->path     = $path;
        $this->position = 0;
        if (empty(self::$data[$path])) {
            self::$data[$path] = null;
        }

        return true;
    }

    /**
     * Reads from the stream.
     *
     * @param integer $count Count
     *
     * @return mixed
     */
    public function stream_read($count)
    {
        $ret             = substr(self::$data[$this->path], $this->position, $count);
        $this->position += strlen($ret);

        return $ret;
    }

    /**
     * Write in the stream
     *
     * @param string $data Data
     *
     * @return integer
     */
    public function stream_write($data)
    {
        if ($this->mode == 'wb') {
            self::$data[$this->path] = null;
            $this->position          = 0;
        }

        $left                    = substr(self::$data[$this->path], 0, $this->position);
        $right                   = substr(self::$data[$this->path], $this->position + strlen($data));
        self::$data[$this->path] = $left . $data . $right;
        $this->position         += strlen($left . $data);

        return strlen($data);
    }

    /**
     * Tells the current position in the stream.
     *
     * @return integer
     */
    public function stream_tell()
    {
        return $this->position;
    }

    /**
     * Tells if we are at the end of the stream.
     *
     * @return boolean
     */
    public function stream_eof()
    {
        return $this->position >= strlen(self::$data[$this->path]);
    }

    /**
     * Stream statistics.
     *
     * @return array
     */
    public function stream_stat()
    {
        return $this->stat;
    }

    /**
     * Seek to a specific point in the stream.
     *
     * @param integer $offset Offset
     * @param integer $whence Whence
     *
     * @return boolean
     */
    public function stream_seek($offset, $whence)
    {
        switch ($whence) {
            case SEEK_SET:
                if ($offset < strlen(self::$data[$this->path]) and $offset >= 0) {
                    $this->position = $offset;
                    return true;
                } else {
                    return false;
                }
                break;
            case SEEK_CUR:
                if ($offset >= 0) {
                    $this->position += $offset;
                    return true;
                } else {
                    return false;
                }
                break;
            case SEEK_END:
                if (strlen(self::$data[$this->path]) + $offset >= 0) {
                    $this->position = strlen(self::$data[$this->path]) + $offset;
                    return true;
                } else {
                    return false;
                }
                break;
            default:
                return false;
        }
    }

    /**
     * Retrieve information about a file
     * Always return false because data come from the database
     *
     * @param string $path  Path
     * @param int    $flags Flags
     *
     * @return boolean
     */
    public function url_stat($path, $flags)
    {
        return false;
    }

    /**
     * Register stream wrapper
     *
     * @param string $name Name
     *
     * @return void
     */
    public static function register($name = 'zend.view', $overwrite = true)
    {
        if (in_array($name, stream_get_wrappers())) {
            if (!$overwrite) {
                return;
            }

            stream_wrapper_unregister($name);
        }

        stream_wrapper_register($name, 'Gc\View\Stream');
    }
}
