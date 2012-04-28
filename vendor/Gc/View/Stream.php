<?php
namespace Gc\View;

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
     */
    public function stream_read($count)
    {
        $ret = substr(self::$_data[$this->_path], $this->_pos, $count);
        $this->_pos += strlen($ret);

        return $ret;
    }


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
     */
    public function stream_tell()
    {
        return $this->_pos;
    }


    /**
     * Tells if we are at the end of the stream.
     */
    public function stream_eof()
    {
        return $this->_pos >= strlen(self::$_data[$this->_path]);
    }


    /**
     * Stream statistics.
     */
    public function stream_stat()
    {
        return $this->_stat;
    }


    /**
     * Seek to a specific point in the stream.
     */
    public function stream_seek($offset, $whence)
    {
        switch ($whence)
        {
            case SEEK_SET:
                if ($offset < strlen(self::$_data[$this->_path]) and $offset >= 0)
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
                if ($offset >= 0)
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
                if (strlen(self::$_data[$this->_path]) + $offset >= 0)
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
}
