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
    protected $_pos = 0;

    /**
     * Current stream path.
     *
     * @var string
     */
    protected $_path = NULL;

    /**
     * Data for streaming.
     *
     * @var string
     */
    protected static $_data = array();

    /**
     * Stream stats.
     *
     * @var array
     */
    protected $_stat = array();

    /**
     * Opens the script file and converts markup.
     *
     * @param string path
     * @param string $mode
     * @param integer $options
     * @param string $opened_path
     * @return boolean
     */
    public function stream_open($path, $mode, $options, &$opened_path)
    {
        $path        = str_replace('zend.view://', '', $path);
        $this->_path = $path;
        if(empty(self::$_data[$path]))
        {
            self::$_data[$path] = NULL;
        }

        return TRUE;
    }

    /**
     * Reads from the stream.
     *
     * @param integer $count
     * @return mixed
     */
    public function stream_read($count)
    {
        $ret = substr(self::$_data[$this->_path], $this->_pos, $count);
        $this->_pos += strlen($ret);

        return $ret;
    }

    /**
     * Write in the stream
     *
     * @param string $data
     * @return integer
     */
    public function stream_write($data)
    {
        $left = substr(self::$_data[$this->_path], 0, $this->_pos);
        $right = substr(self::$_data[$this->_path], $this->_pos + strlen($data));
        self::$_data[$this->_path] = $left . $data . $right;
        $this->_pos += strlen($data);

        return strlen($data);
    }

    /**
     * Tells the current position in the stream.
     *
     * @return integer
     */
    public function stream_tell()
    {
        return $this->_pos;
    }

    /**
     * Tells if we are at the end of the stream.
     *
     * @return boolean
     */
    public function stream_eof()
    {
        return $this->_pos >= strlen(self::$_data[$this->_path]);
    }

    /**
     * Stream statistics.
     *
     * @return array
     */
    public function stream_stat()
    {
        return $this->_stat;
    }

    /**
     * Seek to a specific point in the stream.
     *
     * @param integer $offset
     * @param integer $whence
     * @return boolean
     */
    public function stream_seek($offset, $whence)
    {
        switch ($whence)
        {
            case SEEK_SET:
                if($offset < strlen(self::$_data[$this->_path]) and $offset >= 0)
                {
                    $this->_pos = $offset;
                    return true;
                }
                else
                {
                    return false;
                }
            break;

            case SEEK_CUR:
                if($offset >= 0)
                {
                    $this->_pos += $offset;
                    return true;
                }
                else
                {
                    return false;
                }
            break;

            case SEEK_END:
                if(strlen(self::$_data[$this->_path]) + $offset >= 0)
                {
                    $this->_pos = strlen(self::$_data[$this->_path]) + $offset;
                    return true;
                }
                else
                {
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
     * @param string $path
     * @param int $flags
     * @return boolean
     */
    public function url_stat($path, $flags)
    {
        return FALSE;
    }
}
